<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Integration\Models\TenantNotificationTemplate;
use App\Domain\Integration\Services\NotificationDispatcherService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    public function __construct(
        protected NotificationDispatcherService $notificationDispatcher
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $templates = TenantNotificationTemplate::where('tenant_id', $tenantId)->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $validated = $request->validate([
            'channel' => 'required|string|in:sms,telegram,email',
            'event' => 'required|string|max:60',
            'template_body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $template = TenantNotificationTemplate::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'channel' => $validated['channel'],
                'event' => $validated['event'],
            ],
            [
                'template_body' => $validated['template_body'],
                'is_active' => $validated['is_active'] ?? true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification template saved.',
            'data' => $template,
        ], 201);
    }

    public function update(Request $request, TenantNotificationTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'template_body' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Template updated.',
            'data' => $template,
        ]);
    }

    public function testSend(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?: $request->user()?->tenant_id;

        $validated = $request->validate([
            'channel' => 'required|string|in:sms,telegram,email',
            'event' => 'required|string',
            'recipient' => 'required|string',
            'variables' => 'nullable|array',
        ]);

        $result = $this->notificationDispatcher->send(
            tenantId: $tenantId,
            event: $validated['event'],
            recipient: $validated['recipient'],
            variables: $validated['variables'] ?? ['customer_name' => 'John Doe', 'order_number' => 'TEST-001'],
            channel: $validated['channel']
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] ? 'Test notification sent.' : ($result['error'] ?? 'Failed to send notification.'),
            'data' => $result,
        ]);
    }
}
