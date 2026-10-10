<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\B2bDeliveryNote;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class B2bDeliveryNoteService
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    /**
     * Generate a legally compliant B2B Delivery Note / Накладная from an existing order.
     *
     * @param array{
     *     document_date?: string|null,
     *     delivered_by_name?: string|null,
     *     received_by_name?: string|null,
     *     customer_tax_id?: string|null,
     *     customer_name?: string|null,
     *     notes?: string|null
     * } $overrides
     */
    public function generateFromOrder(Order $order, array $overrides = [], mixed $userId = null): B2bDeliveryNote
    {
        $tenant = $this->tenantContext->getTenant() ?? $order->tenant;
        if (! $tenant) {
            throw new InvalidArgumentException('Tenant context not set.');
        }

        $userUuid = is_object($userId) ? $userId->id : (is_string($userId) && ! empty($userId) ? $userId : null);
        $branch = $order->branch;
        $customer = $order->customer;

        // Resolve legal details
        $customerName = $overrides['customer_name'] ?? ($overrides['recipient_legal_name'] ?? ($order->customer_snapshot['company_name'] ?? ($customer?->company?->company_name ?? ($customer?->first_name ? "{$customer->first_name} {$customer->last_name}" : 'B2B Customer'))));
        $customerTaxId = $overrides['customer_tax_id'] ?? ($overrides['recipient_tax_id'] ?? ($order->customer_snapshot['tax_id'] ?? ($customer?->tax_id ?? $customer?->company?->tax_id)));

        $customerAddress = $overrides['customer_address'] ?? ($overrides['delivery_address'] ?? ($order->customer_snapshot['address']['formatted'] ?? ($order->address?->formatted_address ?? 'ՀՀ, ք. Երևան')));

        $docDate = ! empty($overrides['document_date']) ? Carbon::parse($overrides['document_date'])->toDateString() : now()->toDateString();

        // Build snapshot of line items
        $itemsSnapshot = [];
        foreach ($order->items as $item) {
            $lineTotal = (float) ($item->total ?? $item->total_price ?? $item->subtotal ?? ($item->quantity * $item->unit_price));
            $itemsSnapshot[] = [
                'sku' => $item->product_sku,
                'name' => $item->product_name,
                'product_code' => $item->product_sku,
                'product_name' => $item->product_name,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit_name ?? 'հատ',
                'unit_name' => $item->unit_name ?? 'հատ',
                'unit_price' => (float) $item->unit_price,
                'discount' => (float) ($item->discount ?? 0),
                'tax' => (float) ($item->tax_amount ?? 0),
                'tax_amount' => (float) ($item->tax_amount ?? 0),
                'total' => $lineTotal,
                'total_amount' => $lineTotal,
            ];
        }

        return DB::transaction(function () use ($tenant, $branch, $order, $docDate, $customerName, $customerTaxId, $customerAddress, $itemsSnapshot, $overrides, $userUuid) {
            // Generate next unique document number
            $documentNumber = $this->generateNextDocumentNumber($tenant->id);

            return B2bDeliveryNote::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'order_id' => $order->id,
                'document_number' => $documentNumber,
                'document_date' => $docDate,
                'supplier_name' => $tenant->name,
                'supplier_tax_id' => $tenant->tax_id ?? '02891234',
                'supplier_address' => $branch->address ?? 'ՀՀ, ք. Երևան',
                'customer_name' => $customerName,
                'customer_tax_id' => $customerTaxId,
                'customer_address' => $customerAddress,
                'total_amount' => $order->total,
                'tax_amount' => $order->tax ?? $order->tax_amount ?? 0,
                'items_snapshot' => $itemsSnapshot,
                'delivered_by_name' => $overrides['delivered_by_name'] ?? ($overrides['delivered_by'] ?? ($order->responsibleEmployee?->name ?? 'Հանձնող')),
                'received_by_name' => $overrides['received_by_name'] ?? ($overrides['received_by'] ?? 'Ստացող Լիազորված Անձ'),
                'notes' => $overrides['notes'] ?? $order->customer_notes,
                'reprint_count' => 0,
                'status' => 'issued',
                'created_by' => $userUuid,
            ]);
        });
    }

    /**
     * Mark delivery note reprinted with audit counter.
     */
    public function recordReprint(B2bDeliveryNote $note, mixed $user = null, ?string $reason = null): B2bDeliveryNote
    {
        $note->increment('reprint_count');

        return $note->fresh();
    }

    public function reprint(B2bDeliveryNote $note, ?string $userId = null): B2bDeliveryNote
    {
        return $this->recordReprint($note, $userId);
    }

    /**
     * Cancel an issued delivery note with formal reason without overwriting original record.
     */
    public function cancel(B2bDeliveryNote $note, string $reason, ?string $userId = null): B2bDeliveryNote
    {
        $note->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'notes' => ($note->notes ? $note->notes."\n" : '')."CANCELLED: {$reason}",
        ]);

        return $note->fresh();
    }

    /**
     * Render document HTML with optional reprint watermark.
     */
    public function renderDocumentHtml(B2bDeliveryNote $note): string
    {
        $watermark = '';
        if ($note->reprint_count > 0) {
            $watermark = "<div class='watermark'>ԿՐԿՆՕՐԻՆԱԿ / REPRINT #{$note->reprint_count}</div>";
        }

        $html = "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Delivery Note {$note->document_number}</title></head><body>";
        $html .= $watermark;
        $html .= "<h1>Բեռնագիր / B2B Delivery Note #{$note->document_number}</h1>";
        $html .= "<p>Մատակարար: {$note->supplier_name} (ՀՎՀՀ: {$note->supplier_tax_id})</p>";
        $html .= "<p>Գնորդ: {$note->customer_name} (ՀՎՀՀ: {$note->customer_tax_id})</p>";
        $html .= '<p>Ընդամենը՝ '.number_format((float) $note->total_amount, 2).' ֏</p>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * Generate sequential document number for tenant with concurrency locking.
     */
    protected function generateNextDocumentNumber(string $tenantId): string
    {
        $year = date('Y');
        $prefix = "DN-{$year}-";

        $latest = B2bDeliveryNote::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('document_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('document_number')
            ->first();

        $seq = 1;
        if ($latest && preg_match('/DN-\d{4}-(\d+)/', $latest->document_number, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%06d', $prefix, $seq);
    }
}
