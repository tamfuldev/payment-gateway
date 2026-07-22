<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Data;

use Tamfuldev\Payment\Enums\PaymentMethod;
use Tamfuldev\Payment\Enums\PaymentStatus;

/**
 * Result of initiating a payment. Depending on the gateway, the user is redirected to
 * $redirectUrl or shown $qrContent.
 *
 * @property-read array<string, scalar> $raw Raw response with secrets stripped, for logging/debugging.
 */
final readonly class ChargeResponse
{
    /**
     * @param  array<string, scalar>  $raw
     */
    public function __construct(
        public string $orderId,
        public PaymentStatus $status,
        public PaymentMethod $method,
        public ?string $redirectUrl = null,
        public ?string $qrContent = null,
        public ?string $gatewayReference = null,
        public array $raw = [],
    ) {
    }
}
