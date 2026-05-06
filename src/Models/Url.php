<?php

declare(strict_types=1);

namespace LaravelVitals\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Url declared in the host app's vitals config and audited by the package.
 *
 * @property int $id
 * @property string $label
 * @property string $path
 * @property string $device
 * @property array<string, mixed>|null $options
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
final class Url extends Model
{
    use Prunable;

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
            'options' => 'array',
            'enabled' => 'boolean',
            'is_demo' => 'boolean',
        ];
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
