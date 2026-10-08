<?php

namespace App\Domain\Billing\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class PlanLimitExceededException extends Exception
{
    public function __construct(
        public readonly string $featureCode,
        public readonly int|float $limit,
        public readonly int $currentUsage,
        string $message = ''
    ) {
        $message = $message ?: "Usage limit of {$limit} reached for feature '{$featureCode}'. Current usage: {$currentUsage}.";
        parent::__construct($message, 402);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'PLAN_LIMIT_EXCEEDED',
                'message' => $this->getMessage(),
                'feature' => $this->featureCode,
                'limit' => is_infinite($this->limit) ? 'unlimited' : $this->limit,
                'current_usage' => $this->currentUsage,
            ],
        ], 402);
    }
}
