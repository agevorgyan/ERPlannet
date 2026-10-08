<?php

declare(strict_types=1);

namespace Tests\Feature\Phase5;

use App\Domain\IAM\Models\User;
use App\Domain\Integration\Models\TenantIntegration;
use App\Domain\Integration\Models\TenantNotificationTemplate;
use App\Domain\Integration\Services\NotificationDispatcherService;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationAndAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);
    }

    public function test_can_save_template_and_dispatch_templated_telegram_notification(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 991122],
            ], 200),
        ]);

        // 1. Configure Telegram integration
        $integration = new TenantIntegration([
            'tenant_id' => $this->tenant->id,
            'provider' => 'telegram',
            'name' => 'Dispatch Alerts Bot',
            'status' => 'active',
        ]);
        $integration->setCredentials([
            'bot_token' => '123456789:ABCdefGHIjklMNOpqrSTUvwxYZ',
            'default_chat_id' => '-100123456789',
        ]);
        $integration->save();

        // 2. Save notification template
        $template = TenantNotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'channel' => 'telegram',
            'event' => 'order_dispatched',
            'template_body' => '🚀 <b>Order #{order_number}</b> for <b>{customer_name}</b> is on its way!',
            'is_active' => true,
        ]);

        $rendered = $template->render([
            'order_number' => 'ORD-5544',
            'customer_name' => 'Aram Petrosyan',
        ]);

        $this->assertEquals('🚀 <b>Order #ORD-5544</b> for <b>Aram Petrosyan</b> is on its way!', $rendered);

        // 3. Dispatch via service
        $dispatcher = app(NotificationDispatcherService::class);
        $result = $dispatcher->send(
            tenantId: $this->tenant->id,
            event: 'order_dispatched',
            recipient: '-100123456789',
            variables: ['order_number' => 'ORD-5544', 'customer_name' => 'Aram Petrosyan'],
            channel: 'telegram'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('telegram', $result['channel']);
    }

    public function test_can_dispatch_sms_via_nikita_driver(): void
    {
        Http::fake([
            'https://api.nikita.am/sms/send' => Http::response(['status' => 'OK'], 200),
        ]);

        $integration = new TenantIntegration([
            'tenant_id' => $this->tenant->id,
            'provider' => 'nikita_sms',
            'name' => 'Nikita Mobile Gateway',
            'status' => 'active',
        ]);
        $integration->setCredentials([
            'login' => 'my_login',
            'password' => 'my_pass',
            'sender_id' => 'ERPlannet',
        ]);
        $integration->save();

        TenantNotificationTemplate::create([
            'tenant_id' => $this->tenant->id,
            'channel' => 'sms',
            'event' => 'pos_receipt',
            'template_body' => 'Thank you for shopping at ERPlannet. Receipt #{receipt_no}',
            'is_active' => true,
        ]);

        $dispatcher = app(NotificationDispatcherService::class);
        $result = $dispatcher->send(
            tenantId: $this->tenant->id,
            event: 'pos_receipt',
            recipient: '+37494112233',
            variables: ['receipt_no' => 'REC-12345'],
            channel: 'sms'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('sms', $result['channel']);
    }

    public function test_can_export_accounting_invoices_and_inventory_xml(): void
    {
        // 1. Invoices Export
        $invRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->get('/api/v1/accounting-export/1c/invoices');

        $invRes->assertStatus(200);
        $this->assertEquals('application/xml; charset=utf-8', $invRes->headers->get('Content-Type'));
        $this->assertStringContainsString('<ASDocuments type="Invoices" system="ERPlannet">', $invRes->getContent());

        // 2. Inventory Export
        $invRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->get('/api/v1/accounting-export/as/data');

        $invRes->assertStatus(200);
        $this->assertEquals('application/xml; charset=utf-8', $invRes->headers->get('Content-Type'));
        $this->assertStringContainsString('<ASDocuments type="StockBalances" system="ERPlannet">', $invRes->getContent());
    }
}
