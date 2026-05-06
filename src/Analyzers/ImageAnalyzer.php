<?php

declare(strict_types=1);

namespace LaravelVitals\Analyzers;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

final class ImageAnalyzer implements CodeAnalyzer
{
    /** @var array<int, string> */
    private const SUPPORTED = [
        'modern-image-formats',
        'offscreen-images',
        'uses-responsive-images',
        'efficient-animated-content',
    ];

    public function supports(string $auditKey): bool
    {
        return in_array($auditKey, self::SUPPORTED, true);
    }

    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection
    {
        $viewsDir = $ctx->basePath . '/resources/views';
        if (! is_dir($viewsDir)) {
            return new CodeReferenceCollection();
        }

        $items = $auditData['details']['items'] ?? [];
        $needles = [];
        foreach ($items as $item) {
            $url = $item['url'] ?? null;
            if (is_string($url) && $url !== '') {
                $needles[] = basename(parse_url($url, PHP_URL_PATH) ?: $url);
            }
        }

        $refs = [];

        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewsDir, \FilesystemIterator::SKIP_DOTS));

        foreach ($iter as $file) {
            if (! $file instanceof \SplFileInfo || ! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $lines = @file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [];

            foreach ($lines as $i => $line) {
                if (! str_contains($line, '<img')) {
                    continue;
                }

                $matchesNeedle = $needles === [];
                foreach ($needles as $needle) {
                    if ($needle !== '' && str_contains($line, $needle)) {
                        $matchesNeedle = true;
                        break;
                    }
                }
                if (! $matchesNeedle) {
                    continue;
                }

                $hint = match ($auditKey) {
                    'offscreen-images'        => 'Add loading="lazy" to defer offscreen images.',
                    'modern-image-formats'    => 'Serve this image as WebP or AVIF for smaller payloads.',
                    'uses-responsive-images'  => 'Add srcset / sizes so the browser picks an appropriately-sized variant.',
                    'efficient-animated-content' => 'Prefer a video <video> element over animated GIF.',
                    default                   => null,
                };

                $refs[] = new CodeReference(
                    file: ltrim(str_replace($ctx->basePath, '', $file->getPathname()), '/'),
                    lineStart: $i + 1,
                    lineEnd: $i + 1,
                    snippet: trim($line),
                    hint: $hint,
                );
            }
        }

        return new CodeReferenceCollection($refs);
    }
}
