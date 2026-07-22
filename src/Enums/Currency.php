<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Enums;

/**
 * Supported currencies together with their number of decimal places (exponent) per ISO 4217.
 *
 * Important: VND/JPY are zero-decimal -> 1 major unit == 1 minor unit.
 * USD/EUR have 2 decimals -> 1 major unit == 100 minor units.
 */
enum Currency: string
{
    case VND = 'VND';
    case USD = 'USD';
    case EUR = 'EUR';
    case JPY = 'JPY';
    case SGD = 'SGD';
    case GBP = 'GBP';

    /**
     * Number of decimal places of the smallest (minor) unit.
     */
    public function exponent(): int
    {
        return match ($this) {
            self::VND, self::JPY => 0,
            self::USD, self::EUR, self::SGD, self::GBP => 2,
        };
    }

    /**
     * Conversion factor from major to minor units (10^exponent).
     */
    public function minorUnitFactor(): int
    {
        return 10 ** $this->exponent();
    }
}
