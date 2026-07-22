---
title: Webhooks & Events
description: Verifying IPNs, the auto-registered webhook route, and listening to payment events.
---

[← Back to home](index.html)

## The webhook route

The package auto-registers a route:

```
POST /payment/webhook/{gateway}
```

For each incoming request it:

1. Resolves the named gateway.
2. **Verifies the signature** (constant-time). An invalid signature returns `400` and dispatches nothing.
3. Normalizes the payload into a `WebhookPayload` DTO.
4. Dispatches `PaymentCompleted` or `PaymentFailed`.

The controller is deliberately thin — all order-recording logic lives in your listener.

## Listening to events

```php
use Illuminate\Support\Facades\Event;
use Tamfuldev\Payment\Events\PaymentCompleted;

Event::listen(PaymentCompleted::class, function (PaymentCompleted $event): void {
    $payload = $event->payload; // Tamfuldev\Payment\Data\WebhookPayload

    // 1. Be idempotent: the same webhook may arrive more than once.
    //    Key off $payload->gatewayReference / $payload->orderId.
    // 2. Reconcile server-side: verify $payload->amount matches your stored order
    //    BEFORE marking it paid. Never trust the amount/status blindly.
});
```

### Available events

| Event | When |
|---|---|
| `PaymentCompleted` | A verified webhook reports a successful payment |
| `PaymentFailed` | A verified webhook reports a failed / declined payment |

Each event carries the gateway name (`$event->gateway`) and the normalized `WebhookPayload`.

## Two rules you must follow

> **Idempotency** — providers retry webhooks. Guard against double-crediting an order by keying on the
> transaction reference.
>
> **Server-side reconciliation** — a valid signature proves the payload came from the provider, not
> that the amount is what you expected. Always compare `$payload->amount` against your stored order
> before fulfilling it.

## Customizing the route

```php
// config/payment.php
'webhook' => [
    'prefix'     => 'payment/webhook',
    'middleware' => ['api'],
],
```

See the [Configuration Reference](configuration.html) for details.

---

Next: [Configuration Reference →](configuration.html)
