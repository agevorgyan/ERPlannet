<?php

namespace App\Domain\Printing\Models;

use App\Domain\Branch\Models\Branch;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Printer extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'printers';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'workstation_id',
        'name',
        'printer_type', // thermal, office, virtual
        'connection_type', // browser, escpos_network, escpos_usb_bridge, system_pdf
        'interface_type',
        'protocol',
        'ip_address',
        'port',
        'paper_width', // 58mm, 80mm, a4, a5
        'character_set',
        'is_default',
        'supports_cash_drawer',
        'supports_cutter',
        'status', // online, offline, error
        'settings',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_default' => 'boolean',
        'supports_cash_drawer' => 'boolean',
        'supports_cutter' => 'boolean',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->connection_type) && ! empty($model->interface_type)) {
                $model->connection_type = match ($model->interface_type) {
                    'network' => 'escpos_network',
                    'usb' => 'escpos_usb_bridge',
                    default => $model->interface_type,
                };
            }
            if (empty($model->interface_type) && ! empty($model->connection_type)) {
                $model->interface_type = match ($model->connection_type) {
                    'escpos_network' => 'network',
                    'escpos_usb_bridge' => 'usb',
                    default => $model->connection_type,
                };
            }
            if (empty($model->printer_type) && ! empty($model->protocol)) {
                $model->printer_type = $model->protocol === 'esc_pos' ? 'thermal' : 'office';
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function workstation(): BelongsTo
    {
        return $this->belongsTo(PosWorkstation::class, 'workstation_id');
    }

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class, 'printer_id');
    }

    public function isThermal(): bool
    {
        return $this->printer_type === 'thermal';
    }

    public function isNetwork(): bool
    {
        return $this->connection_type === 'escpos_network';
    }
}
