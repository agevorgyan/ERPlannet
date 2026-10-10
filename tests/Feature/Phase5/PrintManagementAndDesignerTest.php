<?php

declare(strict_types=1);

namespace Tests\Feature\Phase5;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Product;
use App\Domain\IAM\Models\User;
use App\Domain\Printing\Models\DocumentTemplate;
use App\Domain\Printing\Models\Printer;
use App\Domain\Printing\Models\PrintJob;
use App\Domain\Printing\Services\DocumentTemplateRenderer;
use App\Domain\Printing\Services\EscPosConverter;
use App\Domain\Printing\Services\PrintJobManager;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintManagementAndDesignerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Branch $branch;

    protected Warehouse $warehouse;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $this->branch = Branch::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->firstOrFail();

        $this->order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'order_number' => 'ORD-PRINT-001',
            'source' => 'pos',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'currency' => 'AMD',
            'subtotal' => 5000,
            'total' => 5000,
            'paid_amount' => 5000,
            'balance_due' => 0,
            'placed_at' => now(),
        ]);

        $product = Product::where('tenant_id', $this->tenant->id)->firstOrFail();

        $this->order->items()->create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'product_name' => 'Թարմ Հաց Բագետ',
            'product_sku' => 'BAG-001',
            'quantity' => 2,
            'unit_price' => 2500,
            'subtotal' => 5000,
            'total' => 5000,
        ]);
    }

    public function test_can_register_printer_with_interface_and_paper_width(): void
    {
        $payload = [
            'name' => 'Thermal Cashier 1',
            'branch_id' => $this->branch->id,
            'interface_type' => 'network',
            'ip_address' => '192.168.1.180',
            'port' => 9100,
            'paper_width' => '80mm',
            'protocol' => 'esc_pos',
            'supports_cut' => true,
            'supports_drawer' => true,
            'is_default' => true,
        ];

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/printing/printers', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('printers', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Thermal Cashier 1',
            'paper_width' => '80mm',
            'is_default' => true,
        ]);
    }

    public function test_can_trigger_test_print_for_printer(): void
    {
        $printer = Printer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Kitchen Thermal 58mm',
            'interface_type' => 'browser',
            'paper_width' => '58mm',
            'protocol' => 'esc_pos',
            'status' => 'online',
        ]);

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson("/api/v1/printing/printers/{$printer->id}/test-print");

        $response->assertStatus(200);
        $this->assertDatabaseHas('print_jobs', [
            'tenant_id' => $this->tenant->id,
            'printer_id' => $printer->id,
            'document_type' => 'test_page',
            'status' => 'completed',
        ]);
    }

    public function test_document_template_renders_armenian_unicode_text_and_placeholders(): void
    {
        $renderer = app(DocumentTemplateRenderer::class);

        $template = DocumentTemplate::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Standard Receipt Template',
            'code' => 'TEST-RECEIPT-TPL',
            'document_type' => 'order_receipt',
            'paper_width' => '80mm',
            'content' => '<h3>{{organization.name}}</h3><p>Պատվեր: {{order.number}}</p><p>Ընդամենը՝ {{order.grand_total}}</p>',
            'config' => [
                'font_family' => 'sans-serif',
                'font_size' => '12px',
                'show_tax_summary' => true,
            ],
            'is_active' => true,
        ]);

        $html = $renderer->render($template, $this->order, [
            'is_reprint' => false,
        ]);

        // Verify Armenian Unicode preserved and placeholders replaced
        $this->assertStringContainsString('Պատվեր: ORD-PRINT-001', $html);
        $this->assertStringContainsString('Ընդամենը՝', $html);
        $this->assertStringContainsString('5,000.00 ֏', $html);
    }

    public function test_esc_pos_converter_generates_valid_byte_stream_with_cut_and_drawer_kick(): void
    {
        $converter = app(EscPosConverter::class);

        $text = "ERPlannet\nՊատվեր #ORD-001\n";
        $bytes = $converter->textToEscPos($text, [
            'cut_paper' => true,
            'kick_drawer' => true,
            'paper_width' => '80mm',
        ]);

        $this->assertNotEmpty($bytes);
        // ESC @ is init: "\x1B\x40"
        $this->assertStringContainsString("\x1B\x40", $bytes);
        // GS V 0 is cut paper: "\x1D\x56\x00"
        $this->assertStringContainsString("\x1D\x56\x00", $bytes);
        // ESC p 0 is drawer kick: "\x1B\x70\x00"
        $this->assertStringContainsString("\x1B\x70\x00", $bytes);
    }

    public function test_can_publish_new_template_version(): void
    {
        $template = DocumentTemplate::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Custom A4 Delivery Template',
            'code' => 'TPL-A4-CUSTOM',
            'document_type' => 'b2b_delivery_note',
            'paper_width' => 'A4',
            'content' => '<h1>Version 1</h1>',
            'active_version' => 1,
            'is_active' => true,
        ]);

        // Update draft content
        $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->putJson("/api/v1/printing/templates/{$template->id}", [
                'content' => '<h1>Version 2 - Updated</h1>',
                'config' => ['margin' => '10mm'],
            ]);

        // Publish version 2
        $pubResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson("/api/v1/printing/templates/{$template->id}/publish", [
                'notes' => 'Updated headers and table borders',
            ]);

        $pubResponse->assertStatus(200);
        $template->refresh();

        $this->assertEquals(2, $template->active_version);
        $this->assertDatabaseHas('document_template_versions', [
            'document_template_id' => $template->id,
            'version' => 2,
        ]);
    }

    public function test_can_queue_print_job_with_idempotency_key_and_track_reprints(): void
    {
        $printer = Printer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Main POS Printer',
            'interface_type' => 'browser',
            'paper_width' => '80mm',
            'protocol' => 'esc_pos',
            'is_default' => true,
            'status' => 'online',
        ]);

        $manager = app(PrintJobManager::class);

        // 1. Initial print job
        $job = $manager->dispatchOrderPrintJob(
            $this->order,
            'order_receipt',
            $this->user,
            printer: $printer,
            idempotencyKey: 'IDEMP-JOB-001'
        );

        $this->assertNotNull($job);
        $this->assertFalse($job->is_reprint);
        $this->assertEquals(0, $job->reprint_count);

        // 2. Duplicate dispatch with same idempotency key returns existing job without duplication
        $dupJob = $manager->dispatchOrderPrintJob(
            $this->order,
            'order_receipt',
            $this->user,
            printer: $printer,
            idempotencyKey: 'IDEMP-JOB-001'
        );
        $this->assertEquals($job->id, $dupJob->id);
        $this->assertEquals(1, PrintJob::where('idempotency_key', 'IDEMP-JOB-001')->count());

        // 3. Reprint job
        $reprintJob = $manager->reprintJob($job, $this->user);
        $this->assertNotNull($reprintJob);
        $this->assertTrue($reprintJob->is_reprint);
        $this->assertEquals(1, $reprintJob->reprint_count);
    }

    public function test_failed_printer_job_does_not_abort_order(): void
    {
        // An offline network printer
        $printer = Printer::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Dead Network Printer',
            'interface_type' => 'network',
            'ip_address' => '10.255.255.1', // Unreachable IP
            'port' => 9100,
            'paper_width' => '80mm',
            'protocol' => 'esc_pos',
            'status' => 'online',
        ]);

        $manager = app(PrintJobManager::class);

        // Dispatching print job handles hardware error gracefully
        $job = $manager->dispatchOrderPrintJob($this->order, 'order_receipt', $this->user, printer: $printer);

        $this->assertNotNull($job);
        // Order remains completely intact and unaffected
        $this->order->refresh();
        $this->assertEquals('confirmed', $this->order->status);
        $this->assertEquals(5000.0, (float) $this->order->total);
    }
}
