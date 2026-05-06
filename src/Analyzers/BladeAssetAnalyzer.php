<?php

declare(strict_types=1);

namespace LaravelVitals\Analyzers;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

final class BladeAssetAnalyzer implements CodeAnalyzer
{
    /** @var array<int, string> */
    private const SUPPORTED = [
        'unused-javascript',
        'unused-css-rules',
        'unminified-javascript',
        'unminified-css',
        'render-blocking-resources',
        'legacy-javascript',
        'duplicated-javascript',
    ];

    public function supports(string $auditKey): bool
    {
        return in_array($auditKey, self::SUPPORTED, true);
    }

    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection
    {
        $items = $auditData['details']['items'] ?? [];

        $needles = [];
        foreach ($items as $item) {
            $url = $item['url'] ?? null;
            if (is_string($url) && $url !== '') {
                $needles[] = basename(parse_url($url, PHP_URL_PATH) ?: $url);
            }
        }

        if ($needles === []) {
            return new CodeReferenceCollection();
        }

        $viewsDir = $ctx->basePath . '/resources/views';
        if (! is_dir($viewsDir)) {
            return new CodeReferenceCollection();
        }

        $refs = [];

        foreach ($this->bladeFiles($viewsDir) as $file) {
            $lines = @file($file, FILE_IGNORE_NEW_LINES) ?: [];

            foreach ($lines as $i => $line) {
                foreach ($needles as $needle) {
                    if ($needle !== '' && str_contains($line, $needle)) {
                        $refs[] = new CodeReference(
                            file: $this->relativise($file, $ctx->basePath),
                            lineStart: $i + 1,
                            lineEnd: $i + 1,
                            snippet: trim($line),
                            hint: 'Use @vite([...]) to bundle and version this asset, or add `defer` / `async`.',
                        );
                        break;
                    }
                }
            }
        }

        return new CodeReferenceCollection($refs);
    }

    /**
     * @return iterable<int, string>
     */
    private function bladeFiles(string $dir): iterable
    {
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($iter as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                yield $file->getPathname();
            }
        }
    }

    private function relativise(string $absolute, string $base): string
    {
        return ltrim(str_replace($base, '', $absolute), '/');
    }
}
