<?php

namespace App\Domain\Billing\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class FeatureNotAvailableException extends Exception
{
    public function __construct(
        public readonly string $featureCode,
        string $message = ''
    ) {
        $message = $message ?: "Feature '{$featureCode}' is not included in the current subscription plan.";
        parent::__construct($message, 403);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FEATURE_NOT_INCLUDED',
                'message' => $this->getMessage(),
                'feature' => $this->featureCode,
            ],
        ], 403);
    }
}
