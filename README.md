# Payment Gateway

A unified abstraction for integrating **multiple domestic and international payment gateways** in
Laravel through a single, consistent API:

```php
Payment::gateway('vnpay')->charge($request);
```

Instead of learning each provider's signing scheme, endpoints and payload formats, you code against
one contract. Adding a new provider means writing one driver — the calling code never changes.

[![CI](https://img.shields.io/badge/CI-PHPStan%20max%20%7C%20Pest%20%7C%20Pint-brightgreen)](.github/workflows/ci.yml)
[![License](https://img.shields.io/badge/license-Apache--2.0-blue)](LICENSE)

---

## Features

- **One API for every gateway** — `charge`, webhook verification, and normalized events.
- **Security-first** — constant-time signature verification (`hash_equals`), no floats for money,
  secrets read only from config, thin webhook controller that never trusts client input blindly.
- **Correct money handling** — integer minor units via a `Money` value object, with proper
  zero-decimal support (VND, JPY) vs. 2-decimal currencies (USD, EUR).
- **Framework-friendly** — auto-discovered service provider, publishable config, a `Payment` facade,
  and an auto-registered webhook route.
- **Built to extend** — SOLID design (Manager/Factory + Strategy + DTOs), interface segregation so a
  gateway only implements the capabilities it actually supports.

## Requirements

- PHP `^8.2`
- Laravel `^11.0 || ^12.0`

## Installation

```bash
composer require tamfuldev/payment-gateway
```

Publish the config file:

```bash
php artisan vendor:publish --tag=payment-config
```

Then set your credentials in `.env` (see [`.env.example`](.env.example)):

```dotenv
PAYMENT_GATEWAY=vnpay

VNPAY_TMN_CODE=your-tmn-code
VNPAY_HASH_SECRET=your-hash-secret
VNPAY_PAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpc/pay.html
```

## Usage

### 1. Create a payment

```php
use Tamfuldev\Payment\Facades\Payment;
use Tamfuldev\Payment\Data\ChargeRequest;
use Tamfuldev\Payment\Enums\Currency;
use Tamfuldev\Payment\ValueObjects\Money;

$response = Payment::gateway('vnpay')->charge(new ChargeRequest(
    orderId:     'ORDER-123',
    amount:      Money::ofMajor('100000', Currency::VND), // 100,000 VND
    description: 'Order #123',
    returnUrl:   route('checkout.return'),
    ipAddress:   request()->ip(),
));

// For redirect-based gateways (VNPay, OnePay, ...):
return redirect()->away($response->redirectUrl);
```

`ChargeResponse` also carries `status`, `method` (redirect / QR / card / wallet), an optional
`qrContent`, and a `gatewayReference`.

> **Money:** always build amounts with `Money::ofMajor()` (accepts a string/int, never a float) or
> `Money::ofMinor()`. The library manages each currency's decimal places for you.

### 2. Handle webhooks / IPN

The package auto-registers a route: `POST /payment/webhook/{gateway}`. It verifies the signature,
normalizes the payload, and dispatches an event. You listen for the event to record the order:

```php
use Illuminate\Support\Facades\Event;
use Tamfuldev\Payment\Events\PaymentCompleted;

Event::listen(PaymentCompleted::class, function (PaymentCompleted $event): void {
    $payload = $event->payload; // Tamfuldev\Payment\Data\WebhookPayload

    // IMPORTANT — do this in your listener:
    //  1. Be idempotent: the same webhook may arrive more than once
    //     (key off $payload->gatewayReference / $payload->orderId).
    //  2. Reconcile server-side: verify $payload->amount matches your stored order
    //     BEFORE marking it paid. Never trust the amount/status blindly.
});
```

Available events:

| Event | When |
|---|---|
| `PaymentCompleted` | A verified webhook reports a successful payment |
| `PaymentFailed` | A verified webhook reports a failed/declined payment |

An invalid signature returns `400` and dispatches nothing.

### 3. Change the webhook prefix or middleware

```php
// config/payment.php
'webhook' => [
    'prefix'     => 'payment/webhook',
    'middleware' => ['api'],
],
```

## Supported gateways

| Group | Gateway | Status |
|---|---|---|
| Vietnam | **VNPay** | ✅ charge + webhook verification (reference driver) |
| Vietnam | MoMo, ZaloPay, OnePay, PayOS, VietQR | 🔜 planned |
| International | Stripe, PayPal, Paddle, 2Checkout | 🔜 planned |
| Wallet / Crypto | Binance Pay, Coinbase Commerce | 🔜 planned |

> The VNPay reference driver focuses on getting **charge + webhook verification** right (the
> security-critical core). Refund/query are follow-up work (they require extra fields such as
> `transactionDate` / `transactionType`).

## Adding a new gateway

1. Create `src/Gateways/<Name>/<Name>Gateway.php` extending `AbstractGateway` and implementing
   `PaymentGateway` (plus capability interfaces like `SupportsRefund` only if the gateway supports them).
2. Register a `create<Name>Driver()` method in `PaymentManager`.
3. Add a config block under `config/payment.php` (read secrets from `env()` **only** there).
4. Verify webhooks with a constant-time comparison (`hash_equals`).
5. Write tests first (signing + charge + webhook), mocking HTTP.

See [`CLAUDE.md`](CLAUDE.md) for the full architecture and security checklist.

## Architecture

```
Payment::gateway('x')
   → PaymentManager (Factory: pick the driver from config)
      → XGateway (Strategy: one class per provider)
         → DTOs in/out (ChargeRequest → ChargeResponse)
Webhook/IPN → WebhookController (verify signature → parse → dispatch event)
```

## Development

This repo ships a Docker toolchain, so you don't need PHP/Composer installed locally.

```bash
make build      # build the PHP 8.3 image
make install    # composer install
make test       # run the Pest suite
make coverage   # tests with a 90% coverage gate
make analyse    # PHPStan (level max)
make format     # Laravel Pint
make ci         # pint --test + phpstan + pest (the full gate)
make shell      # shell inside the container
```

If you do have PHP/Composer locally, the equivalent `composer` scripts work too
(`composer install`, `composer ci`, ...).

## Security

If you discover a security issue, please email the maintainer rather than opening a public issue.
Key guarantees the library enforces: constant-time signature checks, integer-only money, and secrets
sourced exclusively from configuration.

## License

Apache-2.0 — see [`LICENSE`](LICENSE).
