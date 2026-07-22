<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Data;

use Tamfuldev\Payment\ValueObjects\Money;

/**
 * A request to refund a paid transaction, partially or in full.
 */
final readonly class RefundRequest
{
    public function __construct(
        public string $orderId,
        public string $gatewayReference,
        public Money $amount,
        public string $reason,
    ) {
    }
}
