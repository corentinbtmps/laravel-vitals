<?php

declare(strict_types=1);

namespace LaravelVitals\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

/**
 * @property int $id
 * @property string $audit_id
 * @property string $source
 * @property string $audit_key
 * @property string $category
 * @property string $severity
 * @property string $title_key
 * @property string $description_key
 * @property array<string, mixed>|null $translation_params
 * @property array<string, mixed>|null $metrics
 * @property array<int, array<string, mixed>>|null $code_references
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
            'translation_params' => 'array',
            'metrics'            => 'array',
            'code_references'    => 'array',
            'is_demo'            => 'boolean',
        ];
    }

    public function getConnectionName(): ?string
    {
        return config('vitals.database') ?? parent::getConnectionName();
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            __($this->title_key, (array) ($this->translation_params ?? [])) . ' (' . $this->audit_key . ')',
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
