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
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Device;
use LaravelVitals\Support\Health;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

/**
 * @property string $id
 * @property int $url_id
 * @property string|null $batch_id
 * @property string $driver
 * @property \LaravelVitals\Enums\Device $device
 * @property \LaravelVitals\Enums\AuditStatus $status
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
 * @property array<string, mixed>|null $details
 * @property string|null $error
 * @property string|null $slack_message_ts
 * @property int $api_call_count
 * @property float $api_call_cost
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 */
final class Audit extends Model implements Searchable
{
    use HasUuids;
    use Prunable;

    public string $searchableType = 'audits';

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
            'status'               => AuditStatus::class,
            'device'               => Device::class,
            'score_performance'    => 'integer',
            'score_accessibility'  => 'integer',
            'score_best_practices' => 'integer',
            'score_seo'            => 'integer',
            'details'              => 'array',
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
     * Letter grade computed from the average of all 4 axis scores.
     * Returns null when no scores are available.
     */
    public function getGlobalGradeAttribute(): ?string
    {
        $scores = array_filter([
            $this->score_performance,
            $this->score_accessibility,
            $this->score_best_practices,
            $this->score_seo,
        ], fn ($v) => $v !== null);

        if ($scores === []) {
            return null;
        }

        return Health::grade((int) round(array_sum($scores) / count($scores)));
    }

    /**
     * Letter grade for the performance axis alone.
     * Returns null when score_performance is not set.
     */
    public function getPerformanceGradeAttribute(): ?string
    {
        return $this->score_performance !== null
            ? Health::grade($this->score_performance)
            : null;
    }

    /**
     * Combined SEO score blending Lighthouse SEO (50%) with custom check pass rate (50%).
     *
     * Formula:
     *   vitals_seo_score = round( (lighthouse_seo_score * 0.5) + (custom_seo_pass_rate * 50) )
     *
     * Where custom_seo_pass_rate is the weighted ratio of passing checks (0–1).
     * Returns null when no Lighthouse SEO score is available.
     */
    public function getVitalsSeoScoreAttribute(): ?int
    {
        $lighthouseScore = $this->score_seo;

        if ($lighthouseScore === null) {
            return null;
        }

        // Count SEO recommendations (non-passing) for this audit, weighted
        $seoRecos = $this->recommendations()
            ->where('source', 'seo')
            ->get();

        if ($seoRecos->isEmpty()) {
            // No custom checks ran — use 100% pass rate
            return (int) round($lighthouseScore * 0.5 + 50);
        }

        // Build weighted pass rate from the registry
        $registry = app(\LaravelVitals\Seo\SeoCheckRegistry::class);
        $enabledChecks = $registry->enabled();

        $totalWeight = 0;
        $failedWeight = 0;

        foreach ($enabledChecks as $check) {
            $totalWeight += $check->weight();
        }

        // Sum weights of failed checks
        $failedKeys = $seoRecos->pluck('audit_key')
            ->map(fn ($k) => str_replace('seo-', '', $k))
            ->all();

        foreach ($enabledChecks as $check) {
            if (in_array($check->key(), $failedKeys, true)) {
                $failedWeight += $check->weight();
            }
        }

        $passRate = $totalWeight > 0
            ? ($totalWeight - $failedWeight) / $totalWeight
            : 1.0;

        return (int) round($lighthouseScore * 0.5 + $passRate * 50);
    }

    /**
     * Grade letter computed from vitals_seo_score.
     */
    public function getVitalsSeoGradeAttribute(): ?string
    {
        $score = $this->vitals_seo_score;
        return $score !== null ? Health::grade($score) : null;
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            ($this->url->label ?? 'Audit') . ' · ' . ($this->completed_at?->diffForHumans() ?? ''),
            route('vitals.audit', $this->id),
        );
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
