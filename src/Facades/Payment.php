<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Facades;

use Illuminate\Support\Facades\Facade;
use Tamfuldev\Payment\Contracts\PaymentGateway;
use Tamfuldev\Payment\PaymentManager;

/**
 * @method static PaymentGateway gateway(string|null $name = null)
 * @method static PaymentGateway driver(string|null $driver = null)
 *
 * @see PaymentManager
 */
final class Payment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PaymentManager::class;
    }
}
