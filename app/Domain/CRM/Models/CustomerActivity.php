<?php

namespace App\Domain\CRM\Models;

use App\Domain\IAM\Models\User;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerActivity extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const TYPE_ORDER_PLACED = 'order_placed';

    public const TYPE_ORDER_DELIVERED = 'order_delivered';

    public const TYPE_ORDER_CANCELLED = 'order_cancelled';

    public const TYPE_CALL = 'call';

    public const TYPE_SMS = 'sms';

    public const TYPE_WHATSAPP = 'whatsapp';

    public const TYPE_VIBER = 'viber';

    public const TYPE_EMAIL = 'email';

    public const TYPE_NOTE = 'note';

    public const TYPE_NOTE_ADDED = 'note';

    public const TYPE_LOYALTY = 'loyalty';

    public const TYPE_ADDRESS_ADDED = 'address_added';

    public const TYPE_STATUS_CHANGED = 'status_changed';

    public const TYPE_COMPLAINT = 'complaint';

    public const TYPE_TASK = 'task';

    protected $table = 'customer_activities';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'type',
        'title',
        'content',
        'reference_type',
        'reference_id',
        'metadata',
        'user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
