<?php

declare(strict_types=1);

namespace LaravelVitals\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelVitals\Vitals authorize(\Closure $callback)
 * @method static \Closure|null authorizeCallback()
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
