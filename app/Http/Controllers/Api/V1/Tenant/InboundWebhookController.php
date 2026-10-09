<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Integration\Models\TenantIntegration;
use App\Domain\Integration\Services\WebhookIngressService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboundWebhookController extends Controller
{
    public function __construct(
        protected WebhookIngressService $ingressService
    ) {}

    public function handleWooCommerce(string $integrationId, Request $request): JsonResponse
    {
        // Integration lookup without tenant context (inbound webhook from external internet)
        $integration = TenantIntegration::withoutGlobalScopes()->find($integrationId);

        if (! $integration) {
            return response()->json([
                'success' => false,
                'message' => 'Integration endpoint not found.',
            ], 404);
        }

        $result = $this->ingressService->handleWooCommerceWebhook($integration, $request);

        return response()->json($result, $result['success'] ? 200 : 401);
    }
}
