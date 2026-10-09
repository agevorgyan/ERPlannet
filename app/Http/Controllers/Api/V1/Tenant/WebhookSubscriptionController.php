<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Integration\Models\TenantWebhookDelivery;
use App\Domain\Integration\Models\TenantWebhookSubscription;
use App\Domain\Integration\Services\WebhookDispatcherService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookSubscriptionController extends Controller
{
    public function __construct(
        protected WebhookDispatcherService $webhookDispatcherService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $subs = TenantWebhookSubscription::where('tenant_id', $tenantId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subs,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'target_url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'secret' => 'nullable|string',
            'headers' => 'nullable|array',
        ]);

        // Validate SSRF
        $this->webhookDispatcherService->validateSsrf($validated['target_url']);

        $secret = $validated['secret'] ?? Str::random(32);

        $subscription = new TenantWebhookSubscription([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'target_url' => $validated['target_url'],
            'events' => $validated['events'],
            'is_active' => true,
            'headers' => $validated['headers'] ?? [],
        ]);
        $subscription->setSecret($secret);
        $subscription->save();

        return response()->json([
            'success' => true,
            'message' => 'Webhook subscription registered.',
            'data' => array_merge($subscription->toArray(), ['secret' => $secret]),
        ], 201);
    }

    public function destroy(TenantWebhookSubscription $subscription): JsonResponse
    {
        $subscription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Webhook subscription deleted.',
        ]);
    }

    public function deliveries(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $deliveries = TenantWebhookDelivery::where('tenant_id', $tenantId)
            ->latest('created_at')
            ->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $deliveries->items(),
            'meta' => [
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
                'total' => $deliveries->total(),
            ],
        ]);
    }

    public function retry(TenantWebhookDelivery $delivery): JsonResponse
    {
        $sub = $delivery->subscription;
        if (! $sub) {
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }

        $redelivery = $this->webhookDispatcherService->deliver($sub, $delivery->event_name, $delivery->payload);

        return response()->json([
            'success' => $redelivery->status === 'delivered',
            'message' => "Webhook retry status: {$redelivery->status}",
            'data' => $redelivery,
        ]);
    }
}
