<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Gateways\Vnpay;

use DateTimeImmutable;
use DateTimeZone;
use Tamfuldev\Payment\Data\ChargeRequest;
use Tamfuldev\Payment\Data\ChargeResponse;
use Tamfuldev\Payment\Data\WebhookPayload;
use Tamfuldev\Payment\Enums\Currency;
use Tamfuldev\Payment\Enums\PaymentMethod;
use Tamfuldev\Payment\Enums\PaymentStatus;
use Tamfuldev\Payment\Gateways\AbstractGateway;
use Tamfuldev\Payment\ValueObjects\Money;

/**
 * VNPay gateway (redirect flow, HMAC-SHA512 signature).
 *
 * Note: the gateway multiplies the amount by 100 when sending `vnp_Amount` even though VND is
 * zero-decimal -> always go through {@see Money} and multiply/divide by 100 at the wire boundary,
 * never with floats.
 *
 * Sample scope: charge + verify + parse webhook. Refund/query are follow-up work
 * (they need transactionDate/transactionType) — deliberately not implemented to avoid
 * shipping a half-baked money flow.
 */
final class VnpayGateway extends AbstractGateway
{
    private const VERSION = '2.1.0';
    private const SUCCESS_CODE = '00';
    private const WIRE_AMOUNT_MULTIPLIER = 100;

    public function name(): string
    {
        return 'vnpay';
    }

    public function charge(ChargeRequest $request): ChargeResponse
    {
        $params = [
            'vnp_Version' => self::VERSION,
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $this->requireConfig('tmn_code'),
            'vnp_Amount' => (string) ($request->amount->minorUnits * self::WIRE_AMOUNT_MULTIPLIER),
            'vnp_CurrCode' => Currency::VND->value,
            'vnp_TxnRef' => $request->orderId,
            'vnp_OrderInfo' => $request->description,
            'vnp_OrderType' => $this->config('order_type', 'other'),
            'vnp_Locale' => $request->locale ?? $this->config('locale', 'vn'),
            'vnp_ReturnUrl' => $request->returnUrl,
            'vnp_IpAddr' => $request->ipAddress ?? '127.0.0.1',
            'vnp_CreateDate' => $this->now()->format('YmdHis'),
        ];

        $hashData = $this->buildHashData($params);
        $secureHash = $this->sign($hashData);

        $redirectUrl = $this->requireConfig('pay_url')
            . '?' . $hashData
            . '&vnp_SecureHash=' . $secureHash;

        return new ChargeResponse(
            orderId: $request->orderId,
            status: PaymentStatus::Pending,
            method: PaymentMethod::Redirect,
            redirectUrl: $redirectUrl,
        );
    }

    /**
     * @param  array<string, string>  $payload
     */
    public function verifySignature(array $payload): bool
    {
        $provided = $payload['vnp_SecureHash'] ?? '';
        if ($provided === '') {
            return false;
        }

        unset($payload['vnp_SecureHash'], $payload['vnp_SecureHashType']);

        $expected = $this->sign($this->buildHashData($payload));

        return $this->signaturesMatch($expected, $provided);
    }

    /**
     * @param  array<string, string>  $payload
     */
    public function parseWebhook(array $payload): WebhookPayload
    {
        $wireAmount = (int) ($payload['vnp_Amount'] ?? '0');

        return new WebhookPayload(
            orderId: $payload['vnp_TxnRef'] ?? '',
            status: $this->mapStatus($payload),
            amount: Money::ofMinor(intdiv($wireAmount, self::WIRE_AMOUNT_MULTIPLIER), Currency::VND),
            gatewayReference: $payload['vnp_TransactionNo'] ?? '',
        );
    }

    /**
     * Build the signing string per VNPay's rules: sort keys ascending, drop empty values,
     * join `urlencode(key)=urlencode(value)` with '&'.
     *
     * @param  array<string, string>  $params
     */
    private function buildHashData(array $params): string
    {
        $params = array_filter($params, static fn (string $value): bool => $value !== '');
        ksort($params);

        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = urlencode($key) . '=' . urlencode($value);
        }

        return implode('&', $pairs);
    }

    private function sign(string $hashData): string
    {
        return hash_hmac('sha512', $hashData, $this->requireConfig('hash_secret'));
    }

    /**
     * @param  array<string, string>  $payload
     */
    private function mapStatus(array $payload): PaymentStatus
    {
        $responseCode = $payload['vnp_ResponseCode'] ?? '';
        $transactionStatus = $payload['vnp_TransactionStatus'] ?? '';

        return $responseCode === self::SUCCESS_CODE && $transactionStatus === self::SUCCESS_CODE
            ? PaymentStatus::Completed
            : PaymentStatus::Failed;
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
    }
}
