<?php

namespace App\Domain\Sales\Models;

use App\Domain\Branch\Models\Branch;
use App\Domain\IAM\Models\User;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class XmlImport extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'xml_imports';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'warehouse_id',
        'user_id',
        'file_name',
        'file_size',
        'checksum',
        'document_type', // customer_order, supplier_invoice, catalog_feed
        'format_detected', // erplannet_xml, armenia_e_invoicing, generic_order_xml
        'status', // pending, previewed, imported, failed
        'dry_run',
        'total_records',
        'successful_records',
        'failed_records',
        'external_document_number',
        'external_document_date',
        'errors',
        'preview_payload',
        'created_orders_ids',
    ];

    protected $casts = [
        'dry_run' => 'boolean',
        'total_records' => 'integer',
        'successful_records' => 'integer',
        'failed_records' => 'integer',
        'external_document_date' => 'date',
        'errors' => 'array',
        'preview_payload' => 'array',
        'created_orders_ids' => 'array',
    ];

    protected $appends = [
        'format',
        'orders_count',
        'total_amount',
        'successful_orders',
    ];

    public function getFormatAttribute(): string
    {
        return match ($this->format_detected) {
            'erplannet_xml' => 'erp_xml',
            'armenia_e_invoicing' => 'armenian_e_invoicing',
            'generic_order_xml' => 'generic_order_xml',
            default => $this->format_detected ?? 'erp_xml',
        };
    }

    public function getOrdersCountAttribute(): int
    {
        return 1;
    }

    public function getTotalAmountAttribute(): float
    {
        $totals = $this->preview_payload['totals'] ?? [];

        return (float) ($totals['total'] ?? 0.00);
    }

    public function getSuccessfulOrdersAttribute(): int
    {
        return $this->successful_records;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function importErrors(): HasMany
    {
        return $this->hasMany(XmlImportError::class, 'xml_import_id');
    }
}
