<?php

namespace Tests\Feature\BusinessCore;

use App\Domain\CRM\Models\Customer;
use App\Domain\IAM\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Food Express',
            'slug' => 'foodexpress',
            'subdomain' => 'foodexpress',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff Member',
            'email' => 'staff@express.am',
            'password' => Hash::make('password123'),
            'is_owner' => true,
        ]);

        $this->token = $this->user->createToken('token')->plainTextToken;
    }

    public function test_can_create_customer_with_default_address(): void
    {
        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'foodexpress',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/customers', [
            'first_name' => 'Armen',
            'last_name' => 'Poghosyan',
            'email' => 'armen@example.am',
            'phone' => '+37491112233',
            'company_name' => 'Poghosyan LLC',
            'address' => [
                'title' => 'Office',
                'city' => 'Yerevan',
                'address_line_1' => 'Baghramyan Ave 24',
                'floor' => '3',
                'apartment' => '12',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.phone', '+37491112233')
            ->assertJsonPath('data.addresses.0.city', 'Yerevan');

        $this->assertDatabaseHas('customers', [
            'tenant_id' => $this->tenant->id,
            'phone' => '+37491112233',
        ]);

        $this->assertDatabaseHas('customer_addresses', [
            'tenant_id' => $this->tenant->id,
            'address_line_1' => 'Baghramyan Ave 24',
            'is_default' => true,
        ]);
    }

    public function test_can_add_secondary_address_to_customer(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Sona',
            'phone' => '+37498765432',
        ]);

        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'foodexpress',
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/customers/{$customer->id}/addresses", [
            'title' => 'Home',
            'city' => 'Yerevan',
            'address_line_1' => 'Komitas Ave 10',
            'is_default' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.address_line_1', 'Komitas Ave 10');

        $this->assertCount(1, $customer->addresses);
    }
}
