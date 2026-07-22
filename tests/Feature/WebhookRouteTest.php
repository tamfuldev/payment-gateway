<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Tamfuldev\Payment\Events\PaymentCompleted;
use Tamfuldev\Payment\Events\PaymentFailed;

it('dispatches PaymentCompleted when a valid webhook reports success', function () {
    Event::fake([PaymentCompleted::class, PaymentFailed::class]);

    $payload = [
        'vnp_TxnRef' => 'ORDER123',
        'vnp_Amount' => '10000000',
        'vnp_ResponseCode' => '00',
        'vnp_TransactionStatus' => '00',
        'vnp_TransactionNo' => '987654',
    ];
    $payload['vnp_SecureHash'] = vnpaySign($payload, 'SECRET123');

    $this->postJson('/payment/webhook/vnpay', $payload)->assertOk();

    Event::assertDispatched(PaymentCompleted::class, function (PaymentCompleted $event): bool {
        return $event->gateway === 'vnpay'
            && $event->payload->orderId === 'ORDER123';
    });
    Event::assertNotDispatched(PaymentFailed::class);
});

it('dispatches PaymentFailed when a valid webhook reports failure', function () {
    Event::fake([PaymentCompleted::class, PaymentFailed::class]);

    $payload = [
        'vnp_TxnRef' => 'ORDER123',
        'vnp_Amount' => '10000000',
        'vnp_ResponseCode' => '24',
        'vnp_TransactionStatus' => '02',
        'vnp_TransactionNo' => '0',
    ];
    $payload['vnp_SecureHash'] = vnpaySign($payload, 'SECRET123');

    $this->postJson('/payment/webhook/vnpay', $payload)->assertOk();

    Event::assertDispatched(PaymentFailed::class, function (PaymentFailed $event): bool {
        return $event->gateway === 'vnpay'
            && $event->payload->orderId === 'ORDER123';
    });
    Event::assertNotDispatched(PaymentCompleted::class);
});

it('returns 400 and dispatches nothing when the signature is invalid', function () {
    Event::fake([PaymentCompleted::class, PaymentFailed::class]);

    $this->postJson('/payment/webhook/vnpay', [
        'vnp_TxnRef' => 'ORDER123',
        'vnp_Amount' => '10000000',
        'vnp_ResponseCode' => '00',
        'vnp_TransactionStatus' => '00',
        'vnp_SecureHash' => 'deadbeef',
    ])->assertStatus(400);

    Event::assertNotDispatched(PaymentCompleted::class);
});
