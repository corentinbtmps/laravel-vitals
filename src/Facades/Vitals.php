<?php

declare(strict_types=1);

namespace LaravelVitals\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelVitals\Vitals authorize(\Closure $callback)
 * @method static \Closure|null authorizeCallback()
 * @method static \LaravelVitals\Vitals driver(string $name)
 * @method static \LaravelVitals\Models\Audit audit(\LaravelVitals\Models\Url|string $urlOrLabel, ?string $device = null, bool $sync = true)
 * @method static \Illuminate\Bus\Batch auditAll(?string $device = null)
 *
 * @see \LaravelVitals\Vitals
 */
final class Vitals extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vitals';
    }
}
