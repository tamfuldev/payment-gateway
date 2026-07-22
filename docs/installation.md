---
title: Installation & Setup
description: Requirements, installation, and configuration of the Payment Gateway package.
---

[← Back to home](index.html)

## Requirements

- PHP `^8.2`
- Laravel `^11.0 || ^12.0`

## Install

```bash
composer require tamfuldev/payment-gateway
```

The service provider and the `Payment` facade are registered automatically via package discovery.

## Publish the configuration

```bash
php artisan vendor:publish --tag=payment-config
```

This copies `config/payment.php` into your application, where you can enable gateways and tune the
webhook route. See the [Configuration Reference](configuration.html) for every key.

## Set your credentials

Secrets are read from the environment. Add the keys for the gateways you use to your `.env`:

```dotenv
PAYMENT_GATEWAY=vnpay

# VNPay (sandbox)
VNPAY_TMN_CODE=your-tmn-code
VNPAY_HASH_SECRET=your-hash-secret
VNPAY_PAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpc/pay.html
```

> **Security:** never hardcode secrets or read them with `env()` outside `config/payment.php` —
> that breaks once the config is cached (`php artisan config:cache`).

## Create your first payment

```php
use Tamfuldev\Payment\Facades\Payment;
use Tamfuldev\Payment\Data\ChargeRequest;
use Tamfuldev\Payment\Enums\Currency;
use Tamfuldev\Payment\ValueObjects\Money;

$response = Payment::gateway('vnpay')->charge(new ChargeRequest(
    orderId:     'ORDER-123',
    amount:      Money::ofMajor('100000', Currency::VND),
    description: 'Order #123',
    returnUrl:   route('checkout.return'),
    ipAddress:   request()->ip(),
));

return redirect()->away($response->redirectUrl);
```

`ChargeResponse` also exposes `status`, `method` (redirect / QR / card / wallet), an optional
`qrContent`, and a `gatewayReference`.

### Working with money

Always build amounts with `Money`, never a raw float:

```php
Money::ofMajor('9.99', Currency::USD); // 999 minor units
Money::ofMajor('100000', Currency::VND); // 100000 (VND is zero-decimal)
Money::ofMinor(999, Currency::USD);      // exact, from minor units
```

The value object tracks each currency's decimal places for you, so you never lose cents to
floating-point rounding.

---

Next: [Gateways →](gateways.html)
