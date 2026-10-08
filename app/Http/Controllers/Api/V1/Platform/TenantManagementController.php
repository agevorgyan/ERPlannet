<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\IAM\Models\Permission;
use App\Domain\IAM\Models\Role;
use App\Domain\IAM\Models\User;
use App\Domain\Platform\Models\PlatformAuditLog;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantDomain;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenants = Tenant::with(['activeSubscription.plan', 'domains'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $tenants->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $tenants->currentPage(),
                    'per_page' => $tenants->perPage(),
                    'total' => $tenants->total(),
                    'last_page' => $tenants->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:tenants,slug'],
            'subdomain' => ['required', 'string', 'max:100', 'unique:tenants,subdomain'],
            'custom_domain' => ['nullable', 'string', 'max:255', 'unique:tenants,custom_domain'],
            'country' => ['nullable', 'string', 'size:2'],
            'currency' => ['nullable', 'string', 'size:3'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'default_locale' => ['nullable', 'string', 'max:10'],
            'plan_code' => ['nullable', 'string', 'exists:plans,code'],

            // Initial owner user details
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255'],
            'owner_password' => ['required', 'string', 'min:8'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $tenant = DB::transaction(function () use ($validated, $request) {
            // 1. Create Tenant
            $plan = Plan::where('code', $validated['plan_code'] ?? 'starter')->first();
            $trialDays = $plan?->trial_days ?? 14;

            $tenant = Tenant::create([
                'name' => $validated['name'],
                'legal_name' => $validated['legal_name'] ?? null,
                'tax_number' => $validated['tax_number'] ?? null,
                'slug' => Str::slug($validated['slug']),
                'subdomain' => Str::lower($validated['subdomain']),
                'custom_domain' => $validated['custom_domain'] ?? null,
                'status' => 'trialing',
                'country' => $validated['country'] ?? 'AM',
                'currency' => $validated['currency'] ?? 'AMD',
                'timezone' => $validated['timezone'] ?? 'Asia/Yerevan',
                'default_locale' => $validated['default_locale'] ?? 'hy',
                'trial_ends_at' => now()->addDays($trialDays),
            ]);

            // 2. Register Subdomain in tenant_domains
            TenantDomain::create([
                'tenant_id' => $tenant->id,
                'domain' => $tenant->subdomain . '.' . config('app.domain', 'erplannet.com'),
                'is_primary' => true,
                'is_verified' => true,
                'ssl_status' => 'active',
            ]);

            // If custom domain provided
            if (!empty($validated['custom_domain'])) {
                TenantDomain::create([
                    'tenant_id' => $tenant->id,
                    'domain' => $validated['custom_domain'],
                    'is_primary' => false,
                    'is_verified' => false,
                    'ssl_status' => 'pending',
                ]);
            }

            // 3. Create Subscription
            if ($plan) {
                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'trialing',
                    'billing_cycle' => 'monthly',
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($trialDays),
                    'trial_ends_at' => now()->addDays($trialDays),
                    'auto_renew' => true,
                ]);
            }

            // 4. Create System Roles
            $ownerRole = Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'Owner',
                'slug' => 'owner',
                'description' => 'Account Owner with absolute permissions.',
                'is_system' => true,
            ]);

            Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Tenant Administrator.',
                'is_system' => true,
            ]);

            Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Standard Tenant Employee.',
                'is_system' => true,
            ]);

            // Attach all existing permissions to Owner role
            $allPermissions = Permission::all();
            if ($allPermissions->isNotEmpty()) {
                $ownerRole->permissions()->sync($allPermissions->pluck('id'));
            }

            // 5. Create Owner User
            $ownerUser = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'phone' => $validated['owner_phone'] ?? null,
                'password' => Hash::make($validated['owner_password']),
                'is_active' => true,
                'is_owner' => true,
                'locale' => $tenant->default_locale,
            ]);

            $ownerUser->assignRole($ownerRole);

            // Audit
            PlatformAuditLog::create([
                'platform_user_id' => $request->user()?->id,
                'action' => 'tenant.create',
                'entity_type' => 'Tenant',
                'entity_id' => $tenant->id,
                'tenant_id' => $tenant->id,
                'new_values' => $tenant->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $tenant;
        });

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully.',
            'data' => $tenant->load(['activeSubscription.plan', 'domains']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = Tenant::with(['domains', 'subscriptions.plan', 'users'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $tenant,
        ]);
    }

    public function suspend(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_reason' => $validated['reason'],
        ]);

        PlatformAuditLog::create([
            'platform_user_id' => $request->user()?->id,
            'action' => 'tenant.suspend',
            'entity_type' => 'Tenant',
            'entity_id' => $tenant->id,
            'tenant_id' => $tenant->id,
            'new_values' => ['reason' => $validated['reason']],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant has been suspended.',
            'data' => $tenant,
        ]);
    }

    public function activate(Request $request, string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspended_reason' => null,
        ]);

        PlatformAuditLog::create([
            'platform_user_id' => $request->user()?->id,
            'action' => 'tenant.activate',
            'entity_type' => 'Tenant',
            'entity_id' => $tenant->id,
            'tenant_id' => $tenant->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant has been activated.',
            'data' => $tenant,
        ]);
    }
}
