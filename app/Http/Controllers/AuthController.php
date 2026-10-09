<?php

namespace App\Http\Controllers;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\IAM\Models\Permission;
use App\Domain\IAM\Models\Role;
use App\Domain\IAM\Models\User;
use App\Domain\Platform\Models\PlatformUser;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantDomain;
use App\Infrastructure\MultiTenancy\Resolvers\TenantResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected TenantResolver $tenantResolver
    ) {}

    /**
     * Show the Login page.
     */
    public function showLogin(Request $request): View
    {
        $currentTenant = $this->tenantResolver->resolve($request);
        $tenants = Tenant::whereIn('status', ['active', 'trialing'])->get();

        return view('auth.login', compact('currentTenant', 'tenants'));
    }

    /**
     * Handle Login authentication.
     */
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'tenant_slug' => ['nullable', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($validated['remember'] ?? false);

        // 1. Check if user is a Platform Administrator
        $platformUser = PlatformUser::where('email', $validated['email'])->first();
        if ($platformUser && Hash::check($validated['password'], $platformUser->password)) {
            if (! $platformUser->is_active) {
                return $this->respondError($request, 'Պլատֆորմի ադմինիստրատորի հաշիվն ապաակտիվացված է:', 403);
            }

            Auth::guard('platform')->login($platformUser, $remember);
            $token = $platformUser->createToken('platform_token', ['platform:*'])->plainTextToken;

            return $this->respondSuccess($request, [
                'user' => [
                    'id' => $platformUser->id,
                    'name' => $platformUser->name,
                    'email' => $platformUser->email,
                    'role' => $platformUser->role,
                ],
                'token' => $token,
                'redirect' => '/',
            ], 'Բարի գալուստ, '.$platformUser->name);
        }

        // 2. Identify Tenant
        $tenant = null;
        if (! empty($validated['tenant_slug'])) {
            $tenant = Tenant::where('slug', $validated['tenant_slug'])
                ->orWhere('subdomain', $validated['tenant_slug'])
                ->first();
        } else {
            $tenant = $this->tenantResolver->resolve($request);
        }

        // 3. Find User
        $userQuery = User::withoutGlobalScopes();
        if ($tenant) {
            $userQuery->where('tenant_id', $tenant->id);
        }
        $user = $userQuery->where('email', $validated['email'])->first();

        // If no tenant was passed, but user email exists in exactly one tenant, auto-detect
        if (! $user && empty($validated['tenant_slug'])) {
            $matchingUsers = User::withoutGlobalScopes()->where('email', $validated['email'])->get();
            if ($matchingUsers->count() === 1) {
                $user = $matchingUsers->first();
                $tenant = Tenant::find($user->tenant_id);
            }
        }

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Մուտքանունը կամ գաղտնաբառը սխալ է:'],
            ]);
        }

        if (! $user->is_active) {
            return $this->respondError($request, 'Ձեր օգտահաշիվն ապաակտիվացված է: Դիմեք Ձեր ադմինիստրատորին:', 403);
        }

        if ($tenant && $tenant->isSuspended()) {
            return $this->respondError($request, 'Կազմակերպության աշխատանքային տարածքը կասեցված է:', 403);
        }

        // Login user via Web session guard
        Auth::guard('web')->login($user, $remember);
        $request->session()->put('tenant_id', $user->tenant_id);
        if ($tenant) {
            $request->session()->put('tenant_slug', $tenant->slug);
        }

        $token = $user->createToken('tenant_token', ['tenant:*'])->plainTextToken;

        return $this->respondSuccess($request, [
            'user' => [
                'id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'name' => $user->name,
                'email' => $user->email,
                'is_owner' => $user->is_owner,
            ],
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ] : null,
            'token' => $token,
            'redirect' => '/',
        ], 'Բարի վերադարձ, '.$user->name);
    }

    /**
     * Show the Partner Registration page.
     */
    public function showRegister(Request $request): View
    {
        $plans = Plan::where('is_active', true)->with('features')->get();

        return view('auth.register', compact('plans'));
    }

    /**
     * Handle Partner Registration.
     */
    public function register(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'alpha_dash', 'max:100', 'unique:tenants,subdomain'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'currency' => ['nullable', 'string', 'in:AMD,USD,EUR,RUB'],
            'plan_code' => ['nullable', 'string', 'exists:plans,code'],
        ]);

        $createdData = DB::transaction(function () use ($validated) {
            $plan = Plan::where('code', $validated['plan_code'] ?? 'starter')->first()
                ?? Plan::first();
            $trialDays = $plan?->trial_days ?? 14;

            $subdomain = Str::lower($validated['subdomain']);
            $slug = Str::slug($subdomain);

            // 1. Create Tenant
            $tenant = Tenant::create([
                'name' => $validated['company_name'],
                'legal_name' => $validated['company_name'],
                'slug' => $slug,
                'subdomain' => $subdomain,
                'status' => 'trialing',
                'country' => 'AM',
                'currency' => $validated['currency'] ?? 'AMD',
                'timezone' => 'Asia/Yerevan',
                'default_locale' => 'hy',
                'trial_ends_at' => now()->addDays($trialDays),
            ]);

            // 2. Register Subdomain
            TenantDomain::create([
                'tenant_id' => $tenant->id,
                'domain' => $tenant->subdomain.'.'.config('app.domain', 'erplannet.com'),
                'is_primary' => true,
                'is_verified' => true,
                'ssl_status' => 'active',
            ]);

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

            // 4. Create Default Roles
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
                'password' => Hash::make($validated['password']),
                'is_active' => true,
                'is_owner' => true,
                'locale' => 'hy',
            ]);

            $ownerUser->assignRole($ownerRole);

            return [
                'tenant' => $tenant,
                'user' => $ownerUser,
            ];
        });

        $user = $createdData['user'];
        $tenant = $createdData['tenant'];

        // Automatically login the newly registered partner
        Auth::guard('web')->login($user, true);
        $request->session()->put('tenant_id', $tenant->id);
        $request->session()->put('tenant_slug', $tenant->slug);

        $token = $user->createToken('tenant_token', ['tenant:*'])->plainTextToken;

        return $this->respondSuccess($request, [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'subdomain' => $tenant->subdomain,
            ],
            'token' => $token,
            'redirect' => '/',
        ], 'Շնորհավորում ենք: Ձեր կազմակերպության հաշիվը հաջողությամբ ստեղծվեց:');
    }

    /**
     * Show the Password & Email Reset page.
     */
    public function showPasswordReset(Request $request): View
    {
        $tenants = Tenant::whereIn('status', ['active', 'trialing'])->get();

        return view('auth.password-reset', compact('tenants'));
    }

    /**
     * Request Password Reset verification token.
     */
    public function requestPasswordReset(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'tenant_slug' => ['nullable', 'string'],
        ]);

        $userQuery = User::withoutGlobalScopes();
        if (! empty($validated['tenant_slug'])) {
            $tenant = Tenant::where('slug', $validated['tenant_slug'])
                ->orWhere('subdomain', $validated['tenant_slug'])
                ->first();
            if ($tenant) {
                $userQuery->where('tenant_id', $tenant->id);
            }
        }

        $user = $userQuery->where('email', $validated['email'])->first();

        if (! $user) {
            return $this->respondError($request, 'Նշված էլ. փոստով օգտատեր չի գտնվել:', 404);
        }

        // Generate 6-digit verification reset token
        $token = sprintf('%06d', mt_rand(100000, 999999));

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => $token,
            'tenant_id' => $user->tenant_id,
            'created_at' => now(),
        ]);

        return $this->respondSuccess($request, [
            'email' => $user->email,
            'token' => $token,
            'message' => 'Վերականգնման կոդը պատրաստ է:',
        ], "Վերականգնման կոդը գեներացված է: Կոդ՝ {$token}");
    }

    /**
     * Confirm Password Reset with token.
     */
    public function confirmPasswordReset(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->where('token', $validated['token'])
            ->where('created_at', '>=', now()->subHours(2))
            ->first();

        if (! $record) {
            return $this->respondError($request, 'Վերականգնման կոդը սխալ է կամ ժամկետանց:', 422);
        }

        $user = User::withoutGlobalScopes()->where('email', $validated['email'])->first();
        if (! $user) {
            return $this->respondError($request, 'Օգտատերը չի գտնվել:', 404);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return $this->respondSuccess($request, [
            'redirect' => '/login',
        ], 'Գաղտնաբառը հաջողությամբ թարմացվեց: Այժմ կարող եք մուտք գործել նոր գաղտնաբառով:');
    }

    /**
     * Lookup account / email by company subdomain or phone number.
     */
    public function lookupAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subdomain' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        if (empty($validated['subdomain']) && empty($validated['phone'])) {
            return response()->json([
                'success' => false,
                'message' => 'Նշեք կազմակերպության դոմենը կամ հեռախոսահամարը:',
            ], 422);
        }

        $user = null;
        $tenant = null;

        if (! empty($validated['subdomain'])) {
            $slug = Str::slug($validated['subdomain']);
            $tenant = Tenant::where('slug', $slug)
                ->orWhere('subdomain', Str::lower($validated['subdomain']))
                ->first();

            if ($tenant) {
                $user = User::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where('is_owner', true)
                    ->first()
                    ?? User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
            }
        }

        if (! $user && ! empty($validated['phone'])) {
            $user = User::withoutGlobalScopes()->where('phone', 'like', '%'.$validated['phone'].'%')->first();
            if ($user) {
                $tenant = Tenant::find($user->tenant_id);
            }
        }

        if (! $user || ! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Համապատասխան տվյալներով կազմակերպություն կամ հաշիվ չգտնվեց:',
            ], 404);
        }

        // Mask email for security: e.g. "a***m@gourmet.am"
        $parts = explode('@', $user->email);
        $namePart = $parts[0];
        $domainPart = $parts[1] ?? '';
        $maskedName = mb_substr($namePart, 0, 1).'***'.(mb_strlen($namePart) > 1 ? mb_substr($namePart, -1) : '');
        $maskedEmail = $maskedName.'@'.$domainPart;

        return response()->json([
            'success' => true,
            'data' => [
                'tenant_name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'masked_email' => $maskedEmail,
                'full_email' => $user->email, // useful for instant pre-fill
                'owner_name' => $user->name,
                'login_url' => url('/login?tenant='.$tenant->slug),
            ],
            'message' => 'Կազմակերպության հաշիվը գտնվել է:',
        ]);
    }

    /**
     * Handle Logout.
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::guard('web')->logout();
        Auth::guard('platform')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ելքը հաջողությամբ կատարվեց:',
                'redirect' => '/login',
            ]);
        }

        return redirect('/login')->with('success', 'Ելքը հաջողությամբ կատարվեց:');
    }

    /**
     * Helper to return standard success response.
     */
    protected function respondSuccess(Request $request, array $data, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ]);
        }

        $redirect = $data['redirect'] ?? '/';

        return redirect($redirect)->with('success', $message);
    }

    /**
     * Helper to return standard error response.
     */
    protected function respondError(Request $request, string $message, int $status = 400): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'AUTH_ERROR',
                    'message' => $message,
                ],
            ], $status);
        }

        return back()->withInput()->withErrors(['auth' => $message]);
    }
}
