<?php

namespace App\Domain\Billing\Exceptions;

use Exception;

class FeatureNotAvailableException extends Exception
{
    public function __construct(
        public readonly string $featureCode,
        string $message = ''
    ) {
        $message = $message ?: "Feature '{$featureCode}' is not included in the current subscription plan.";
        parent::__construct($message, 403); // HTTP 403 Forbidden
    }
}
