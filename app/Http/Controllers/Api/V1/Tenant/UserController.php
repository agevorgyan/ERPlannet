<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\IAM\Models\Role;
use App\Domain\IAM\Models\User;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            return response()->json(['success' => false, 'message' => 'Tenant not identified'], 400);
        }

        $query = User::where('tenant_id', $tenant->id)->with('roles');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $roleSlug = $request->query('role');
            $query->whereHas('roles', function ($q) use ($roleSlug) {
                $q->where('slug', $roleSlug);
            });
        }

        $users = $query->latest()->get()->map(function ($user) {
            $firstRole = $user->roles->first();

            return [
                'id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_owner' => (bool) $user->is_owner,
                'is_active' => (bool) $user->is_active,
                'status' => $user->is_active ? 'active' : 'inactive',
                'role' => $firstRole?->slug ?? ($user->is_owner ? 'owner' : 'member'),
                'role_name' => $firstRole?->name ?? ($user->is_owner ? 'Owner' : 'Member'),
                'created_at' => $user->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $users,
            'meta' => [
                'total' => $users->count(),
                'current_page' => 1,
                'last_page' => 1,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            return response()->json(['success' => false, 'message' => 'Tenant not identified'], 400);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->where('tenant_id', $tenant->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $password = $validated['password'] ?? Str::password(12);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($password),
            'is_active' => $validated['is_active'] ?? true,
            'is_owner' => false,
            'locale' => 'hy',
        ]);

        if (! empty($validated['role'])) {
            $role = Role::where('slug', $validated['role'])
                ->orWhere('id', $validated['role'])
                ->first();
            if ($role) {
                $user->assignRole($role);
            }
        }

        $user->load('roles');
        $firstRole = $user->roles->first();

        return response()->json([
            'success' => true,
            'message' => 'User invited/created successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_active' => (bool) $user->is_active,
                'status' => $user->is_active ? 'active' : 'inactive',
                'role' => $firstRole?->slug ?? 'member',
                'role_name' => $firstRole?->name ?? 'Member',
                'created_at' => $user->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();
        $user = User::where('tenant_id', $tenant->id)->with('roles')->findOrFail($id);

        $firstRole = $user->roles->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_owner' => (bool) $user->is_owner,
                'is_active' => (bool) $user->is_active,
                'status' => $user->is_active ? 'active' : 'inactive',
                'role' => $firstRole?->slug ?? 'member',
                'role_name' => $firstRole?->name ?? 'Member',
                'created_at' => $user->created_at?->toIso8601String(),
            ],
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();
        $user = User::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->where('tenant_id', $tenant->id)->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'role' => ['nullable', 'string'],
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'is_active']));

        if ($request->has('role') && ! $user->is_owner) {
            $role = Role::where('slug', $validated['role'])
                ->orWhere('id', $validated['role'])
                ->first();
            if ($role) {
                $user->roles()->sync([$role->id => ['tenant_id' => $tenant->id]]);
            }
        }

        $user->load('roles');
        $firstRole = $user->roles->first();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_owner' => (bool) $user->is_owner,
                'is_active' => (bool) $user->is_active,
                'status' => $user->is_active ? 'active' : 'inactive',
                'role' => $firstRole?->slug ?? 'member',
                'role_name' => $firstRole?->name ?? 'Member',
            ],
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();
        $user = User::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($user->is_owner) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant owner account cannot be deleted.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
