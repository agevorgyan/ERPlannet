<?php

namespace App\Domain\CRM\Models;

use App\Domain\IAM\Models\User;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNote extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const CATEGORY_GENERAL = 'general';

    public const CATEGORY_PREFERENCE = 'preference';

    public const CATEGORY_COMPLAINT = 'complaint';

    public const CATEGORY_FINANCIAL = 'financial';

    public const CATEGORY_CALL = 'call';

    public const CATEGORY_TASK = 'task';

    protected $table = 'customer_notes';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'author_user_id',
        'category',
        'content',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
