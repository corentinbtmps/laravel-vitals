<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * Builds laravel.com doc URLs against the host application's Laravel major version.
 *
 * Reads `Illuminate\Foundation\Application::VERSION` at runtime so a Laravel 12 host
 * gets `/docs/12.x/...` and a Laravel 13 host gets `/docs/13.x/...`. Falls back to
 * the versionless URL (which laravel.com redirects to the latest stable) when the
 * Application class is not available.
 */
final class LaravelDocs
{
    public static function url(string $path): string
    {
        $section = ltrim($path, '/');
        $version = self::detectVersion();

        if ($version === null) {
            return "https://laravel.com/docs/{$section}";
        }

        return "https://laravel.com/docs/{$version}/{$section}";
    }

    private static function detectVersion(): ?string
    {
        if (! class_exists(\Illuminate\Foundation\Application::class)) {
            return null;
        }

        $version = \Illuminate\Foundation\Application::VERSION;

        if (preg_match('/^(\d+)\./', $version, $m) === 1) {
            return $m[1] . '.x';
        }

        return null;
    }
}
