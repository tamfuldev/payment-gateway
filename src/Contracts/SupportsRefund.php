<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Contracts;

use Tamfuldev\Payment\Data\RefundRequest;
use Tamfuldev\Payment\Data\RefundResponse;

/**
 * Only gateways capable of refunds implement this interface.
 */
interface SupportsRefund
{
    public function refund(RefundRequest $request): RefundResponse;
}
