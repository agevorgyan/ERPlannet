<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\IAM\Models\User;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TenantAuthController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function login(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TENANT_REQUIRED',
                    'message' => 'A valid tenant must be identified for tenant authentication.',
                ],
            ], 400);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Find user strictly scoped to this tenant
        $user = User::where('tenant_id', $tenant->id)
            ->where('email', $validated['email'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_DISABLED',
                    'message' => 'Your account has been deactivated. Please contact your company administrator.',
                ],
            ], 403);
        }

        $token = $user->createToken(
            name: 'tenant_api_token',
            abilities: ['tenant:*']
        )->plainTextToken;

        AuditLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'action' => 'user.login',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_owner' => $user->is_owner,
                    'roles' => $user->roles->pluck('slug'),
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'subdomain' => $tenant->subdomain,
                    'currency' => $tenant->currency,
                    'timezone' => $tenant->timezone,
                    'default_locale' => $tenant->default_locale,
                ],
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $this->tenantContext->getTenant();

        $user->load(['roles.permissions']);

        $permissions = $user->is_owner
            ? ['*']
            : $user->roles->flatMap->permissions->pluck('code')->unique()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_owner' => $user->is_owner,
                    'locale' => $user->locale,
                    'roles' => $user->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug]),
                    'permissions' => $permissions,
                ],
                'tenant' => [
                    'id' => $tenant?->id,
                    'name' => $tenant?->name,
                    'currency' => $tenant?->currency,
                    'timezone' => $tenant?->timezone,
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
