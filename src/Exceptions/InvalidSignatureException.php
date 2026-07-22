<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Exceptions;

/**
 * Thrown when a webhook/IPN/return signature is invalid.
 * This is a security signal and must not be swallowed silently.
 */
final class InvalidSignatureException extends PaymentException
{
    public static function forGateway(string $gateway): self
    {
        return new self("Invalid signature for gateway '{$gateway}'. Refusing to process the payload.");
    }
}
