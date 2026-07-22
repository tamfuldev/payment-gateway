<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Data;

use Tamfuldev\Payment\Enums\PaymentStatus;
use Tamfuldev\Payment\ValueObjects\Money;

/**
 * A webhook/IPN payload normalized **after the signature has been verified successfully**.
 *
 * Security note: do NOT blindly trust $amount/$status here — the application must still
 * reconcile against the server-side order (correct orderId, correct amount) before recording it.
 *
 * @property-read array<string, scalar> $raw Raw payload with secrets stripped.
 */
final readonly class WebhookPayload
{
    /**
     * @param  array<string, scalar>  $raw
     */
    public function __construct(
        public string $orderId,
        public PaymentStatus $status,
        public Money $amount,
        public string $gatewayReference,
        public array $raw = [],
    ) {
    }
}
