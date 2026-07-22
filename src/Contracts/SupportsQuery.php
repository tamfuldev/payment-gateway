<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Contracts;

use Tamfuldev\Payment\Enums\PaymentStatus;

/**
 * Only gateways that can actively query a transaction's status implement this interface.
 * Useful for reconciliation when a webhook is lost.
 */
interface SupportsQuery
{
    public function queryStatus(string $orderId, string $gatewayReference): PaymentStatus;
}
