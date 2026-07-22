<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory;
use Tamfuldev\Payment\Data\ChargeRequest;
use Tamfuldev\Payment\Enums\Currency;
use Tamfuldev\Payment\Enums\PaymentMethod;
use Tamfuldev\Payment\Enums\PaymentStatus;
use Tamfuldev\Payment\Exceptions\GatewayNotConfiguredException;
use Tamfuldev\Payment\Gateways\Vnpay\VnpayGateway;
use Tamfuldev\Payment\ValueObjects\Money;

function makeVnpay(): VnpayGateway
{
    return new VnpayGateway([
        'tmn_code' => 'TESTTMN',
        'hash_secret' => 'SECRET123',
        'pay_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpc/pay.html',
    ], new Factory());
}

/**
 * @return array<string, string>
 */
function successWebhook(): array
{
    return [
        'vnp_TxnRef' => 'ORDER123',
        'vnp_Amount' => '10000000',
        'vnp_ResponseCode' => '00',
        'vnp_TransactionStatus' => '00',
        'vnp_TransactionNo' => '987654',
    ];
}

it('builds a redirect URL with a self-verifiable signature', function () {
    $gateway = makeVnpay();

    $response = $gateway->charge(new ChargeRequest(
        orderId: 'ORDER123',
        amount: Money::ofMajor('100000', Currency::VND),
        description: 'Thanh toan don hang',
        returnUrl: 'https://shop.test/return',
        ipAddress: '10.0.0.1',
    ));

    expect($response->status)->toBe(PaymentStatus::Pending)
        ->and($response->method)->toBe(PaymentMethod::Redirect)
        ->and($response->redirectUrl)->toStartWith('https://sandbox.vnpayment.vn/paymentv2/vpc/pay.html?');

    $query = parse_url((string) $response->redirectUrl, PHP_URL_QUERY);
    parse_str((string) $query, $params);

    // The generated signature must verify -> sign & verify are consistent.
    /** @var array<string, string> $params */
    expect($gateway->verifySignature($params))->toBeTrue()
        // The wire amount is multiplied by 100 (VNPay quirk), no floats involved.
        ->and($params['vnp_Amount'])->toBe('10000000');
});

it('accepts a correctly signed webhook and rejects a tampered amount', function () {
    $gateway = makeVnpay();

    $payload = successWebhook();
    $payload['vnp_SecureHash'] = vnpaySign($payload, 'SECRET123');
    expect($gateway->verifySignature($payload))->toBeTrue();

    // An attacker changes the amount but keeps the old signature.
    $tampered = $payload;
    $tampered['vnp_Amount'] = '1';
    expect($gateway->verifySignature($tampered))->toBeFalse();
});

it('rejects a webhook without a signature', function () {
    expect(makeVnpay()->verifySignature(['vnp_TxnRef' => 'ORDER123']))->toBeFalse();
});

it('parses a successful webhook into a normalized DTO', function () {
    $webhook = makeVnpay()->parseWebhook(successWebhook());

    expect($webhook->orderId)->toBe('ORDER123')
        ->and($webhook->status)->toBe(PaymentStatus::Completed)
        ->and($webhook->amount->minorUnits)->toBe(100000) // divided by 100 back to VND
        ->and($webhook->amount->currency)->toBe(Currency::VND)
        ->and($webhook->gatewayReference)->toBe('987654');
});

it('fails fast when required config is missing', function () {
    $gateway = new VnpayGateway([], new Factory());

    $gateway->charge(new ChargeRequest(
        orderId: 'ORDER123',
        amount: Money::ofMajor('100000', Currency::VND),
        description: 'x',
        returnUrl: 'https://shop.test/return',
    ));
})->throws(GatewayNotConfiguredException::class);

it('maps a non-00 response code to failed', function () {
    $webhook = makeVnpay()->parseWebhook([
        'vnp_TxnRef' => 'ORDER123',
        'vnp_Amount' => '10000000',
        'vnp_ResponseCode' => '24',
        'vnp_TransactionStatus' => '02',
        'vnp_TransactionNo' => '0',
    ]);

    expect($webhook->status)->toBe(PaymentStatus::Failed);
});
