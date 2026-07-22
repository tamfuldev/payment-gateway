<?php

declare(strict_types=1);

namespace Tamfuldev\Payment\Exceptions;

use RuntimeException;

/**
 * Root exception of the package. Every payment-related error extends this
 * so the application can catch them consistently.
 */
class PaymentException extends RuntimeException
{
}
