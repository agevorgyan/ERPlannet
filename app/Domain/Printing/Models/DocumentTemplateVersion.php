<?php

namespace App\Domain\Printing\Models;

use App\Domain\IAM\Models\User;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplateVersion extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'document_template_versions';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'document_template_id',
        'version_number',
        'version',
        'content',
        'config',
        'layout_config',
        'published_by',
        'changelog',
        'is_published',
        'created_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'version' => 'integer',
        'config' => 'array',
        'layout_config' => 'array',
        'is_published' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
