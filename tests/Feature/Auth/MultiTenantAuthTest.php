<?php

namespace Tests\Feature\Auth;

use App\Domain\IAM\Models\User;
use App\Domain\Platform\Models\PlatformUser;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MultiTenantAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_login_and_access_platform_endpoints(): void
    {
        $platformAdmin = PlatformUser::create([
            'name' => 'Root Admin',
            'email' => 'admin@erplannet.com',
            'password' => Hash::make('SecretPass123!'),
            'role' => 'superadmin',
        ]);

        $response = $this->postJson('/api/v1/platform/auth/login', [
            'email' => 'admin@erplannet.com',
            'password' => 'SecretPass123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);

        $token = $response->json('data.token');

        // Access /api/v1/platform/auth/me
        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/platform/auth/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('data.email', 'admin@erplannet.com');
    }

    public function test_tenant_user_can_login_when_tenant_is_identified(): void
    {
        $tenant = Tenant::create([
            'name' => 'Armenian Foods',
            'slug' => 'armfoods',
            'subdomain' => 'armfoods',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Davit Sargsyan',
            'email' => 'davit@armfoods.am',
            'password' => Hash::make('MyPassword123!'),
            'is_owner' => true,
        ]);

        $response = $this->withHeader('X-Tenant-Slug', 'armfoods')
            ->postJson('/api/v1/auth/login', [
                'email' => 'davit@armfoods.am',
                'password' => 'MyPassword123!',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tenant.slug', 'armfoods')
            ->assertJsonPath('data.user.email', 'davit@armfoods.am');
    }

    public function test_user_cannot_login_to_different_tenant_even_with_correct_password(): void
    {
        $tenantA = Tenant::create(['name' => 'Tenant A', 'slug' => 'tenant-a', 'subdomain' => 'tenant-a']);
        $tenantB = Tenant::create(['name' => 'Tenant B', 'slug' => 'tenant-b', 'subdomain' => 'tenant-b']);

        // User belongs strictly to Tenant A
        User::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Alice',
            'email' => 'alice@business.am',
            'password' => Hash::make('SecretKey456!'),
        ]);

        // Alice attempts to log in targeting Tenant B
        $response = $this->withHeader('X-Tenant-Slug', 'tenant-b')
            ->postJson('/api/v1/auth/login', [
                'email' => 'alice@business.am',
                'password' => 'SecretKey456!',
            ]);

        $response->assertStatus(422) // ValidationException: auth.failed
            ->assertJsonValidationErrors(['email']);
    }

    public function test_suspended_tenant_is_blocked_with_403(): void
    {
        $tenant = Tenant::create([
            'name' => 'Suspended Co',
            'slug' => 'suspended',
            'subdomain' => 'suspended',
            'status' => 'suspended',
            'suspended_reason' => 'Overdue payment',
        ]);

        $response = $this->withHeader('X-Tenant-Slug', 'suspended')
            ->postJson('/api/v1/auth/login', [
                'email' => 'any@suspended.am',
                'password' => 'password',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('error.code', 'TENANT_SUSPENDED');
    }
}
