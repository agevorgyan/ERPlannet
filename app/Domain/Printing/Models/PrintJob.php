<?php

namespace App\Domain\Printing\Models;

use App\Domain\Branch\Models\Branch;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintJob extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'print_jobs';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'printer_id',
        'workstation_id',
        'order_id',
        'document_type',
        'document_template_id',
        'template_version_id',
        'copies',
        'requested_by',
        'status', // queued, processing, completed, failed, cancelled
        'idempotency_key',
        'is_reprint',
        'reprint_count',
        'error_message',
        'payload_rendered',
        'processed_at',
    ];

    protected $casts = [
        'copies' => 'integer',
        'is_reprint' => 'boolean',
        'reprint_count' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class, 'printer_id');
    }

    public function workstation(): BelongsTo
    {
        return $this->belongsTo(PosWorkstation::class, 'workstation_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplateVersion::class, 'template_version_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(PrintJobEvent::class, 'print_job_id')->orderBy('created_at');
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'error_message' => null,
        ]);

        $this->recordEvent('printed', 'Print job completed successfully.');
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'processed_at' => now(),
            'error_message' => $error,
        ]);

        $this->recordEvent('failed', "Print job failed: {$error}");
    }

    public function recordEvent(string $eventType, ?string $message = null, ?array $metadata = null): PrintJobEvent
    {
        return PrintJobEvent::create([
            'tenant_id' => $this->tenant_id,
            'print_job_id' => $this->id,
            'event_type' => $eventType,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }
}
