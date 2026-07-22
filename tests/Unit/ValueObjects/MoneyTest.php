<?php

declare(strict_types=1);

use Tamfuldev\Payment\Enums\Currency;
use Tamfuldev\Payment\Exceptions\PaymentException;
use Tamfuldev\Payment\ValueObjects\Money;

it('creates from minor units exactly', function () {
    expect(Money::ofMinor(100000, Currency::VND)->minorUnits)->toBe(100000);
});

it('converts major to minor for a 2-decimal currency', function () {
    expect(Money::ofMajor('9.99', Currency::USD)->minorUnits)->toBe(999);
});

it('treats VND as zero-decimal', function () {
    expect(Money::ofMajor('100000', Currency::VND)->minorUnits)->toBe(100000);
});

it('pads missing fraction digits', function () {
    expect(Money::ofMajor('9.9', Currency::USD)->minorUnits)->toBe(990)
        ->and(Money::ofMajor('9', Currency::USD)->minorUnits)->toBe(900);
});

it('rejects too many decimal places', function () {
    Money::ofMajor('9.999', Currency::USD);
})->throws(PaymentException::class);

it('rejects VND with a fractional part', function () {
    Money::ofMajor('100.5', Currency::VND);
})->throws(PaymentException::class);

it('rejects an invalid string', function () {
    Money::ofMajor('abc', Currency::USD);
})->throws(PaymentException::class);

it('rejects a negative amount', function () {
    Money::ofMinor(-1, Currency::USD);
})->throws(PaymentException::class);

it('formats the major string correctly for decimal currencies', function () {
    expect(Money::ofMinor(999, Currency::USD)->toMajorString())->toBe('9.99')
        ->and(Money::ofMinor(5, Currency::USD)->toMajorString())->toBe('0.05')
        ->and(Money::ofMinor(100000, Currency::VND)->toMajorString())->toBe('100000');
});

it('adds two amounts of the same currency', function () {
    $sum = Money::ofMinor(100, Currency::USD)->add(Money::ofMinor(50, Currency::USD));
    expect($sum->minorUnits)->toBe(150);
});

it('refuses to add two different currencies', function () {
    Money::ofMinor(100, Currency::USD)->add(Money::ofMinor(50, Currency::VND));
})->throws(PaymentException::class);

it('compares equality by both amount and currency', function () {
    expect(Money::ofMinor(100, Currency::USD)->equals(Money::ofMinor(100, Currency::USD)))->toBeTrue()
        ->and(Money::ofMinor(100, Currency::USD)->equals(Money::ofMinor(100, Currency::VND)))->toBeFalse();
});
