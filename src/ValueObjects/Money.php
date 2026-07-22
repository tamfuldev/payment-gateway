<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\ValueObjects;

use Tamfuldev\Payment\Enums\Currency;
use Tamfuldev\Payment\Exceptions\PaymentException;

/**
 * Immutable amount of money, represented as integer **minor units** to avoid floating-point errors.
 *
 * Examples: 100,000 VND -> minorUnits = 100000 (VND exponent 0);
 *           9.99 USD    -> minorUnits = 999   (USD exponent 2).
 *
 * Money is never stored as a float.
 */
final readonly class Money
{
    private function __construct(
        public int $minorUnits,
        public Currency $currency,
    ) {
        if ($minorUnits < 0) {
            throw new PaymentException('Amount must not be negative.');
        }
    }

    /**
     * Create from minor units (the smallest unit) — exact.
     */
    public static function ofMinor(int $minorUnits, Currency $currency): self
    {
        return new self($minorUnits, $currency);
    }

    /**
     * Create from major units (the "user-facing" amount). Accepts a string or integer
     * to avoid floats, e.g. ofMajor('9.99', USD) or ofMajor(100000, VND).
     */
    public static function ofMajor(int|string $amount, Currency $currency): self
    {
        $normalized = self::normalizeDecimalString((string) $amount);
        $exponent = $currency->exponent();

        [$integer, $fraction] = self::splitDecimal($normalized);

        if (strlen($fraction) > $exponent) {
            throw new PaymentException(
                "Amount '{$amount}' has more decimal places than {$currency->value} allows ({$exponent})."
            );
        }

        $fraction = str_pad($fraction, $exponent, '0');
        $minorUnits = (int) ($integer . $fraction);

        return new self($minorUnits, $currency);
    }

    /**
     * The major value as a string (safe to display/compare), e.g. "100000" or "9.99".
     */
    public function toMajorString(): string
    {
        $exponent = $this->currency->exponent();
        if ($exponent === 0) {
            return (string) $this->minorUnits;
        }

        $padded = str_pad((string) $this->minorUnits, $exponent + 1, '0', STR_PAD_LEFT);
        $integer = substr($padded, 0, -$exponent);
        $fraction = substr($padded, -$exponent);

        return $integer . '.' . $fraction;
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minorUnits + $other->minorUnits, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency
            && $this->minorUnits === $other->minorUnits;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new PaymentException(
                "Cannot operate on two different currencies: {$this->currency->value} and {$other->currency->value}."
            );
        }
    }

    private static function normalizeDecimalString(string $value): string
    {
        $value = trim($value);
        if ($value === '' || preg_match('/^\d+(\.\d+)?$/', $value) !== 1) {
            throw new PaymentException("Invalid amount: '{$value}'. Only non-negative numbers are accepted.");
        }

        return $value;
    }

    /**
     * @return array{0: string, 1: string} [integer part, fractional part]
     */
    private static function splitDecimal(string $value): array
    {
        if (! str_contains($value, '.')) {
            return [$value, ''];
        }

        /** @var array{0: string, 1: string} $parts */
        $parts = explode('.', $value, 2);

        return $parts;
    }
}
