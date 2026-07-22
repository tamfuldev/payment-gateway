---
title: Configuration Reference
description: Every configuration key in config/payment.php explained.
---

[← Back to home](index.html)

All configuration lives in `config/payment.php` (publish it with
`php artisan vendor:publish --tag=payment-config`).

## Top-level keys

| Key | Type | Default | Description |
|---|---|---|---|
| `default` | string | `env('PAYMENT_GATEWAY', 'vnpay')` | Gateway used when `Payment::gateway()` is called without a name |
| `webhook.prefix` | string | `env('PAYMENT_WEBHOOK_PREFIX', 'payment/webhook')` | URL prefix for the webhook route (`/{prefix}/{gateway}`) |
| `webhook.middleware` | array | `['api']` | Middleware applied to the webhook route |
| `gateways` | array | — | Per-gateway configuration blocks (see below) |

## VNPay

```php
'gateways' => [
    'vnpay' => [
        'tmn_code'    => env('VNPAY_TMN_CODE', ''),
        'hash_secret' => env('VNPAY_HASH_SECRET', ''),
        'pay_url'     => env('VNPAY_PAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpc/pay.html'),
        'api_url'     => env('VNPAY_API_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'),
        'order_type'  => env('VNPAY_ORDER_TYPE', 'other'),
        'locale'      => env('VNPAY_LOCALE', 'vn'),
    ],
],
```

| Key | Required | Description |
|---|---|---|
| `tmn_code` | ✅ | Merchant terminal code from the VNPay portal |
| `hash_secret` | ✅ | Secret used to sign requests and verify webhooks (HMAC-SHA512) |
| `pay_url` | ✅ | Payment endpoint (sandbox vs. production) |
| `api_url` | — | Merchant API endpoint (used by future refund/query support) |
| `order_type` | — | VNPay order category, defaults to `other` |
| `locale` | — | `vn` or `en` |

## Environment variables

| Variable | Maps to |
|---|---|
| `PAYMENT_GATEWAY` | `default` |
| `PAYMENT_WEBHOOK_PREFIX` | `webhook.prefix` |
| `VNPAY_TMN_CODE` | `gateways.vnpay.tmn_code` |
| `VNPAY_HASH_SECRET` | `gateways.vnpay.hash_secret` |
| `VNPAY_PAY_URL` | `gateways.vnpay.pay_url` |
| `VNPAY_API_URL` | `gateways.vnpay.api_url` |
| `VNPAY_ORDER_TYPE` | `gateways.vnpay.order_type` |
| `VNPAY_LOCALE` | `gateways.vnpay.locale` |

> Keep secrets out of version control. The package reads them from the environment only through this
> config file, which keeps `php artisan config:cache` safe to use in production.

---

[← Back to home](index.html)
