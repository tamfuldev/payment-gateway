<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Data;

use Tamfuldev\Payment\ValueObjects\Money;

/**
 * A request to create a payment, independent of any specific gateway.
 *
 * @property-read array<string, scalar> $metadata Gateway-specific custom data (must not contain secrets).
 */
final readonly class ChargeRequest
{
    /**
     * @param  array<string, scalar>  $metadata
     */
    public function __construct(
        public string $orderId,
        public Money $amount,
        public string $description,
        public string $returnUrl,
        public ?string $ipAddress = null,
        public ?string $locale = null,
        public array $metadata = [],
    ) {
    }
}
