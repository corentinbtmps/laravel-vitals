<?php

declare(strict_types=1);

namespace LaravelVitals\Recommendations;

final class RecommendationRegistry
{
    /** @var array<string, RecommendationDescriptor> */
    private array $map = [];

    public function __construct()
    {
        foreach ([
            'unused-javascript'         => 'warning',
            'unused-css-rules'          => 'warning',
            'unminified-javascript'     => 'warning',
            'unminified-css'            => 'warning',
            'render-blocking-resources' => 'warning',
            'modern-image-formats'      => 'info',
            'uses-responsive-images'    => 'info',
            'efficient-animated-content'=> 'info',
            'offscreen-images'          => 'info',
            'legacy-javascript'         => 'warning',
            'duplicated-javascript'     => 'info',
        ] as $key => $sev) {
            $this->register($key, 'lighthouse', 'performance', $sev);
        }

        foreach ([
            'color-contrast'   => 'warning',
            'image-alt'        => 'warning',
            'document-title'   => 'critical',
            'html-has-lang'    => 'critical',
        ] as $key => $sev) {
            $this->register($key, 'lighthouse', 'accessibility', $sev);
        }

        $this->register('errors-in-console', 'lighthouse', 'best_practices', 'info');
        $this->register('no-vulnerable-libraries', 'lighthouse', 'best_practices', 'critical');
        $this->register('meta-description', 'lighthouse', 'seo', 'warning');

        $this->register('config-cache-disabled', 'config', 'best_practices', 'warning');
        $this->register('route-cache-disabled', 'config', 'best_practices', 'warning');
        $this->register('view-cache-disabled', 'config', 'best_practices', 'info');
        $this->register('debug-on-prod', 'config', 'best_practices', 'critical');
        $this->register('opcache-disabled', 'config', 'best_practices', 'warning');

        $this->register('missing-php-version', 'static', 'best_practices', 'info');

        $this->register('session-driver-file', 'config', 'best_practices', 'info');
        $this->register('cache-driver-file', 'config', 'best_practices', 'info');
        $this->register('queue-driver-sync-prod', 'config', 'best_practices', 'warning');

        $this->register('n-plus-one-detected', 'backend', 'performance', 'warning');
        $this->register('slow-queries-detected', 'backend', 'performance', 'warning');
        $this->register('slow-views', 'backend', 'performance', 'info');
        $this->register('real-world-perf-degraded', 'backend', 'performance', 'warning');
    }

    public function get(string $auditKey): ?RecommendationDescriptor
    {
        return $this->map[$auditKey] ?? null;
    }

    /** @return array<int, string> */
    public function allKeys(): array
    {
        return array_keys($this->map);
    }

    private function register(string $key, string $source, string $category, string $severity): void
    {
        $this->map[$key] = new RecommendationDescriptor(
            auditKey:       $key,
            source:         $source,
            category:       $category,
            severity:       $severity,
            titleKey:       "vitals::recommendations.$key.title",
            descriptionKey: "vitals::recommendations.$key.description",
        );
    }
}
