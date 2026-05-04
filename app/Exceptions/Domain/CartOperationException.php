<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use RuntimeException;

class CartOperationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $field = 'quantity',
    ) {
        parent::__construct($message);
    }
}
