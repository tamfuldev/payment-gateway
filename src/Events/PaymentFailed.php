<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Events;

use Tamfuldev\Payment\Data\WebhookPayload;

/**
 * Dispatched when a valid webhook reports a failed/declined transaction.
 */
final readonly class PaymentFailed
{
    public function __construct(
        public string $gateway,
        public WebhookPayload $payload,
    ) {
    }
}
