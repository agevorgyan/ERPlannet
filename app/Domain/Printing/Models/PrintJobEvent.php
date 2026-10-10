<?php

namespace App\Domain\Printing\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJobEvent extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'print_job_events';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'print_job_id',
        'event_type',
        'message',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function printJob(): BelongsTo
    {
        return $this->belongsTo(PrintJob::class, 'print_job_id');
    }
}
