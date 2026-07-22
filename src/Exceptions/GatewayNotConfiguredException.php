<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Exceptions;

/**
 * Thrown when a requested gateway is missing required configuration (missing key/secret...).
 */
final class GatewayNotConfiguredException extends PaymentException
{
    public static function missingKey(string $gateway, string $key): self
    {
        return new self("Gateway '{$gateway}' is missing required config: '{$key}'.");
    }

    public static function unknownGateway(string $gateway): self
    {
        return new self("Payment gateway '{$gateway}' is not defined or has no registered driver.");
    }
}
