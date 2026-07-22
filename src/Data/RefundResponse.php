<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Data;

use Tamfuldev\Payment\Enums\PaymentStatus;

/**
 * Result of a refund request.
 *
 * @property-read array<string, scalar> $raw Raw response with secrets stripped.
 */
final readonly class RefundResponse
{
    /**
     * @param  array<string, scalar>  $raw
     */
    public function __construct(
        public string $orderId,
        public PaymentStatus $status,
        public ?string $refundReference = null,
        public array $raw = [],
    ) {
    }
}
