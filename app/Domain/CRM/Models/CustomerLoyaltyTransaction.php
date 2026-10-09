<?php

namespace App\Domain\CRM\Models;

use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLoyaltyTransaction extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const TYPE_EARN = 'earn';

    public const TYPE_REDEEM = 'redeem';

    public const TYPE_REFUND = 'refund';

    public const TYPE_MANUAL_ADJ = 'manual_adj';

    public const TYPE_EXPIRE = 'expire';

    public const TYPE_BIRTHDAY_GIFT = 'birthday_gift';

    protected $table = 'customer_loyalty_transactions';

    protected $fillable = [
        'tenant_id',
        'loyalty_account_id',
        'customer_id',
        'order_id',
        'type',
        'points_delta',
        'balance_after',
        'currency_value',
        'reason',
        'performed_by_user_id',
    ];

    protected $casts = [
        'points_delta' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'currency_value' => 'decimal:2',
    ];

    public function loyaltyAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerLoyaltyAccount::class, 'loyalty_account_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
