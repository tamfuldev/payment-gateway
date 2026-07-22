<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Http\Controllers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tamfuldev\Payment\Enums\PaymentStatus;
use Tamfuldev\Payment\Events\PaymentCompleted;
use Tamfuldev\Payment\Events\PaymentFailed;
use Tamfuldev\Payment\PaymentManager;

/**
 * Receives webhooks/IPNs from payment gateways. The controller is intentionally thin:
 * verify signature -> parse -> dispatch event. All order-recording business logic lives in the
 * application's listener (which must be idempotent and reconcile the amount server-side).
 */
final class WebhookController
{
    public function __construct(
        private readonly PaymentManager $payments,
        private readonly Dispatcher $events,
    ) {
    }

    public function __invoke(Request $request, string $gateway): JsonResponse
    {
        $driver = $this->payments->gateway($gateway);

        /** @var array<string, string> $payload */
        $payload = array_map(
            static fn (mixed $value): string => is_scalar($value) ? (string) $value : '',
            $request->all(),
        );

        if (! $driver->verifySignature($payload)) {
            // Do not disclose details; just return 400 to avoid probing.
            return new JsonResponse(['message' => 'invalid signature'], 400);
        }

        $webhook = $driver->parseWebhook($payload);

        $event = $webhook->status === PaymentStatus::Completed
            ? new PaymentCompleted($gateway, $webhook)
            : new PaymentFailed($gateway, $webhook);

        $this->events->dispatch($event);

        return new JsonResponse(['message' => 'ok']);
    }
}
