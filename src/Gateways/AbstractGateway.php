<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Gateways;

use Illuminate\Http\Client\Factory as HttpFactory;
use Tamfuldev\Payment\Contracts\PaymentGateway;
use Tamfuldev\Payment\Exceptions\GatewayNotConfiguredException;

/**
 * Base class for every gateway: holds config, provides an injectable HTTP client
 * (so it can be mocked in tests), and shared security helpers. It contains no
 * provider-specific logic.
 */
abstract class AbstractGateway implements PaymentGateway
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected readonly array $config,
        protected readonly HttpFactory $http,
    ) {
    }

    /**
     * Get a required config value; fail fast with a clear error if missing (no guessing).
     */
    protected function requireConfig(string $key): string
    {
        $value = $this->config[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw GatewayNotConfiguredException::missingKey($this->name(), $key);
        }

        return $value;
    }

    protected function config(string $key, string $default = ''): string
    {
        $value = $this->config[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    /**
     * Compare signatures in constant time to prevent timing attacks.
     * ALL gateways must use this instead of `==`/`===` when comparing hashes.
     */
    protected function signaturesMatch(string $expected, string $provided): bool
    {
        return hash_equals($expected, $provided);
    }
}
