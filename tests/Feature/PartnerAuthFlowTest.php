<?php

namespace Tests\Feature;

use App\Domain\Billing\Models\Plan;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PartnerAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Plan::create([
            'name' => 'Starter Plan',
            'code' => 'starter',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'trial_days' => 14,
            'is_active' => true,
        ]);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Բարի վերադարձ');
    }

    public function test_tenant_user_can_login_via_web_post(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Partner LLC',
            'slug' => 'test-partner',
            'subdomain' => 'test-partner',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Arman Hakobyan',
            'email' => 'arman@testpartner.am',
            'password' => Hash::make('password123'),
            'is_owner' => true,
        ]);

        $response = $this->post('/login', [
            'tenant_slug' => 'test-partner',
            'email' => 'arman@testpartner.am',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Partner LLC',
            'slug' => 'test-partner',
            'subdomain' => 'test-partner',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Arman Hakobyan',
            'email' => 'arman@testpartner.am',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'tenant_slug' => 'test-partner',
            'email' => 'arman@testpartner.am',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('web');
    }

    public function test_register_page_renders_successfully(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
        $response->assertSee('Գրանցեք Ձեր կազմակերպությունը');
    }

    public function test_partner_can_register_new_tenant_and_is_authenticated(): void
    {
        $payload = [
            'company_name' => 'New Gourmet Brand',
            'subdomain' => 'new-gourmet',
            'owner_name' => 'Hasmik Grigoryan',
            'owner_email' => 'hasmik@newgourmet.am',
            'owner_phone' => '+37493112233',
            'password' => 'SecretPassword123!',
            'password_confirmation' => 'SecretPassword123!',
            'currency' => 'AMD',
            'plan_code' => 'starter',
        ];

        $response = $this->post('/register', $payload);

        $response->assertRedirect('/');

        // Verify Tenant was created
        $tenant = Tenant::where('subdomain', 'new-gourmet')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('New Gourmet Brand', $tenant->name);
        $this->assertEquals('trialing', $tenant->status);

        // Verify Owner User was created
        $user = User::withoutGlobalScopes()->where('email', 'hasmik@newgourmet.am')->first();
        $this->assertNotNull($user);
        $this->assertEquals($tenant->id, $user->tenant_id);
        $this->assertTrue($user->is_owner);

        // Verify user is logged in
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_password_reset_page_renders_successfully(): void
    {
        $response = $this->get('/password-reset');

        $response->assertStatus(200);
        $response->assertViewIs('auth.password-reset');
        $response->assertSee('Հաշվի վերականգնում');
    }

    public function test_password_reset_request_generates_token(): void
    {
        $tenant = Tenant::create([
            'name' => 'Demo Co',
            'slug' => 'democo',
            'subdomain' => 'democo',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Narek',
            'email' => 'narek@democo.am',
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->postJson('/password-reset/request', [
            'email' => 'narek@democo.am',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'narek@democo.am',
        ]);
    }

    public function test_password_reset_confirm_updates_password(): void
    {
        $tenant = Tenant::create([
            'name' => 'Demo Co',
            'slug' => 'democo',
            'subdomain' => 'democo',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Narek',
            'email' => 'narek@democo.am',
            'password' => Hash::make('old-password'),
        ]);

        $token = '123456';
        DB::table('password_reset_tokens')->insert([
            'email' => 'narek@democo.am',
            'token' => $token,
            'tenant_id' => $tenant->id,
            'created_at' => now(),
        ]);

        $response = $this->postJson('/password-reset/confirm', [
            'email' => 'narek@democo.am',
            'token' => $token,
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewPassword123!', $user->password));

        // Verify token was cleaned up
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'narek@democo.am',
        ]);
    }

    public function test_lookup_account_by_subdomain_or_phone(): void
    {
        $tenant = Tenant::create([
            'name' => 'Yerevan Bakery',
            'slug' => 'ybakery',
            'subdomain' => 'ybakery',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Vardan',
            'email' => 'vardan@ybakery.am',
            'phone' => '+37499887766',
            'password' => Hash::make('pass'),
            'is_owner' => true,
        ]);

        // Lookup by subdomain
        $response = $this->postJson('/password-reset/lookup-account', [
            'subdomain' => 'ybakery',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tenant_name', 'Yerevan Bakery');

        // Lookup by phone
        $responsePhone = $this->postJson('/password-reset/lookup-account', [
            'phone' => '887766',
        ]);

        $responsePhone->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tenant_name', 'Yerevan Bakery');
    }

    public function test_logout_invalidates_session_and_redirects(): void
    {
        $tenant = Tenant::create([
            'name' => 'Demo Co',
            'slug' => 'democo',
            'subdomain' => 'democo',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Narek',
            'email' => 'narek@democo.am',
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user, 'web');

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest('web');
    }
}
