<?php

declare(strict_types=1);

namespace App\Domain\Integration\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantNotificationTemplate extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'tenant_notification_templates';

    protected $fillable = [
        'tenant_id',
        'channel',
        'event',
        'template_body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Render the template with the provided dynamic variables.
     *
     * @param array<string, string|int|float> $variables
     */
    public function render(array $variables): string
    {
        $rendered = $this->template_body;
        foreach ($variables as $key => $val) {
            $rendered = str_replace('{' . $key . '}', (string) $val, $rendered);
        }
        return $rendered;
    }
}
