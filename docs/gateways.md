---
title: Gateways
description: Supported providers, the VNPay reference driver, and how to add your own gateway.
---

[← Back to home](index.html)

## Supported gateways

| Group | Gateway | Status |
|---|---|---|
| Vietnam | **VNPay** | ✅ charge + webhook verification (reference driver) |
| Vietnam | MoMo, ZaloPay, OnePay, PayOS, VietQR | 🔜 planned |
| International | Stripe, PayPal, Paddle, 2Checkout | 🔜 planned |
| Wallet / Crypto | Binance Pay, Coinbase Commerce | 🔜 planned |

You select a gateway by name; the calling code is identical across providers:

```php
Payment::gateway('vnpay')->charge($request);
Payment::gateway()->charge($request); // uses the default from config
```

## The VNPay driver

The reference driver implements the **redirect flow** and **HMAC-SHA512** webhook verification.

- `charge()` builds the signed payment URL; redirect the user to `$response->redirectUrl`.
- The wire amount is multiplied by 100 (a VNPay quirk) — handled internally via `Money`, never a float.
- Webhook signatures are verified with a constant-time comparison (`hash_equals`).

It intentionally covers **charge + webhook verification** — the security-critical core. Refund and
status-query are follow-up work (they require extra fields such as `transactionDate` /
`transactionType`).

## Architecture

```
Payment::gateway('x')
   → PaymentManager        (Factory: pick the driver from config)
      → XGateway           (Strategy: one class per provider)
         → DTOs in/out     (ChargeRequest → ChargeResponse)
Webhook/IPN → WebhookController (verify signature → parse → dispatch event)
```

Capabilities are split into small interfaces so a gateway only implements what it actually supports:

| Interface | Purpose |
|---|---|
| `PaymentGateway` | Required: `charge`, `verifySignature`, `parseWebhook` |
| `SupportsRefund` | Optional: `refund` |
| `SupportsQuery` | Optional: `queryStatus` (reconciliation) |

## Adding a new gateway

1. Create `src/Gateways/<Name>/<Name>Gateway.php` extending `AbstractGateway` and implementing
   `PaymentGateway` (plus capability interfaces only if the gateway supports them).
2. Register a `create<Name>Driver()` method in `PaymentManager`.
3. Add a config block under `config/payment.php` — read secrets from `env()` **only** there.
4. Verify webhooks with a constant-time comparison (`hash_equals`).
5. Write tests first (signing + charge + webhook), mocking HTTP.

Every provider-specific detail lives inside its own gateway namespace; nothing leaks into the core.

---

Next: [Webhooks & Events →](webhooks.html)
