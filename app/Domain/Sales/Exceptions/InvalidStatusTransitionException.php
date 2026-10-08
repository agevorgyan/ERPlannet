<?php

namespace App\Domain\Sales\Exceptions;

use Exception;

class InvalidStatusTransitionException extends Exception
{
    public function __construct(
        public readonly string $fromStatus,
        public readonly string $toStatus,
        string $message = ''
    ) {
        $message = $message ?: "Cannot transition order status from '{$fromStatus}' to '{$toStatus}'.";
        parent::__construct($message, 422);
    }
}
