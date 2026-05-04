<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use RuntimeException;

class CheckoutOperationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $field = 'cart',
    ) {
        parent::__construct($message);
    }
}
