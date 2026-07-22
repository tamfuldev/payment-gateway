<?php

declare(strict_types=1);

use Tamfuldev\Payment\Exceptions\GatewayNotConfiguredException;
use Tamfuldev\Payment\Exceptions\InvalidSignatureException;
use Tamfuldev\Payment\Exceptions\PaymentException;

it('builds a missing-key exception message', function () {
    $e = GatewayNotConfiguredException::missingKey('vnpay', 'hash_secret');
    expect($e)->toBeInstanceOf(PaymentException::class)
        ->and($e->getMessage())->toContain('vnpay')
        ->and($e->getMessage())->toContain('hash_secret');
});

it('builds an unknown-gateway exception message', function () {
    $e = GatewayNotConfiguredException::unknownGateway('foobar');
    expect($e->getMessage())->toContain('foobar');
});

it('builds an invalid-signature exception message', function () {
    $e = InvalidSignatureException::forGateway('vnpay');
    expect($e)->toBeInstanceOf(PaymentException::class)
        ->and($e->getMessage())->toContain('vnpay');
});
