<?php

declare(strict_types=1);

namespace LaravelVitals\Analyzers;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

/**
 * Critical CSS analyzer.
 *
 * For each Blade view detected in an audit, parses class names used in elements
 * that are likely "above the fold" — elements whose class attribute contains one
 * of the hero/header/nav sentinel words.  Generates a recommendation entry
 * "Inline critical CSS" with the found classes listed so developers can extract
 * and inline those rules for faster First Contentful Paint.
 *
 * This is a heuristic: it does not run a real CSS parser but instead pattern-
 * matches Blade templates, which is fast and zero-dependency.
 */
final class CriticalCssAnalyzer implements CodeAnalyzer
{
    /** @var array<int, string> */
    private const SUPPORTED = [
        'render-blocking-resources',
        'unused-css-rules',
        'critical-css',
    ];

    /** @var array<int, string> Sentinel class substrings that suggest above-the-fold elements. */
    private const ABOVE_FOLD_SENTINELS = ['hero', 'header', 'nav', 'banner', 'above-fold', 'masthead'];

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

        $refs = [];
        $allCriticalClasses = [];

        foreach ($this->bladeFiles($viewsDir) as $file) {
            $content = @file_get_contents($file) ?: '';
            $classes  = $this->extractAboveFoldClasses($content);

            if ($classes === []) {
                continue;
            }

            $allCriticalClasses = array_unique([...$allCriticalClasses, ...$classes]);

            $relativePath = ltrim(str_replace($ctx->basePath, '', $file), '/');

            $refs[] = new CodeReference(
                file: $relativePath,
                lineStart: 1,
                lineEnd: 1,
                snippet: implode(' ', $classes),
                hint: 'Consider inlining these critical CSS classes in a <style> block in your <head> to eliminate render-blocking CSS. These classes appear to be used above the fold.',
            );
        }

        return new CodeReferenceCollection($refs);
    }

    /**
     * Extract class names from elements that match above-fold sentinel patterns.
     *
     * @return array<int, string>
     */
    private function extractAboveFoldClasses(string $html): array
    {
        $classes = [];

        // Match any HTML tag whose class attribute contains a sentinel word.
        // Pattern: class="..." on the same element as one of our sentinel words.
        $pattern = '/class=["\']([^"\']+)["\'][^>]*>/i';

        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[1] as $classString) {
                $isAboveFold = false;
                foreach (self::ABOVE_FOLD_SENTINELS as $sentinel) {
                    if (str_contains(strtolower($classString), $sentinel)) {
                        $isAboveFold = true;
                        break;
                    }
                }

                if ($isAboveFold) {
                    foreach (preg_split('/\s+/', trim($classString)) ?: [] as $cls) {
                        if ($cls !== '') {
                            $classes[] = $cls;
                        }
                    }
                }
            }
        }

        return array_values(array_unique($classes));
    }

    /**
     * @return iterable<int, string>
     */
    private function bladeFiles(string $dir): iterable
    {
        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iter as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                yield $file->getPathname();
            }
        }
    }
}
