<?php

declare(strict_types=1);

namespace LaravelVitals\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelVitals\Enums\Severity;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

/**
 * @property int $id
 * @property string $audit_id
 * @property string $source
 * @property string $audit_key
 * @property string $category
 * @property \LaravelVitals\Enums\Severity $severity
 * @property string $title_key
 * @property string $description_key
 * @property array<string, mixed>|null $translation_params
 * @property array<string, mixed>|null $metrics
 * @property array<int, array<string, mixed>>|null $code_references
 * @property array<int, array<string, mixed>>|null $detail_items
 */
final class Recommendation extends Model implements Searchable
{
    use Prunable;

    public string $searchableType = 'recommendations';

    /** @var string */
    protected $table = 'vitals_audit_recommendations';

    /** @var array<int, string> */
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'severity'           => Severity::class,
            'translation_params' => 'array',
            'metrics'            => 'array',
            'code_references'    => 'array',
            'detail_items'       => 'array',
            'is_demo'            => 'boolean',
        ];
    }

    /**
     * Scalar-only subset of translation_params, safe to pass to __() as the
     * replace argument. Laravel's translator calls mb_substr on each value
     * and blows up on arrays — so we filter out non-scalars (top_patterns,
     * nested data) before substitution.
     *
     * @return array<string, scalar>
     */
    public function getTranslationReplaceParamsAttribute(): array
    {
        $params = is_array($this->translation_params) ? $this->translation_params : [];

        return array_filter($params, static fn ($v): bool => is_scalar($v));
    }

    /**
     * Returns display-ready detail items with url, hint, and wasted_label fields.
     *
     * @return array<int, array{url: string|null, hint: string|null, wasted_label: string|null}>
     */
    public function getFormattedDetailItemsAttribute(): array
    {
        $items = is_array($this->detail_items) ? $this->detail_items : [];

        $formatted = array_map(function (array $item): array {
            $entry = ['url' => $item['url'] ?? null];

            if (isset($item['wasted_bytes']) && (int) $item['wasted_bytes'] > 0) {
                $entry['wasted_label'] = $this->formatBytes((int) $item['wasted_bytes']) . ' wasted';
            } elseif (isset($item['wasted_ms']) && (float) $item['wasted_ms'] > 0) {
                $entry['wasted_label'] = round((float) $item['wasted_ms']) . 'ms blocking';
            } else {
                $entry['wasted_label'] = null;
            }

            $entry['hint'] = match ($this->audit_key) {
                'uses-optimized-images'   => 'Convert to WebP or AVIF',
                'modern-image-formats'    => 'Serve in next-gen format (WebP/AVIF)',
                'uses-text-compression'   => 'Enable gzip or Brotli compression',
                'render-blocking-resources' => 'Defer or async load this resource',
                'unused-javascript'       => 'Code-split or tree-shake this bundle',
                'unused-css-rules'        => 'Purge unused selectors (e.g. Tailwind content config)',
                'uses-responsive-images'  => 'Add srcset for multiple viewport sizes',
                'efficient-animated-content' => 'Convert to MP4/WebM video element',
                'offscreen-images'        => 'Add loading="lazy" attribute',
                'prioritize-lcp-image'    => 'Add fetchpriority="high" to this image',
                'lcp-lazy-loaded'         => 'Remove loading="lazy" from LCP image',
                default                   => null,
            };

            return $entry;
        }, $items);

        return array_slice($formatted, 0, 10);
    }

    /**
     * Format a byte count into a human-readable string (KB or MB).
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 1) . ' MB';
        }

        return round($bytes / 1_024) . ' KB';
    }

    public function getConnectionName(): ?string
    {
        return config('vitals.database') ?? parent::getConnectionName();
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            __($this->title_key, $this->translation_replace_params) . ' (' . $this->audit_key . ')',
            route('vitals.learn') . '#' . $this->audit_key,
        );
    }

    /**
     * @return BelongsTo<Audit, Recommendation>
     */
    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    /**
     * @return Builder<Recommendation>
     */
    public function prunable(): Builder
    {
        return self::query()
            ->where('created_at', '<', now()->subDays((int) config('vitals.retention.days')));
    }
}
