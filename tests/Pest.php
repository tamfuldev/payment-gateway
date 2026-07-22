<?php

declare(strict_types=1);

use Tamfuldev\Payment\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
| Only Feature tests need the Laravel environment (Testbench). Unit tests run
| standalone, so we don't bind TestCase there — keeps them fast and isolated.
*/
uses(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Shared helpers
|--------------------------------------------------------------------------
*/

/**
 * Compute a VNPay signature exactly like production to pin behavior (known-answer test).
 *
 * @param  array<string, string>  $params
 */
function vnpaySign(array $params, string $secret): string
{
    unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
    $params = array_filter($params, static fn (string $value): bool => $value !== '');
    ksort($params);

    $pairs = [];
    foreach ($params as $key => $value) {
        $pairs[] = urlencode($key) . '=' . urlencode($value);
    }

    return hash_hmac('sha512', implode('&', $pairs), $secret);
}
