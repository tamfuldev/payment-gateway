<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Enums;

/**
 * Normalized transaction status, independent of each gateway's own status codes.
 * Every gateway is responsible for mapping the provider's codes onto these cases.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Failed, self::Refunded, self::Cancelled => true,
            self::Pending => false,
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::Completed;
    }
}
