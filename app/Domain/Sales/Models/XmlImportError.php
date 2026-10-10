<?php

namespace App\Domain\Sales\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XmlImportError extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'xml_import_errors';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'xml_import_id',
        'line_number',
        'record_identifier',
        'error_code',
        'error_message',
        'raw_snippet',
        'created_at',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'created_at' => 'datetime',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(XmlImport::class, 'xml_import_id');
    }
}
