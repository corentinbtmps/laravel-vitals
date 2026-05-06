<?php

declare(strict_types=1);

namespace LaravelVitals\Analyzers;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

final class EnvironmentAnalyzer implements CodeAnalyzer
{
    public function supports(string $auditKey): bool
    {
        return in_array($auditKey, ['session-driver-file', 'cache-driver-file', 'queue-driver-sync-prod'], true);
    }

    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection
    {
        $snap = $ctx->configSnapshot;
        $env = $snap['app_env'] ?? 'production';
        $isProd = $env !== 'local' && $env !== 'testing';

        return match ($auditKey) {
            'session-driver-file'    => $isProd && ($snap['session_driver'] ?? null) === 'file'
                ? $this->envRef('SESSION_DRIVER=file', 'Use redis or database for session storage on multi-process hosts.')
                : new CodeReferenceCollection(),
            'cache-driver-file'      => $isProd && ($snap['cache_driver'] ?? null) === 'file'
                ? $this->envRef('CACHE_STORE=file', 'Use redis or memcached for cache in production.')
                : new CodeReferenceCollection(),
            'queue-driver-sync-prod' => $isProd && ($snap['queue_default'] ?? null) === 'sync'
                ? $this->envRef('QUEUE_CONNECTION=sync', 'Configure a real queue (redis/database/sqs) in production.')
                : new CodeReferenceCollection(),
            default                  => new CodeReferenceCollection(),
        };
    }

    private function envRef(string $snippet, string $hint): CodeReferenceCollection
    {
        return new CodeReferenceCollection([
            new CodeReference(file: '.env', lineStart: 1, lineEnd: 1, snippet: $snippet, hint: $hint),
        ]);
    }
}
