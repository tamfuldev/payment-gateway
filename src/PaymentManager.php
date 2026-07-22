<?php

declare(strict_types=1);

namespace Tamfuldev\Payment;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Manager;
use InvalidArgumentException;
use Tamfuldev\Payment\Contracts\PaymentGateway;
use Tamfuldev\Payment\Exceptions\GatewayNotConfiguredException;
use Tamfuldev\Payment\Gateways\Vnpay\VnpayGateway;

/**
 * Main entry point of the package. Selects and builds a payment gateway driver from config.
 *
 * Usage: Payment::gateway('vnpay')->charge($request)
 *
 * Adding a new gateway = add a create<Name>Driver() method (see CLAUDE.md §3.3).
 */
class PaymentManager extends Manager
{
    /**
     * Resolve a payment gateway by name (null = the default gateway).
     */
    public function gateway(?string $name = null): PaymentGateway
    {
        $driver = $this->driver($name);

        if (! $driver instanceof PaymentGateway) {
            throw GatewayNotConfiguredException::unknownGateway($name ?? $this->getDefaultDriver());
        }

        return $driver;
    }

    public function getDefaultDriver(): string
    {
        $default = $this->config->get('payment.default');

        if (! is_string($default) || $default === '') {
            throw new GatewayNotConfiguredException('No default gateway configured (payment.default).');
        }

        return $default;
    }

    protected function createVnpayDriver(): PaymentGateway
    {
        return new VnpayGateway(
            $this->gatewayConfig('vnpay'),
            $this->container->make(HttpFactory::class),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function gatewayConfig(string $name): array
    {
        /** @var array<string, mixed> $config */
        $config = (array) $this->config->get("payment.gateways.{$name}", []);

        return $config;
    }

    /**
     * Wrap Manager's default error into a clear package-specific exception.
     */
    protected function createDriver($driver)
    {
        try {
            return parent::createDriver($driver);
        } catch (InvalidArgumentException) {
            throw GatewayNotConfiguredException::unknownGateway((string) $driver);
        }
    }
}
