<?php

declare(strict_types=1);

namespace LaravelVitals\Analyzers;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

final class LaravelConfigAnalyzer implements CodeAnalyzer
{
    public function supports(string $auditKey): bool
    {
        return in_array($auditKey, ['config-cache-disabled', 'route-cache-disabled', 'view-cache-disabled', 'debug-on-prod', 'opcache-disabled'], true);
    }

    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection
    {
        $snap = $ctx->configSnapshot;

        return match ($auditKey) {
            'config-cache-disabled' => $this->checkCache($snap, 'config_cached', 'config:cache'),
            'route-cache-disabled'  => $this->checkCache($snap, 'route_cached',  'route:cache'),
            'view-cache-disabled'   => $this->checkCache($snap, 'view_cached',   'view:cache'),
            'debug-on-prod'         => $this->checkDebug($snap),
            'opcache-disabled'      => $this->checkOpcache($snap),
            default                 => new CodeReferenceCollection(),
        };
    }

    /** @param array<string, mixed> $snap */
    private function checkCache(array $snap, string $key, string $command): CodeReferenceCollection
    {
        if (($snap[$key] ?? false) === true) {
            return new CodeReferenceCollection();
        }

        return new CodeReferenceCollection([
            new CodeReference(
                file: 'artisan',
                lineStart: 1,
                lineEnd: 1,
                snippet: "php artisan $command",
                hint: "Run `php artisan $command` in your deploy script.",
            ),
        ]);
    }

    /** @param array<string, mixed> $snap */
    private function checkDebug(array $snap): CodeReferenceCollection
    {
        $env = $snap['app_env'] ?? 'production';
        $debug = $snap['app_debug'] ?? false;

        if ($env === 'production' && $debug === true) {
            return new CodeReferenceCollection([
                new CodeReference(
                    file: '.env',
                    lineStart: 1,
                    lineEnd: 1,
                    snippet: 'APP_DEBUG=true',
                    hint: 'Set APP_DEBUG=false in production. Debug mode leaks stack traces and slows requests.',
                ),
            ]);
        }

        return new CodeReferenceCollection();
    }

    /** @param array<string, mixed> $snap */
    private function checkOpcache(array $snap): CodeReferenceCollection
    {
        if (($snap['opcache_enabled'] ?? true) === true) {
            return new CodeReferenceCollection();
        }

        return new CodeReferenceCollection([
            new CodeReference(
                file: 'php.ini',
                lineStart: 1,
                lineEnd: 1,
                snippet: 'opcache.enable=1',
                hint: 'Enable OPcache in production php.ini.',
            ),
        ]);
    }
}
