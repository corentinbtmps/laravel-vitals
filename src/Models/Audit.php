<?php

declare(strict_types=1);

namespace LaravelVitals\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property int $url_id
 * @property string|null $batch_id
 * @property string $driver
 * @property string $device
 * @property string $status
 * @property int|null $score_performance
 * @property int|null $score_accessibility
 * @property int|null $score_best_practices
 * @property int|null $score_seo
 * @property int|null $lcp_ms
 * @property float|null $cls
 * @property int|null $inp_ms
 * @property int|null $ttfb_ms
 * @property int|null $fcp_ms
 * @property int|null $si_ms
 * @property int|null $tbt_ms
 * @property string|null $report_path
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 */
final class Audit extends Model
{
    use HasUuids;
    use Prunable;

    /** @var string */
    protected $table = 'vitals_audits';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<int, string> */
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score_performance'    => 'integer',
            'score_accessibility'  => 'integer',
            'score_best_practices' => 'integer',
            'score_seo'            => 'integer',
            'started_at'           => 'datetime',
            'completed_at'         => 'datetime',
            'is_demo'              => 'boolean',
        ];
    }

    public function getConnectionName(): ?string
    {
        return config('vitals.database') ?? parent::getConnectionName();
    }

    /**
     * @return BelongsTo<Url, Audit>
     */
    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class, 'url_id');
    }

    /**
     * @return HasMany<Recommendation, Audit>
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'audit_id');
    }

    /**
     * @return HasOne<BackendTelemetry, Audit>
     */
    public function telemetry(): HasOne
    {
        return $this->hasOne(BackendTelemetry::class, 'audit_id');
    }

    /**
     * Audits older than the configured retention window.
     *
     * @return Builder<Audit>
     */
    public function prunable(): Builder
    {
        return self::query()
            ->where('created_at', '<', now()->subDays((int) config('vitals.retention.days')));
    }
}
