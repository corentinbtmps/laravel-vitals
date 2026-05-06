<?php

declare(strict_types=1);

namespace LaravelVitals\Analyzers;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

final class ComposerAnalyzer implements CodeAnalyzer
{
    public function supports(string $auditKey): bool
    {
        return $auditKey === 'missing-php-version';
    }

    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection
    {
        $path = $ctx->basePath . '/composer.json';
        if (! is_file($path)) {
            return new CodeReferenceCollection();
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new CodeReferenceCollection();
        }

        return match ($auditKey) {
            'missing-php-version' => $this->checkPhp($decoded),
            default               => new CodeReferenceCollection(),
        };
    }

    /** @param array<string, mixed> $composer */
    private function checkPhp(array $composer): CodeReferenceCollection
    {
        $require = $composer['require'] ?? [];
        if (is_array($require) && isset($require['php'])) {
            return new CodeReferenceCollection();
        }

        return new CodeReferenceCollection([
            new CodeReference(
                file: 'composer.json',
                lineStart: 1,
                lineEnd: 1,
                snippet: '"require": { "php": "^8.2" }',
                hint: 'Add an explicit php constraint to require so Composer rejects incompatible PHP versions on install.',
            ),
        ]);
    }
}
