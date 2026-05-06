<?php

declare(strict_types=1);

namespace LaravelVitals\Analyzers;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

final class BladeViewAnalyzer implements CodeAnalyzer
{
    public function supports(string $auditKey): bool
    {
        return $auditKey === 'slow-views';
    }

    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection
    {
        $slow = $ctx->configSnapshot['slow_views'] ?? [];
        if (! is_array($slow) || $slow === []) {
            return new CodeReferenceCollection();
        }

        $refs = [];
        foreach ($slow as $entry) {
            $name = $entry['name'] ?? null;
            $timeMs = (float) ($entry['time_ms'] ?? 0);
            if (! is_string($name) || $name === '') {
                continue;
            }

            $relative = 'resources/views/' . str_replace('.', '/', $name) . '.blade.php';
            $absolute = $ctx->basePath . '/' . $relative;
            if (! is_file($absolute)) {
                continue;
            }

            $refs[] = new CodeReference(
                file: $relative,
                lineStart: 1,
                lineEnd: 1,
                snippet: '@view ' . $name,
                hint: sprintf('This view rendered in %.1fms. Consider caching, partial extraction, or fewer DB calls.', $timeMs),
            );
        }

        return new CodeReferenceCollection($refs);
    }
}
