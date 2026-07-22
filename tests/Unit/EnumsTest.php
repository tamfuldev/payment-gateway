<?php

declare(strict_types=1);

use Tamfuldev\Payment\Enums\Currency;
use Tamfuldev\Payment\Enums\PaymentStatus;

it('reports which statuses are final', function () {
    expect(PaymentStatus::Completed->isFinal())->toBeTrue()
        ->and(PaymentStatus::Failed->isFinal())->toBeTrue()
        ->and(PaymentStatus::Refunded->isFinal())->toBeTrue()
        ->and(PaymentStatus::Cancelled->isFinal())->toBeTrue()
        ->and(PaymentStatus::Pending->isFinal())->toBeFalse();
});

it('reports which statuses are successful', function () {
    expect(PaymentStatus::Completed->isSuccessful())->toBeTrue()
        ->and(PaymentStatus::Failed->isSuccessful())->toBeFalse()
        ->and(PaymentStatus::Pending->isSuccessful())->toBeFalse();
});

it('exposes currency exponent and minor-unit factor', function () {
    expect(Currency::VND->exponent())->toBe(0)
        ->and(Currency::VND->minorUnitFactor())->toBe(1)
        ->and(Currency::JPY->minorUnitFactor())->toBe(1)
        ->and(Currency::USD->exponent())->toBe(2)
        ->and(Currency::USD->minorUnitFactor())->toBe(100)
        ->and(Currency::EUR->minorUnitFactor())->toBe(100);
});
