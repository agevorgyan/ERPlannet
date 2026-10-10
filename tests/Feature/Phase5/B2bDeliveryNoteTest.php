<?php

declare(strict_types=1);

namespace Tests\Feature\Phase5;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Product;
use App\Domain\CRM\Models\Customer;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\B2bDeliveryNote;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Services\B2bDeliveryNoteService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class B2bDeliveryNoteTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Branch $branch;

    protected Warehouse $warehouse;

    protected Customer $customer;

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
        $this->customer = Customer::where('tenant_id', $this->tenant->id)->firstOrFail();

        $this->order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-B2B-100',
            'source' => 'manual_backoffice',
            'order_type' => 'b2b_wholesale',
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'currency' => 'AMD',
            'subtotal' => 20000,
            'total' => 20000,
            'paid_amount' => 0,
            'balance_due' => 20000,
            'placed_at' => now(),
        ]);

        $product = Product::where('tenant_id', $this->tenant->id)->firstOrFail();

        $this->order->items()->create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'product_name' => 'Կրուասան Շոկոլադով',
            'product_sku' => 'CROIS-01',
            'quantity' => 20,
            'unit_name' => 'հատ',
            'unit_price' => 1000,
            'subtotal' => 20000,
            'total' => 20000,
            'total_price' => 20000,
        ]);
    }

    public function test_can_generate_b2b_delivery_note_from_order_with_sequential_numbering(): void
    {
        $payload = [
            'recipient_legal_name' => '«Մեծածախ Տուն» ՍՊԸ',
            'recipient_tax_id' => '02554411',
            'delivery_address' => 'Երևան, Տիգրան Մեծ 12',
            'delivered_by' => 'Կարեն Սարգսյան',
            'received_by' => 'Արթուր Գրիգորյան',
            'notes' => 'Պայմանագիր #B2B-2026/05',
        ];

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson("/api/v1/sales/orders/{$this->order->id}/delivery-notes", $payload);

        $response->assertStatus(201);
        $doc = B2bDeliveryNote::find($response->json('data.id'));

        $this->assertNotNull($doc);
        // Sequential format DN-YYYY-NNNNNN
        $this->assertMatchesRegularExpression('/^DN-\d{4}-\d{6}$/', $doc->document_number);
        $this->assertEquals('«Մեծածախ Տուն» ՍՊԸ', $doc->recipient_legal_name);
        $this->assertEquals('02554411', $doc->recipient_tax_id);
        $this->assertEquals(20000.0, (float) $doc->total_amount);
        $this->assertEquals('issued', $doc->status);
    }

    public function test_b2b_delivery_note_snapshots_line_items_and_parties(): void
    {
        $service = app(B2bDeliveryNoteService::class);

        $doc = $service->generateFromOrder($this->order, [
            'recipient_legal_name' => '«Գուրման Կաֆե» ՍՊԸ',
            'recipient_tax_id' => '01889922',
        ], $this->user);

        $this->assertNotEmpty($doc->items);
        $this->assertCount(1, $doc->items);
        $this->assertEquals('Կրուասան Շոկոլադով', $doc->items[0]['product_name']);
        $this->assertEquals(20, $doc->items[0]['quantity']);
        $this->assertEquals(1000, $doc->items[0]['unit_price']);

        // Snapshot of supplier
        $this->assertNotEmpty($doc->supplier_snapshot);
        $this->assertEquals($this->tenant->name, $doc->supplier_snapshot['name']);
    }

    public function test_reprinting_b2b_delivery_note_increments_reprint_count_and_preserves_document_number(): void
    {
        $service = app(B2bDeliveryNoteService::class);

        $doc = $service->generateFromOrder($this->order, [
            'recipient_legal_name' => '«Ֆուդ Թրեյդ» ՍՊԸ',
        ], $this->user);

        $originalDocNumber = $doc->document_number;
        $this->assertEquals(0, $doc->reprint_count);

        // First reprint
        $reprinted1 = $service->recordReprint($doc, $this->user, 'Customer lost original');
        $this->assertEquals($originalDocNumber, $reprinted1->document_number);
        $this->assertEquals(1, $reprinted1->reprint_count);

        // Second reprint via API
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson("/api/v1/sales/delivery-notes/{$doc->id}/reprint", [
                'reason' => 'Accounting copy requested',
            ]);

        $response->assertStatus(200);
        $doc->refresh();
        $this->assertEquals(2, $doc->reprint_count);
        $this->assertEquals($originalDocNumber, $doc->document_number);
    }

    public function test_can_cancel_delivery_note(): void
    {
        $service = app(B2bDeliveryNoteService::class);

        $doc = $service->generateFromOrder($this->order, [
            'recipient_legal_name' => '«Էլիտ Սերվիս» ՍՊԸ',
        ], $this->user);

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson("/api/v1/sales/delivery-notes/{$doc->id}/cancel", [
                'reason' => 'Incorrect recipient TIN',
            ]);

        $response->assertStatus(200);
        $doc->refresh();
        $this->assertEquals('cancelled', $doc->status);
        $this->assertEquals('Incorrect recipient TIN', $doc->cancellation_reason);
    }

    public function test_delivery_note_rendering_includes_watermark_when_reprinted(): void
    {
        $service = app(B2bDeliveryNoteService::class);

        $doc = $service->generateFromOrder($this->order, [
            'recipient_legal_name' => '«Գլոբալ Դիստրիբյուշն» ՍՊԸ',
        ], $this->user);

        // Render original
        $originalHtml = $service->renderDocumentHtml($doc);
        $this->assertStringNotContainsString('ԿՐԿՆՕՐԻՆԱԿ', $originalHtml);

        // Record reprint
        $service->recordReprint($doc, $this->user);

        // Render reprinted document
        $reprintHtml = $service->renderDocumentHtml($doc);
        $this->assertStringContainsString('ԿՐԿՆՕՐԻՆԱԿ', $reprintHtml);
        $this->assertStringContainsString('REPRINT #1', $reprintHtml);
    }
}
