<?php

namespace App\Domain\Printing\Models;

use App\Domain\Branch\Models\Branch;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class DocumentTemplate extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'document_templates';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'slug',
        'code',
        'document_type', // pos_receipt, order_receipt, kitchen_ticket, delivery_document, b2b_delivery_note, order_confirmation, payment_receipt, refund_receipt, a4_document
        'paper_size', // 58mm, 80mm, a4, a5
        'paper_width',
        'content',
        'config',
        'active_version',
        'is_active',
        'is_default',
        'layout_config',
        'sample_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'active_version' => 'integer',
        'config' => 'array',
        'layout_config' => 'array',
        'sample_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $base = $model->code ?? $model->name ?? 'template';
                $model->slug = Str::slug($base).'-'.Str::random(6);
            }
            if (empty($model->paper_width) && ! empty($model->paper_size)) {
                $model->paper_width = $model->paper_size;
            }
            if (empty($model->paper_size) && ! empty($model->paper_width)) {
                $model->paper_size = strtolower($model->paper_width);
            }
            if (empty($model->layout_config)) {
                $model->layout_config = ['blocks' => []];
            }
            if (empty($model->active_version)) {
                $model->active_version = 1;
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentTemplateVersion::class, 'document_template_id')->orderByDesc('version_number');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(DocumentTemplateVersion::class, 'document_template_id')->orderByDesc('version_number');
    }

    /**
     * Publish a new version snapshot of this template.
     */
    public function publishVersion(?string $userId = null, ?string $changelog = null): DocumentTemplateVersion
    {
        $currentMax = max(
            (int) ($this->active_version ?? 0),
            (int) ($this->versions()->max('version_number') ?? 0),
            (int) ($this->versions()->max('version') ?? 0)
        );
        $nextNumber = $currentMax + 1;
        $this->update(['active_version' => $nextNumber]);

        return DocumentTemplateVersion::create([
            'tenant_id' => $this->tenant_id,
            'document_template_id' => $this->id,
            'version_number' => $nextNumber,
            'version' => $nextNumber,
            'content' => $this->content,
            'config' => $this->config,
            'layout_config' => $this->layout_config ?? ['blocks' => []],
            'published_by' => $userId,
            'changelog' => $changelog ?? "Version {$nextNumber} published",
            'is_published' => true,
        ]);
    }
}
