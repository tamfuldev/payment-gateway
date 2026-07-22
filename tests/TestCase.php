<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Tests;

use Illuminate\Contracts\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Tamfuldev\Payment\PaymentServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [PaymentServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        /** @var Application $app */
        $app['config']->set('payment.default', 'vnpay');
        $app['config']->set('payment.gateways.vnpay', [
            'tmn_code' => 'TESTTMN',
            'hash_secret' => 'SECRET123',
            'pay_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpc/pay.html',
        ]);
        // Drop the 'api' middleware in tests to avoid depending on a rate limiter Testbench doesn't define.
        $app['config']->set('payment.webhook.middleware', []);
    }
}
