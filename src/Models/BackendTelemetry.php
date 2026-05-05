<?php

declare(strict_types=1);

namespace LaravelVitals\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $audit_id
 * @property bool $sampled_request
 * @property string|null $route_name
 * @property int $http_status
 * @property float $duration_ms
 * @property int $memory_peak_kb
 * @property int $queries_count
 * @property float $queries_time_ms
 * @property int $queries_unique
 * @property bool $n_plus_one_suspect
 * @property int $views_rendered
 * @property float $views_time_ms
 * @property int $jobs_dispatched
 * @property int $events_fired
 * @property int $cache_hits
 * @property int $cache_misses
 * @property array<int, array<string, mixed>>|null $slow_queries
 * @property bool $truncated
 */
final class BackendTelemetry extends Model
{
    use Prunable;

    /** @var string */
    protected $table = 'vitals_backend_telemetry';

    /** @var array<int, string> */
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sampled_request'    => 'boolean',
            'duration_ms'        => 'float',
            'queries_time_ms'    => 'float',
            'views_time_ms'      => 'float',
            'n_plus_one_suspect' => 'boolean',
            'truncated'          => 'boolean',
            'slow_queries'       => 'array',
        ];
    }

    public function getConnectionName(): ?string
    {
        return config('vitals.database') ?? parent::getConnectionName();
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function prunable(): Builder
    {
        return static::query()
            ->where('created_at', '<', now()->subDays((int) config('vitals.retention.days')));
    }
}
