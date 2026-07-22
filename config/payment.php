<?php

declare(strict_types=1);

/**
 * Payment package configuration.
 *
 * SECURITY RULE: secrets are read from env() ONLY in this file (the single place),
 * never hardcoded and never via env() elsewhere — otherwise it breaks when the config is cached.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Default gateway
    |--------------------------------------------------------------------------
    | Used when Payment::gateway() is called without a name.
    */
    'default' => env('PAYMENT_GATEWAY', 'vnpay'),

    /*
    |--------------------------------------------------------------------------
    | Webhook route
    |--------------------------------------------------------------------------
    | Prefix for the webhook/IPN route: /{prefix}/{gateway}
    */
    'webhook' => [
        'prefix' => env('PAYMENT_WEBHOOK_PREFIX', 'payment/webhook'),
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-gateway configuration
    |--------------------------------------------------------------------------
    */
    'gateways' => [

        'vnpay' => [
            'tmn_code' => env('VNPAY_TMN_CODE', ''),
            'hash_secret' => env('VNPAY_HASH_SECRET', ''),
            'pay_url' => env('VNPAY_PAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpc/pay.html'),
            'api_url' => env('VNPAY_API_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'),
            'order_type' => env('VNPAY_ORDER_TYPE', 'other'),
            'locale' => env('VNPAY_LOCALE', 'vn'),
        ],

        // Add new gateways here (momo, zalopay, stripe, paypal...).
        // Use the /add-gateway command to scaffold them the right way.

    ],

];
