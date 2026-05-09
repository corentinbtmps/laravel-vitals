<?php

declare(strict_types=1);

namespace LaravelVitals\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * @property int $id
 * @property string $url
 * @property string $metric  LCP|INP|CLS|TTFB|FCP
 * @property float $value
 * @property string|null $rating  good|needs-improvement|poor
 * @property string $device  mobile|desktop
 * @property string|null $navigation_type
 * @property string|null $connection
 * @property array<string, mixed>|null $attribution
 * @property string|null $user_agent
 * @property Carbon $occurred_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class RumEvent extends Model
{
    use Prunable;

    /** @var string */
    protected $table = 'vitals_rum_events';

    /** @var array<int, string> */
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value'       => 'float',
            'attribution' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function getConnectionName(): ?string
    {
        return config('vitals.database') ?? parent::getConnectionName();
    }

    /**
     * @return Builder<RumEvent>
     */
    public function prunable(): Builder
    {
        $retentionDays = (int) config('vitals.rum.retention_days', 90);

        return self::query()
            ->where('occurred_at', '<', now()->subDays($retentionDays));
    }
}
