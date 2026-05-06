<?php

declare(strict_types=1);

namespace LaravelVitals\Analyzers;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

final class ViteConfigAnalyzer implements CodeAnalyzer
{
    public function supports(string $auditKey): bool
    {
        return in_array($auditKey, ['unminified-javascript', 'unminified-css', 'legacy-javascript'], true);
    }

    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection
    {
        $candidates = ['vite.config.js', 'vite.config.ts', 'vite.config.mjs'];
        $configFile = null;
        foreach ($candidates as $name) {
            if (is_file($ctx->basePath . '/' . $name)) {
                $configFile = $name;
                break;
            }
        }

        if ($configFile === null) {
            return new CodeReferenceCollection();
        }

        $lines = @file($ctx->basePath . '/' . $configFile, FILE_IGNORE_NEW_LINES) ?: [];

        $refs = [];
        foreach ($lines as $i => $line) {
            if (preg_match('/minify\s*:\s*false/i', $line) === 1) {
                $refs[] = new CodeReference(
                    file: $configFile,
                    lineStart: $i + 1,
                    lineEnd: $i + 1,
                    snippet: trim($line),
                    hint: 'Enable minification (default in Vite production builds).',
                );
            }
        }

        return new CodeReferenceCollection($refs);
    }
}
