<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Events;

use Tamfuldev\Payment\Data\WebhookPayload;

/**
 * Dispatched when a valid webhook reports a successfully paid transaction.
 * The application must still reconcile the amount/order server-side before recording it.
 */
final readonly class PaymentCompleted
{
    public function __construct(
        public string $gateway,
        public WebhookPayload $payload,
    ) {
    }
}
