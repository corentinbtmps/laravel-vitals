<?php

declare(strict_types=1);

namespace LaravelVitals\Recommendations;

use LaravelVitals\Enums\Severity;

final class RecommendationRegistry
{
    /** @var array<string, RecommendationDescriptor> */
    private array $map = [];

    public function __construct()
    {
        foreach ([
            'unused-javascript'           => Severity::Warning,
            'unused-css-rules'            => Severity::Warning,
            'unminified-javascript'       => Severity::Warning,
            'unminified-css'              => Severity::Warning,
            'render-blocking-resources'   => Severity::Warning,
            'modern-image-formats'        => Severity::Info,
            'uses-responsive-images'      => Severity::Info,
            'efficient-animated-content'  => Severity::Info,
            'offscreen-images'            => Severity::Info,
            'legacy-javascript'           => Severity::Warning,
            'duplicated-javascript'       => Severity::Info,
            // alpha.70 additions
            'uses-text-compression'       => Severity::Warning,
            'uses-optimized-images'       => Severity::Warning,
            'uses-rel-preconnect'         => Severity::Info,
            'prioritize-lcp-image'        => Severity::Warning,
            'mainthread-work-breakdown'   => Severity::Warning,
            'dom-size'                    => Severity::Warning,
            'redirects'                   => Severity::Warning,
            'server-response-time'        => Severity::Warning,
            'uses-passive-event-listeners' => Severity::Info,
            'no-document-write'           => Severity::Warning,
            'uses-long-cache-ttl'         => Severity::Info,
            'lcp-lazy-loaded'             => Severity::Warning,
            'largest-contentful-paint-element' => Severity::Info,
            'layout-shift-elements'       => Severity::Warning,
            'non-composited-animations'   => Severity::Info,
            'image-size-responsive'       => Severity::Info,
        ] as $key => $sev) {
            $this->register($key, 'lighthouse', 'performance', $sev);
        }

        foreach ([
            'color-contrast'      => Severity::Warning,
            'image-alt'           => Severity::Warning,
            'document-title'      => Severity::Critical,
            'html-has-lang'       => Severity::Critical,
            // alpha.70 additions
            'link-name'           => Severity::Warning,
            'button-name'         => Severity::Warning,
            'meta-viewport'       => Severity::Warning,
            'html-lang-valid'     => Severity::Warning,
            'aria-required-attr'  => Severity::Warning,
            'aria-valid-attr-value' => Severity::Warning,
        ] as $key => $sev) {
            $this->register($key, 'lighthouse', 'accessibility', $sev);
        }

        $this->register('errors-in-console', 'lighthouse', 'best_practices', Severity::Info);
        $this->register('no-vulnerable-libraries', 'lighthouse', 'best_practices', Severity::Critical);

        // alpha.70 best practices
        foreach ([
            'is-on-https'                        => Severity::Critical,
            'geolocation-on-start'               => Severity::Warning,
            'notification-on-start'              => Severity::Warning,
            'password-inputs-can-be-pasted-into' => Severity::Warning,
            'image-aspect-ratio'                 => Severity::Info,
        ] as $key => $sev) {
            $this->register($key, 'lighthouse', 'best_practices', $sev);
        }

        $this->register('meta-description', 'lighthouse', 'seo', Severity::Warning);

        // alpha.70 SEO additions
        foreach ([
            'hreflang'   => Severity::Warning,
            'canonical'  => Severity::Warning,
            'robots-txt' => Severity::Warning,
            'tap-targets' => Severity::Warning,
        ] as $key => $sev) {
            $this->register($key, 'lighthouse', 'seo', $sev);
        }

        $this->register('config-cache-disabled', 'config', 'best_practices', Severity::Warning);
        $this->register('route-cache-disabled', 'config', 'best_practices', Severity::Warning);
        $this->register('view-cache-disabled', 'config', 'best_practices', Severity::Info);
        $this->register('debug-on-prod', 'config', 'best_practices', Severity::Critical);
        $this->register('opcache-disabled', 'config', 'best_practices', Severity::Warning);

        $this->register('missing-php-version', 'static', 'best_practices', Severity::Info);

        $this->register('session-driver-file', 'config', 'best_practices', Severity::Info);
        $this->register('cache-driver-file', 'config', 'best_practices', Severity::Info);
        $this->register('queue-driver-sync-prod', 'config', 'best_practices', Severity::Warning);

        $this->register('n-plus-one-detected', 'backend', 'performance', Severity::Warning);
        $this->register('slow-queries-detected', 'backend', 'performance', Severity::Warning);
        $this->register('slow-views', 'backend', 'performance', Severity::Info);
        $this->register('real-world-perf-degraded', 'backend', 'performance', Severity::Warning);

        // Detail-driven (alpha.12)
        $this->register('excessive-dom-size',     'static',  'best_practices', Severity::Warning);
        $this->register('cache-policy-short',     'static',  'best_practices', Severity::Info);
        $this->register('third-party-blocking',   'backend', 'performance',    Severity::Warning);
        $this->register('large-payload',          'static',  'performance',    Severity::Warning);
        $this->register('bootup-time-high',       'static',  'performance',    Severity::Warning);

        // Lighthouse - additional CWV (alpha.14)
        $this->register('unsized-images',          'lighthouse', 'performance',    Severity::Warning);
        $this->register('font-display',            'lighthouse', 'performance',    Severity::Info);
        $this->register('uses-rel-preload',        'lighthouse', 'performance',    Severity::Info);
        $this->register('uses-http2',              'lighthouse', 'best_practices', Severity::Info);

        // Custom Laravel - alpha.14
        $this->register('octane-not-running',      'config', 'performance',     Severity::Info);
        $this->register('assets-not-hashed',       'static', 'best_practices',  Severity::Warning);
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

    private function register(string $key, string $source, string $category, Severity $severity): void
    {
        $this->map[$key] = new RecommendationDescriptor(
            auditKey:       $key,
            source:         $source,
            category:       $category,
            severity:       $severity,
            titleKey:       "vitals::vitals.recommendations.$key.title",
            descriptionKey: "vitals::vitals.recommendations.$key.description",
        );
    }
}
