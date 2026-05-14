<?php

declare(strict_types=1);

namespace LaravelVitals\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LaravelVitals\Enums\Device;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

/**
 * Url declared in the host app's vitals config and audited by the package.
 *
 * @property int $id
 * @property string $label
 * @property string $path
 * @property \LaravelVitals\Enums\Device $device
 * @property array<string, mixed>|null $options
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon|null $pinned_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
final class Url extends Model implements Searchable
{
    use Prunable;

    public string $searchableType = 'urls';

    /** @var string */
    protected $table = 'vitals_urls';

    /** @var array<int, string> */
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'device'    => Device::class,
            'options'   => 'array',
            'enabled'   => 'boolean',
            'is_demo'   => 'boolean',
            'pinned_at' => 'datetime',
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Url>  $q
     * @return \Illuminate\Database\Eloquent\Builder<Url>
     */
    public function scopePinned(Builder $q): Builder
    {
        return $q->whereNotNull('pinned_at')->orderByDesc('pinned_at');
    }

    public function getConnectionName(): ?string
    {
        return config('vitals.database') ?? parent::getConnectionName();
    }

    /**
     * @return HasMany<Audit, Url>
     */
    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class, 'url_id');
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->label . ' (' . $this->path . ')',
            route('vitals.url', $this->id),
        );
    }

    /**
     * Records that should be deleted by `model:prune`.
     *
     * @return Builder<Url>
     */
    public function prunable(): Builder
    {
        // Urls themselves are not pruned; they are the configuration. Audits
        // and telemetry rows below them have their own prune logic.
        return self::query()->whereRaw('1 = 0');
    }
}
