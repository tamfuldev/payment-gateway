<?php

declare(strict_types=1);

use Tamfuldev\Payment\Data\RefundRequest;
use Tamfuldev\Payment\Data\RefundResponse;
use Tamfuldev\Payment\Enums\Currency;
use Tamfuldev\Payment\Enums\PaymentStatus;
use Tamfuldev\Payment\ValueObjects\Money;

it('constructs an immutable refund request', function () {
    $request = new RefundRequest(
        orderId: 'ORDER123',
        gatewayReference: 'REF987',
        amount: Money::ofMajor('50000', Currency::VND),
        reason: 'customer request',
    );

    expect($request->orderId)->toBe('ORDER123')
        ->and($request->gatewayReference)->toBe('REF987')
        ->and($request->amount->minorUnits)->toBe(50000)
        ->and($request->reason)->toBe('customer request');
});

it('constructs a refund response', function () {
    $response = new RefundResponse(
        orderId: 'ORDER123',
        status: PaymentStatus::Refunded,
        refundReference: 'RFND1',
    );

    expect($response->status)->toBe(PaymentStatus::Refunded)
        ->and($response->refundReference)->toBe('RFND1')
        ->and($response->raw)->toBe([]);
});
