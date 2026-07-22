<?php

declare(strict_types=1);

use Tamfuldev\Payment\Contracts\PaymentGateway;
use Tamfuldev\Payment\Exceptions\GatewayNotConfiguredException;
use Tamfuldev\Payment\Facades\Payment;
use Tamfuldev\Payment\Gateways\Vnpay\VnpayGateway;
use Tamfuldev\Payment\PaymentManager;

it('resolves the vnpay gateway through the facade', function () {
    expect(Payment::gateway('vnpay'))->toBeInstanceOf(VnpayGateway::class);
});

it('resolves the default gateway when no name is given', function () {
    expect(Payment::gateway())->toBeInstanceOf(PaymentGateway::class);
});

it('throws for an unknown gateway', function () {
    Payment::gateway('does-not-exist');
})->throws(GatewayNotConfiguredException::class);

it('throws when no default gateway is configured', function () {
    config()->set('payment.default', '');

    app(PaymentManager::class)->gateway();
})->throws(GatewayNotConfiguredException::class);
