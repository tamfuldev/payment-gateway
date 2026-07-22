---
title: Payment Gateway
description: A unified abstraction for integrating multiple domestic and international payment gateways in Laravel.
---

[![License](https://img.shields.io/badge/license-Apache--2.0-blue)](https://github.com/tamfuldev/payment-gateway/blob/develop/LICENSE)
[![CI](https://img.shields.io/badge/CI-PHPStan%20max%20%7C%20Pest%20%7C%20Pint-brightgreen)](https://github.com/tamfuldev/payment-gateway/actions)

## What is Payment Gateway?

**Payment Gateway** is a Laravel package that gives you one consistent API to integrate **many
domestic and international payment providers** — instead of learning each provider's signing scheme,
endpoints, and payload formats.

```php
Payment::gateway('vnpay')->charge($request);
```

Adding a new provider means writing one driver — your calling code never changes.

### Key features

- **One API for every gateway** — `charge`, webhook verification, and normalized events.
- **Security-first** — constant-time signature checks (`hash_equals`), no floats for money, secrets
  read only from config, a thin webhook controller that never blindly trusts client input.
- **Correct money handling** — integer minor units via a `Money` value object, with proper
  zero-decimal support (VND, JPY) vs. 2-decimal currencies (USD, EUR).
- **Framework-friendly** — auto-discovered service provider, publishable config, a `Payment` facade,
  and an auto-registered webhook route.
- **Built to extend** — SOLID design (Manager/Factory + Strategy + DTOs) with interface segregation.

## Quick Start

```bash
composer require tamfuldev/payment-gateway
php artisan vendor:publish --tag=payment-config
```

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

return redirect()->away($response->redirectUrl);
```

## Documentation

| Guide | What it covers |
|---|---|
| [Installation & Setup](installation.html) | Requirements, install, publish and fill in configuration |
| [Gateways](gateways.html) | Supported providers, the VNPay driver, and how to add your own |
| [Webhooks & Events](webhooks.html) | Verifying IPNs, the auto-registered route, and listening to events |
| [Configuration Reference](configuration.html) | Every config key explained |

## Requirements

- PHP `^8.2`
- Laravel `^11.0 || ^12.0`

## License

Released under the [Apache-2.0 License](https://github.com/tamfuldev/payment-gateway/blob/develop/LICENSE).
