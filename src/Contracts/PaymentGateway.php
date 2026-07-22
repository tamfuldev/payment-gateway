<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Contracts;

use Tamfuldev\Payment\Data\ChargeRequest;
use Tamfuldev\Payment\Data\ChargeResponse;
use Tamfuldev\Payment\Data\WebhookPayload;

/**
 * The minimum contract every payment gateway must satisfy.
 * Extended capabilities (refund, recurring...) live in separate interfaces to honor ISP.
 */
interface PaymentGateway
{
    /**
     * Gateway identifier (matches the key under config `payment.gateways`).
     */
    public function name(): string;

    /**
     * Initiate a payment.
     */
    public function charge(ChargeRequest $request): ChargeResponse;

    /**
     * Verify the signature of a webhook/IPN/return payload.
     * Must use a constant-time comparison (hash_equals).
     *
     * @param  array<string, string>  $payload
     */
    public function verifySignature(array $payload): bool;

    /**
     * Normalize a (verified) webhook payload into a DTO.
     *
     * @param  array<string, string>  $payload
     */
    public function parseWebhook(array $payload): WebhookPayload;
}
