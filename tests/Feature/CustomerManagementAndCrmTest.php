<?php

namespace Tests\Feature;

use App\Domain\Branch\Models\Branch;
use App\Domain\CRM\Actions\AddCustomerAddressAction;
use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerActivity;
use App\Domain\CRM\Models\CustomerAddress;
use App\Domain\CRM\Models\CustomerLoyaltyTransaction;
use App\Domain\CRM\Models\CustomerNote;
use App\Domain\CRM\Policies\CustomerPolicy;
use App\Domain\CRM\Services\CustomerAnalyticsService;
use App\Domain\CRM\Services\CustomerLoyaltyService;
use App\Domain\CRM\Services\PhoneNumberNormalizer;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementAndCrmTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();
        $this->branch = Branch::where('tenant_id', $this->tenant->id)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);
    }

    /**
     * 1. Ֆիզիկական անձի ստեղծում (Create individual customer)
     */
    public function test_01_can_create_individual_customer(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers', [
                'type' => Customer::TYPE_INDIVIDUAL,
                'first_name' => 'Արմեն',
                'last_name' => 'Սարգսյան',
                'middle_name' => 'Կարենի',
                'birth_date' => '1990-05-15',
                'gender' => 'male',
                'phone' => '+37494112233',
                'email' => 'armen.sargsyan@example.am',
                'notes' => 'Նախընտրում է առավոտյան առաքում',
                'address' => [
                    'city' => 'Երևան',
                    'street' => 'Կոմիտաս',
                    'building' => '15',
                    'apartment' => '8',
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', Customer::TYPE_INDIVIDUAL)
            ->assertJsonPath('data.first_name', 'Արմեն')
            ->assertJsonPath('data.last_name', 'Սարգսյան')
            ->assertJsonPath('data.phone', '+37494112233');

        $customerId = $response->json('data.id');
        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Արմեն',
            'phone' => '+37494112233',
        ]);

        $this->assertDatabaseHas('customer_individuals', [
            'customer_id' => $customerId,
            'first_name' => 'Արմեն',
            'last_name' => 'Սարգսյան',
            'birth_date' => '1990-05-15',
        ]);

        $this->assertDatabaseHas('customer_loyalty_accounts', [
            'customer_id' => $customerId,
            'current_tier' => Customer::TIER_BASIC,
        ]);
    }

    /**
     * 2. Իրավաբանական անձի ստեղծում (Create company customer with contacts)
     */
    public function test_02_can_create_company_customer(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers', [
                'type' => Customer::TYPE_COMPANY,
                'company_name' => 'Էյ Բի Սի Տրեյդինգ ՍՊԸ',
                'legal_name' => '«Էյ Բի Սի Տրեյդինգ» Սահմանափակ Պատասխանատվությամբ Ընկերություն',
                'tax_id' => '02938475',
                'phone' => '+37410556677',
                'email' => 'office@abctrading.am',
                'legal_address' => 'ք․ Երևան, Վազգեն Սարգսյան 26/1',
                'contacts' => [
                    [
                        'name' => 'Գոռ Հարությունյան',
                        'position' => 'Գնումների մենեջեր',
                        'phone' => '+37493889900',
                        'email' => 'gor@abctrading.am',
                        'is_primary' => true,
                    ],
                    [
                        'name' => 'Սոնա Պետրոսյան',
                        'position' => 'Գլխավոր հաշվապահ',
                        'phone' => '+37491776655',
                        'email' => 'sona@abctrading.am',
                        'is_primary' => false,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', Customer::TYPE_COMPANY)
            ->assertJsonPath('data.company_name', 'Էյ Բի Սի Տրեյդինգ ՍՊԸ')
            ->assertJsonPath('data.tax_id', '02938475');

        $customerId = $response->json('data.id');
        $this->assertDatabaseHas('customer_companies', [
            'customer_id' => $customerId,
            'legal_name' => '«Էյ Բի Սի Տրեյդինգ» Սահմանափակ Պատասխանատվությամբ Ընկերություն',
            'tax_id' => '02938475',
        ]);

        $this->assertDatabaseHas('customer_contacts', [
            'customer_id' => $customerId,
            'name' => 'Գոռ Հարությունյան',
            'is_primary' => true,
        ]);
    }

    /**
     * 3. Պարտադիր դաշտերի վավերացում (Validation of required fields)
     */
    public function test_03_validation_of_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /**
     * 4. Հայկական և ռուսական հեռախոսահամարների վավերացում և նորմալացում (E.164)
     */
    public function test_04_armenian_and_russian_phone_normalization(): void
    {
        // Armenian local number normalizes to +374
        $this->assertEquals('+37494123456', PhoneNumberNormalizer::normalize('094 12-34-56'));
        $this->assertEquals('+37410223344', PhoneNumberNormalizer::normalize('010 223344'));
        $this->assertEquals('+37477889900', PhoneNumberNormalizer::normalize('+374 77 88-99-00'));

        // Russian number normalizes to +7
        $this->assertEquals('+79161234567', PhoneNumberNormalizer::normalize('8 (916) 123-45-67', 'RU'));
        $this->assertEquals('+79255554433', PhoneNumberNormalizer::normalize('+7 925 555-44-33'));

        // Create customer with unformatted local Armenian number
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers', [
                'type' => Customer::TYPE_INDIVIDUAL,
                'first_name' => 'Տիգրան',
                'last_name' => 'Վարդանյան',
                'phone' => '093 44-55-66',
            ]);

        $response->assertStatus(201);
        $this->assertEquals('+37493445566', $response->json('data.phone'));
    }

    /**
     * 5. Կրկնվող հեռախոսահամարի հայտնաբերում (Duplicate phone detection warning)
     */
    public function test_05_duplicate_phone_detection_warning(): void
    {
        // Create initial customer
        $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers', [
                'type' => Customer::TYPE_INDIVIDUAL,
                'first_name' => 'Անուշ',
                'last_name' => 'Սիմոնյան',
                'phone' => '+37498112233',
            ]);

        // Check duplicate with same phone in different format
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers/check-duplicate', [
                'phone' => '098 11-22-33',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('has_duplicate', true);

        $duplicates = $response->json('duplicates');
        $this->assertNotEmpty($duplicates);
        $this->assertEquals('+37498112233', $duplicates[0]['phone']);
        $this->assertContains('phone', $duplicates[0]['matches']);
    }

    /**
     * 6. Էլ. փոստի վավերացում (Email validation)
     */
    public function test_06_email_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers', [
                'type' => Customer::TYPE_INDIVIDUAL,
                'first_name' => 'Արամ',
                'last_name' => 'Մարտիրոսյան',
                'phone' => '+37491223344',
                'email' => 'invalid-email-string',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * 7. ՀՎՀՀ-ի վավերացում (Tax ID duplicate check)
     */
    public function test_07_hvhh_tax_id_duplicate_check(): void
    {
        $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers', [
                'type' => Customer::TYPE_COMPANY,
                'company_name' => 'Արտ ՍՊԸ',
                'tax_id' => '08877665',
                'phone' => '+37410998877',
            ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers/check-duplicate', [
                'tax_id' => '08877665',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('has_duplicate', true);

        $duplicates = $response->json('duplicates');
        $this->assertContains('tax_id', $duplicates[0]['matches']);
    }

    /**
     * 8. Բազմաթիվ հասցեների կառավարում (Multiple addresses management)
     */
    public function test_08_multiple_addresses_management(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-ADDR1',
            'first_name' => 'Դավիթ',
            'last_name' => 'Հովհաննիսյան',
            'phone' => '+37494778899',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $addAddressAction = app(AddCustomerAddressAction::class);

        $address1 = $addAddressAction->execute($customer, [
            'title' => 'Տուն',
            'city' => 'Երևան',
            'street' => 'Թումանյան',
            'building' => '12',
            'apartment' => '4',
            'is_default' => true,
        ]);

        $address2 = $addAddressAction->execute($customer, [
            'title' => 'Գրասենյակ',
            'city' => 'Երևան',
            'street' => 'Ամիրյան',
            'building' => '24',
            'floor' => '3',
            'is_default' => false,
        ]);

        $this->assertEquals(2, $customer->addresses()->count());
        $this->assertEquals('Երևան, Թումանյան 12, բն. 4', $address1->formatted_address);
        $this->assertEquals('Երևան, Ամիրյան 24, հարկ 3', $address2->formatted_address);
    }

    /**
     * 9. Հիմնական և վերջին առաքման հասցեների տարբերակում (Default vs Last Used Address)
     */
    public function test_09_default_vs_last_used_delivery_address_distinction(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-ADDR2',
            'first_name' => 'Մարիամ',
            'last_name' => 'Բաբայան',
            'phone' => '+37495112233',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $addAddressAction = app(AddCustomerAddressAction::class);

        $defaultAddr = $addAddressAction->execute($customer, [
            'title' => 'Հիմնական',
            'city' => 'Երևան',
            'street' => 'Սայաթ-Նովա',
            'building' => '5',
            'is_default' => true,
        ]);

        $lastUsedAddr = $addAddressAction->execute($customer, [
            'title' => 'Ամառանոց',
            'city' => 'Դիլիջան',
            'street' => 'Կալինինի',
            'building' => '30',
            'is_default' => false,
            'is_last_used' => true,
        ]);

        $freshCustomer = $customer->fresh(['defaultAddress', 'lastUsedAddress']);
        $this->assertEquals($defaultAddr->id, $freshCustomer->defaultAddress->id);
        $this->assertEquals($lastUsedAddr->id, $freshCustomer->lastUsedAddress->id);
        $this->assertNotEquals($freshCustomer->defaultAddress->id, $freshCustomer->lastUsedAddress->id);
    }

    /**
     * 10. Պատվերի հասցեի snapshot-ի պահպանում (Order address snapshot immutability)
     */
    public function test_10_preservation_of_delivery_address_snapshot_on_orders(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-SNAP1',
            'first_name' => 'Կարեն',
            'last_name' => 'Գասպարյան',
            'phone' => '+37491334455',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $address = CustomerAddress::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'city' => 'Երևան',
            'street' => 'Տերյան',
            'building' => '10',
            'apartment' => '4',
            'is_default' => true,
        ]);

        // Place order with address snapshot
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'customer_address_id' => $address->id,
            'order_number' => 'ORD-TEST-001',
            'status' => 'delivered',
            'currency' => 'AMD',
            'subtotal' => 15000,
            'total' => 15000,
            'delivery_address_snapshot' => $address->toSnapshot(),
            'placed_at' => now(),
        ]);

        // Later, customer changes their address
        $address->update([
            'street' => 'Մաշտոցի',
            'building' => '50',
            'apartment' => '12',
        ]);

        // The order's snapshot remains frozen at original values
        $freshOrder = $order->fresh();
        $this->assertEquals('Տերյան', $freshOrder->delivery_address_snapshot['street']);
        $this->assertEquals('10', $freshOrder->delivery_address_snapshot['building']);
        $this->assertEquals('4', $freshOrder->delivery_address_snapshot['apartment']);
        $this->assertNotEquals($address->fresh()->street, $freshOrder->delivery_address_snapshot['street']);
    }

    /**
     * 11. Պատվերների ճիշտ վիճակագրություն (Customer order statistics calculation)
     */
    public function test_11_customer_order_statistics_calculation(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-STAT1',
            'first_name' => 'Էդգար',
            'last_name' => 'Մինասյան',
            'phone' => '+37494556677',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        // 2 delivered orders
        Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-STAT-01',
            'status' => 'delivered',
            'currency' => 'AMD',
            'subtotal' => 20000,
            'total' => 20000,
            'placed_at' => now()->subDays(10),
        ]);

        Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-STAT-02',
            'status' => 'delivered',
            'currency' => 'AMD',
            'subtotal' => 30000,
            'total' => 30000,
            'placed_at' => now()->subDays(2),
        ]);

        $analyticsService = app(CustomerAnalyticsService::class);
        $customer = $analyticsService->recalculate($customer);

        $this->assertEquals(2, $customer->orders_count);
        $this->assertEquals(50000.00, (float) $customer->total_spent);
        $this->assertEquals(50000.00, (float) $customer->lifetime_value);
        $this->assertNotNull($customer->first_ordered_at);
        $this->assertNotNull($customer->last_ordered_at);
    }

    /**
     * 12. Միջին չեքի ճիշտ հաշվարկ (Average Order Value calculation)
     */
    public function test_12_average_order_value_calculation(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-AOV1',
            'first_name' => 'Ռուբեն',
            'last_name' => 'Կիրակոսյան',
            'phone' => '+37493667788',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-AOV-01',
            'status' => 'delivered',
            'currency' => 'AMD',
            'subtotal' => 10000,
            'total' => 10000,
            'placed_at' => now()->subDays(5),
        ]);

        Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-AOV-02',
            'status' => 'delivered',
            'currency' => 'AMD',
            'subtotal' => 30000,
            'total' => 30000,
            'placed_at' => now()->subDays(1),
        ]);

        $analyticsService = app(CustomerAnalyticsService::class);
        $customer = $analyticsService->recalculate($customer);

        // (10,000 + 30,000) / 2 = 20,000
        $this->assertEquals(20000.00, (float) $customer->average_order_value);
    }

    /**
     * 13. Վերադարձների և չեղարկված պատվերների ճիշտ հաշվարկ (Cancelled & Refunded orders)
     */
    public function test_13_returned_and_cancelled_orders_handling_in_stats(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-CANCEL1',
            'first_name' => 'Հայկ',
            'last_name' => 'Պողոսյան',
            'phone' => '+37494889911',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        // 1 completed order
        Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-RET-01',
            'status' => 'delivered',
            'payment_status' => 'paid',
            'currency' => 'AMD',
            'subtotal' => 40000,
            'total' => 40000,
            'placed_at' => now()->subDays(3),
        ]);

        // 1 cancelled order
        Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-RET-02',
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'currency' => 'AMD',
            'subtotal' => 15000,
            'total' => 15000,
            'placed_at' => now()->subDays(2),
        ]);

        // 1 refunded order
        Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-RET-03',
            'status' => 'delivered',
            'payment_status' => 'refunded',
            'currency' => 'AMD',
            'subtotal' => 8000,
            'total' => 8000,
            'placed_at' => now()->subDays(1),
        ]);

        $analyticsService = app(CustomerAnalyticsService::class);
        $customer = $analyticsService->recalculate($customer);

        $this->assertEquals(3, $customer->orders_count);
        $this->assertEquals(1, $customer->canceled_orders_count);
        $this->assertEquals(1, $customer->returned_orders_count);
    }

    /**
     * 14. Միավորների կուտակում (Loyalty points accumulation)
     */
    public function test_14_loyalty_points_accumulation(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-LOYAL1',
            'first_name' => 'Վահե',
            'last_name' => 'Միրզոյան',
            'phone' => '+37498778899',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $loyaltyService = app(CustomerLoyaltyService::class);
        $transaction = $loyaltyService->recordTransaction(
            $customer,
            'earn',
            500.00,
            'Գնման բոնուս ORD-1001'
        );

        $this->assertEquals(500.00, (float) $transaction->points_delta);
        $this->assertEquals(500.00, (float) $transaction->balance_after);

        $account = $loyaltyService->getOrCreateAccount($customer)->fresh();
        $this->assertEquals(500.00, (float) $account->points_balance);
        $this->assertEquals(500.00, (float) $account->lifetime_points_earned);
    }

    /**
     * 15. Միավորների օգտագործում (Loyalty points redemption)
     */
    public function test_15_loyalty_points_redemption(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-LOYAL2',
            'first_name' => 'Արսեն',
            'last_name' => 'Դանիելյան',
            'phone' => '+37491443322',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $loyaltyService = app(CustomerLoyaltyService::class);
        $loyaltyService->recordTransaction($customer, 'earn', 1000.00, 'Նախնական բոնուս');

        // Redeem 300 points
        $redeemTx = $loyaltyService->recordTransaction(
            $customer,
            'redeem',
            -300.00,
            'Զեղչի կիրառում ORD-1002'
        );

        $this->assertEquals(-300.00, (float) $redeemTx->points_delta);
        $this->assertEquals(700.00, (float) $redeemTx->balance_after);

        $account = $loyaltyService->getOrCreateAccount($customer)->fresh();
        $this->assertEquals(700.00, (float) $account->points_balance);
        $this->assertEquals(300.00, (float) $account->lifetime_points_spent);
    }

    /**
     * 16. Միավորների հակադարձ գործարք (Loyalty points adjustment via API)
     */
    public function test_16_loyalty_transaction_reversal_or_adjustment(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-LOYAL3',
            'first_name' => 'Նարեկ',
            'last_name' => 'Թովմասյան',
            'phone' => '+37494332211',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $loyaltyService = app(CustomerLoyaltyService::class);
        $loyaltyService->recordTransaction($customer, 'earn', 600.00, 'Սկզբնական միավորներ');

        // Adjust via API endpoint
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/customers/{$customer->id}/loyalty/adjust", [
                'type' => 'manual_adj',
                'points_delta' => -200,
                'reason' => 'Ապրանքի վերադարձի ճշգրտում',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $account = $loyaltyService->getOrCreateAccount($customer)->fresh();
        $this->assertEquals(400.00, (float) $account->points_balance);
    }

    /**
     * 17. Կրկնակի գործարքի կանխում (Idempotent loyalty points accrual)
     */
    public function test_17_idempotent_loyalty_points_accrual(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-IDEM1',
            'first_name' => 'Լիլիթ',
            'last_name' => 'Մանուկյան',
            'phone' => '+37495556677',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-IDEM-01',
            'status' => 'delivered',
            'currency' => 'AMD',
            'subtotal' => 25000,
            'total' => 25000,
            'placed_at' => now(),
        ]);

        $loyaltyService = app(CustomerLoyaltyService::class);

        // First award call
        $tx1 = $loyaltyService->recordTransaction(
            $customer,
            'earn',
            250.00,
            'Բոնուս ORD-IDEM-01',
            $order->id
        );

        // Duplicate call with same orderId and type
        $tx2 = $loyaltyService->recordTransaction(
            $customer,
            'earn',
            250.00,
            'Բոնուս ORD-IDEM-01',
            $order->id
        );

        // Both calls return the exact same transaction ID and points are not duplicated
        $this->assertEquals($tx1->id, $tx2->id);

        $account = $loyaltyService->getOrCreateAccount($customer)->fresh();
        $this->assertEquals(250.00, (float) $account->points_balance);

        $txCount = CustomerLoyaltyTransaction::where('order_id', $order->id)->count();
        $this->assertEquals(1, $txCount);
    }

    /**
     * 18. Timeline-ի ճիշտ տվյալներ (Correct timeline data & aggregation)
     */
    public function test_18_timeline_data_and_aggregation(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-TIME1',
            'first_name' => 'Սամվել',
            'last_name' => 'Ալեքսանյան',
            'phone' => '+37491889900',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        CustomerActivity::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'type' => CustomerActivity::TYPE_NOTE_ADDED,
            'title' => 'Զանգ հաճախորդին',
            'content' => 'Քննարկվել է նոր պատվերի պայմանները',
            'created_at' => now()->subHour(),
        ]);

        CustomerActivity::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'type' => CustomerActivity::TYPE_LOYALTY,
            'title' => 'Միավորների կուտակում',
            'content' => 'Շնորհվել է 100 բոնուս',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson("/api/v1/customers/{$customer->id}/timeline");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $items = $response->json('data');
        $this->assertCount(2, $items);
        $this->assertEquals('Միավորների կուտակում', $items[0]['title']);
    }

    /**
     * 19. Permissions-ի ստուգում (Authorization via CustomerPolicy)
     */
    public function test_19_customer_policy_permissions(): void
    {
        $policy = app(CustomerPolicy::class);

        // Owner user has full permissions
        $this->assertTrue($policy->viewAny($this->user));
        $this->assertTrue($policy->create($this->user));

        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-POL1',
            'first_name' => 'Աննա',
            'phone' => '+37494001122',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $this->assertTrue($policy->view($this->user, $customer));
        $this->assertTrue($policy->update($this->user, $customer));
        $this->assertTrue($policy->delete($this->user, $customer));

        // Non-owner without permissions
        $restrictedUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Հասարակ Օգտատեր',
            'email' => 'simple.user@example.am',
            'phone' => '+37499009900',
            'password' => bcrypt('secret123'),
            'is_owner' => false,
            'is_active' => true,
        ]);

        $this->assertFalse($policy->viewAny($restrictedUser));
        $this->assertFalse($policy->create($restrictedUser));
    }

    /**
     * 20. Audit log գրառումներ (CustomerActivity audit trail)
     */
    public function test_20_customer_audit_log_records(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-AUD1',
            'first_name' => 'Արթուր',
            'last_name' => 'Աբգարյան',
            'phone' => '+37493114477',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        // Add a note via API
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/customers/{$customer->id}/notes", [
                'content' => 'Հաճախորդը խնդրեց զանգահարել 18:00-ից հետո',
                'category' => CustomerNote::CATEGORY_CALL,
                'is_pinned' => true,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'type' => CustomerActivity::TYPE_NOTE_ADDED,
        ]);
    }

    /**
     * 21. Կրկնվող հաճախորդների միավորում (Duplicate customers merge action)
     */
    public function test_21_duplicate_customers_merge_action(): void
    {
        $customerPrimary = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-MAIN',
            'first_name' => 'Վարդան',
            'last_name' => 'Ղազարյան',
            'phone' => '+37494119988',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        $customerDuplicate = Customer::create([
            'tenant_id' => $this->tenant->id,
            'type' => Customer::TYPE_INDIVIDUAL,
            'customer_code' => 'CUST-DUP',
            'first_name' => 'Վարդան',
            'last_name' => 'Ղազարյան',
            'phone' => '+37494119988',
            'status' => Customer::STATUS_ACTIVE,
        ]);

        // Duplicate has an order and points
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customerDuplicate->id,
            'order_number' => 'ORD-MERGE-01',
            'status' => 'delivered',
            'currency' => 'AMD',
            'subtotal' => 25000,
            'total' => 25000,
            'placed_at' => now(),
        ]);

        $loyaltyService = app(CustomerLoyaltyService::class);
        $loyaltyService->recordTransaction($customerDuplicate, 'earn', 200.00, 'Կուտակված բոնուս');

        // Merge duplicate into primary
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/customers/merge', [
                'target_customer_id' => $customerPrimary->id,
                'source_customer_id' => $customerDuplicate->id,
                'notes' => 'Կրկնօրինակի միավորում հիմնական քարտին',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Order is transferred to primary
        $this->assertEquals($customerPrimary->id, $order->fresh()->customer_id);

        // Duplicate is soft deleted
        $this->assertSoftDeleted('customers', ['id' => $customerDuplicate->id]);

        // Merge log is written
        $this->assertDatabaseHas('customer_merge_logs', [
            'primary_customer_id' => $customerPrimary->id,
            'merged_customer_id' => $customerDuplicate->id,
        ]);

        // Points are transferred
        $primaryAccount = $loyaltyService->getOrCreateAccount($customerPrimary)->fresh();
        $this->assertEquals(200.00, (float) $primaryAccount->points_balance);
    }

    /**
     * 22. Գոյություն ունեցող ERP մոդուլների regression testing (Existing ERP modules intact)
     */
    public function test_22_existing_erp_modules_regression(): void
    {
        // 1. Web application entry point
        $response = $this->get('/');
        $response->assertStatus(200);

        // 2. Categories API
        $categoriesResp = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/categories');
        $categoriesResp->assertStatus(200);

        // 3. Suppliers API
        $suppliersResp = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/suppliers');
        $suppliersResp->assertStatus(200);

        // 4. Products API
        $productsResp = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/products');
        $productsResp->assertStatus(200);

        // 5. Customer sources API
        $sourcesResp = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->getJson('/api/v1/customers/sources');
        $sourcesResp->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
