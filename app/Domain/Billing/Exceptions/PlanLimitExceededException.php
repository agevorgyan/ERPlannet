<?php

namespace App\Domain\Billing\Exceptions;

use Exception;

class PlanLimitExceededException extends Exception
{
    public function __construct(
        public readonly string $featureCode,
        public readonly int|float $limit,
        public readonly int $currentUsage,
        string $message = ''
    ) {
        $message = $message ?: "Usage limit of {$limit} reached for feature '{$featureCode}'. Current usage: {$currentUsage}.";
        parent::__construct($message, 402); // HTTP 402 Payment Required
    }
}
