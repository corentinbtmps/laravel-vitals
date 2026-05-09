<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'placeholder' => [
            'body' => 'The dashboard is being assembled. Real Livewire pages land in plan 5.',
        ],
    ],
    'api' => [
        'not_found'  => 'Resource not found.',
        'no_audits'  => 'No completed audits found for this URL.',
        'forbidden'  => 'Access denied.',
        'validation' => 'Invalid query parameters.',
        'error'      => 'An unexpected error occurred.',
    ],
    'commands' => [],
    'empty' => [
        'overview_no_urls' => [
            'title' => 'Add your first URL to start monitoring',
            'body'  => 'Laravel Vitals tracks Lighthouse scores and backend telemetry for the URLs you configure. Start by adding the URLs you want to monitor.',
            'cta'   => 'Configure URLs',
            'docs'  => 'Read docs',
        ],
        'overview_no_audits' => [
            'title' => 'No audits yet',
            'body'  => 'Run your first audit to populate the dashboard. Audits can run via artisan command, scheduled task, or your CI pipeline.',
            'cta'   => 'Open URLs',
            'docs'  => 'Read docs',
        ],
        'urls_no_urls' => [
            'title' => 'No URLs configured',
            'body'  => 'Configure URLs in config/vitals.php under the urls key, or run the demo seeder for sample data.',
            'docs'  => 'Read docs',
        ],
        'recos_no_recos' => [
            'title' => 'No recommendations yet',
            'body'  => 'Run an audit to surface optimization opportunities. Each recommendation links to the exact file and line in your app.',
            'cta'   => 'Browse known issues',
            'docs'  => 'Read docs',
        ],
        'insights_no_history' => [
            'title' => 'Not enough audit history',
            'body'  => 'Insights compare audits across time. Run at least 2 audits per URL to see trends and regressions.',
            'docs'  => 'Read docs',
        ],
        'budgets_no_budgets' => [
            'title' => 'No budgets defined',
            'body'  => 'Performance budgets fail your CI when scores drop below a threshold. Define them in config/vitals.php under budgets.',
            'docs'  => 'Read docs',
        ],
    ],
    'onboarding' => [
        'banner_title'   => 'Get started with Laravel Vitals',
        'banner_subtitle' => ':count of :total steps complete',
        'dismiss'        => 'Skip onboarding',
        'dismiss_confirm' => 'Hide this banner permanently. You can still access all features from the navigation.',
        'steps' => [
            'urls' => [
                'title' => 'Configure your first URL',
                'cta'   => 'Add URL',
            ],
            'audit' => [
                'title' => 'Run your first audit',
                'cta'   => 'Run audit',
            ],
            'notifications' => [
                'title' => 'Configure notifications (optional)',
                'cta'   => 'Configure',
            ],
            'budgets' => [
                'title' => 'Set performance budgets',
                'cta'   => 'Set budgets',
            ],
        ],
    ],
    'tooltip' => [
        'pin'          => 'Pin to favorites',
        'unpin'        => 'Unpin from favorites',
        'last_audit_at' => 'Last audited :timestamp',
        'metric_score' => 'Composite Lighthouse Performance score (0–100)',
        'metric_lcp'   => 'Largest Contentful Paint — time until the largest visible content renders. Good < 2.5s',
        'metric_inp'   => 'Interaction to Next Paint — input latency. Good < 200ms',
        'metric_cls'   => 'Cumulative Layout Shift — visual stability. Good < 0.1',
        'metric_ttfb'  => 'Time to First Byte — server response time. Good < 800ms',
        'cwv_lcp'      => 'Largest Contentful Paint — time until the largest visible content element is rendered. Good = under 2.5s.',
        'cwv_cls'      => 'Cumulative Layout Shift — how much visible content unexpectedly shifts during load. Good = under 0.1.',
        'cwv_inp'      => 'Interaction to Next Paint — latency between user input and the next paint. Good = under 200ms.',
        'cwv_ttfb'     => 'Time to First Byte — how long the server takes to respond with the first byte. Good = under 800ms.',
        'score_label'  => 'Lighthouse :label score',
    ],
    'spotlight' => [
        'placeholder'            => 'Search URLs, audits, recommendations…',
        'group_urls'             => 'URLs',
        'group_audits'           => 'Audits',
        'group_recommendations'  => 'Recommendations',
        'group_learn'            => 'Learn',
        'empty'                  => 'No results',
        'hint'                   => 'Type at least 2 characters to search',
        'kbd_navigate'           => 'Navigate',
        'kbd_open'               => 'Open',
        'button_label'           => 'Search...',
    ],
    'rum' => [
        'title'                 => 'Real User Monitoring',
        'subtitle'              => 'Core Web Vitals collected from real visitors — privacy-respecting, no IP storage.',
        'period_24h'            => 'Last 24 hours',
        'period_7d'             => 'Last 7 days',
        'period_30d'            => 'Last 30 days',
        'period_90d'            => 'Last 90 days',
        'no_data'               => 'No data',
        'url_breakdown'         => 'Per-URL breakdown',
        'inp_attribution_title' => 'INP attribution — slow interactions',
        'inp_attribution_subtitle' => 'Interaction targets and event types contributing to poor INP, extracted from web-vitals attribution data.',
        'col_url'               => 'URL',
        'col_samples'           => 'Samples',
        'col_element'           => 'Element',
        'col_event_type'        => 'Event',
        'empty' => [
            'title' => 'No RUM data yet',
            'body'  => 'Add the @vitalsRum directive to your main layout\'s <head> to start collecting real-user Core Web Vitals.',
        ],
    ],
    'queries' => [
        'title'                   => 'Query baseline',
        'subtitle'                => 'avg / p75 / p95 queries and query time per route — sorted by p95 to surface the heaviest routes.',
        'baseline_title'          => 'Query statistics per route',
        'baseline_subtitle'       => 'Routes with a p75 queries count > 2× the previous period are flagged as regressions.',
        'memory_hogs_title'       => 'Memory hogs',
        'memory_hogs_subtitle'    => 'Top 5 routes by p75 peak memory usage.',
        'regression'              => 'regression',
        'col_route'               => 'Route',
        'col_samples'             => 'Samples',
        'empty' => [
            'title' => 'No query data yet',
            'body'  => 'Backend telemetry must be enabled. Enable always_capture or trigger an audit to populate query baselines.',
        ],
    ],
    'recommendations' => [
        'unused-javascript' => [
            'title'       => 'Reduce unused JavaScript',
            'description' => 'JavaScript shipped to the browser but never executed wastes bandwidth and parse time.',
        ],
        'unused-css-rules' => [
            'title'       => 'Remove unused CSS',
            'description' => 'Unused CSS bytes still need to be downloaded and parsed by the browser.',
        ],
        'unminified-javascript' => [
            'title'       => 'Minify JavaScript',
            'description' => 'Minified JavaScript reduces transfer size with no behaviour change.',
        ],
        'unminified-css' => [
            'title'       => 'Minify CSS',
            'description' => 'Minified CSS reduces transfer size.',
        ],
        'render-blocking-resources' => [
            'title'       => 'Eliminate render-blocking resources',
            'description' => 'Resources in the head block first paint until they download. Defer or inline critical CSS.',
        ],
        'modern-image-formats' => [
            'title'       => 'Serve images in next-gen formats',
            'description' => 'WebP and AVIF compress better than JPEG/PNG.',
        ],
        'uses-responsive-images' => [
            'title'       => 'Use responsive images',
            'description' => 'Add srcset and sizes so the browser picks the best variant for each viewport.',
        ],
        'efficient-animated-content' => [
            'title'       => 'Use video for animated content',
            'description' => 'Animated GIFs are heavy. Encode the animation as MP4/WebM and use <video>.',
        ],
        'offscreen-images' => [
            'title'       => 'Lazy-load offscreen images',
            'description' => 'Add loading="lazy" so below-the-fold images are fetched on demand.',
        ],
        'legacy-javascript' => [
            'title'       => 'Avoid serving legacy JavaScript to modern browsers',
            'description' => 'Polyfilled bundles bloat modern browser payloads.',
        ],
        'duplicated-javascript' => [
            'title'       => 'Remove duplicated modules',
            'description' => 'Multiple bundles include the same module. Tune Vite vendor splitting.',
        ],
        'color-contrast' => [
            'title'       => 'Improve color contrast',
            'description' => 'Text contrast is below the WCAG AA threshold.',
        ],
        'image-alt' => [
            'title'       => 'Add alt text to images',
            'description' => 'Images without alt text are inaccessible to screen readers.',
        ],
        'document-title' => [
            'title'       => 'Add a document title',
            'description' => 'Pages without a <title> are inaccessible and bad for SEO.',
        ],
        'html-has-lang' => [
            'title'       => 'Declare a document language',
            'description' => 'Add <html lang="..."> for assistive technologies.',
        ],
        'errors-in-console' => [
            'title'       => 'Fix browser console errors',
            'description' => 'Console errors hint at runtime bugs that can degrade UX.',
        ],
        'no-vulnerable-libraries' => [
            'title'       => 'Update vulnerable JavaScript libraries',
            'description' => 'A bundled library has a known vulnerability. Upgrade or remove it.',
        ],
        'meta-description' => [
            'title'       => 'Add a meta description',
            'description' => 'Search engines display the meta description in result snippets.',
        ],
        'config-cache-disabled' => [
            'title'       => 'Cache the Laravel configuration',
            'description' => 'Run `php artisan config:cache` in production deploys.',
        ],
        'route-cache-disabled' => [
            'title'       => 'Cache the route table',
            'description' => 'Run `php artisan route:cache` in production deploys.',
        ],
        'view-cache-disabled' => [
            'title'       => 'Pre-compile Blade views',
            'description' => 'Run `php artisan view:cache` to avoid runtime compilation.',
        ],
        'debug-on-prod' => [
            'title'       => 'Disable APP_DEBUG in production',
            'description' => 'Debug mode leaks stack traces and slows requests.',
        ],
        'opcache-disabled' => [
            'title'       => 'Enable OPcache',
            'description' => 'OPcache caches the compiled PHP bytecode and is essential for production performance.',
        ],
        'missing-php-version' => [
            'title'       => 'Pin a PHP version',
            'description' => 'Add an explicit php constraint in composer.json so deploys reject incompatible versions.',
        ],
        'session-driver-file' => [
            'title'       => 'Switch session driver from file',
            'description' => 'Use redis or database for sessions on multi-process production hosts.',
        ],
        'cache-driver-file' => [
            'title'       => 'Switch cache driver from file',
            'description' => 'Use redis or memcached in production.',
        ],
        'queue-driver-sync-prod' => [
            'title'       => 'Configure a real queue connection',
            'description' => 'Sync queue runs jobs in-process and blocks responses. Use redis/database/sqs in production.',
        ],
        'n-plus-one-detected' => [
            'title'       => 'N+1 query pattern detected',
            'description' => 'A query pattern repeated above the configured threshold during this audit.',
        ],
        'slow-queries-detected' => [
            'title'       => 'Slow database queries detected',
            'description' => 'One or more queries exceeded the slow-query threshold during this audit.',
        ],
        'slow-views' => [
            'title'       => 'Slow Blade views detected',
            'description' => 'A rendered view took longer than the configured threshold.',
        ],
        'real-world-perf-degraded' => [
            'title'       => 'Real-world performance worse than synthetic',
            'description' => 'Real-traffic telemetry (Pulse / Telescope) shows P95 metrics significantly above the synthetic Lighthouse audit. Investigate production-only conditions: load, third-party scripts, geography.',
        ],
        'excessive-dom-size' => [
            'title'       => 'Avoid excessive DOM size',
            'description' => 'Page has :count DOM elements. Large DOMs slow rendering and JS interactions. Aim for under 1500 elements.',
        ],
        'cache-policy-short' => [
            'title'       => 'Improve cache policy',
            'description' => ':count resource(s) have a TTL under 30 days. Long-term caching reduces repeat-visit load times.',
        ],
        'third-party-blocking' => [
            'title'       => 'Third-party scripts blocking the main thread',
            'description' => ':count third-party origin(s) (:entities) block the main thread > 250ms. Defer or self-host where possible.',
        ],
        'large-payload' => [
            'title'       => 'Reduce page weight',
            'description' => 'Total page weight is :mb MB. Large payloads hurt LCP on slow connections. Compress images and split JavaScript bundles.',
        ],
        'bootup-time-high' => [
            'title'       => 'Reduce JavaScript execution time',
            'description' => 'A single script takes :ms ms to evaluate. Code-split, lazy-load, or remove unused JavaScript.',
        ],
        'unsized-images' => [
            'title'       => 'Image elements have explicit width and height',
            'description' => 'Set explicit width/height attributes on images to reserve layout space. Browsers can compute the aspect ratio early and avoid layout shifts when the image loads.',
        ],
        'font-display' => [
            'title'       => 'Ensure text remains visible during webfont load',
            'description' => 'Use `font-display: swap` so the browser shows a fallback font immediately and swaps to the webfont when ready. Avoids invisible text (FOIT).',
        ],
        'uses-rel-preload' => [
            'title'       => 'Preload key requests',
            'description' => 'Add `<link rel="preload">` for resources discovered late in the page (e.g. Vite-emitted modules, hero images, critical fonts). The browser fetches them earlier.',
        ],
        'uses-http2' => [
            'title'       => 'Use HTTP/2 (or HTTP/3)',
            'description' => 'HTTP/2 multiplexes requests over a single connection — faster than HTTP/1.1 for resource-heavy pages. Most modern hosts (Forge, Vapor, Cloudflare) enable it by default.',
        ],
        'octane-not-running' => [
            'title'       => 'Consider Laravel Octane for lower TTFB',
            'description' => 'Octane keeps the application bootstrapped between requests (Swoole / FrankenPHP / RoadRunner), eliminating the per-request bootstrap cost. Typical TTFB savings: 40-200ms.',
        ],
        'assets-not-hashed' => [
            'title'       => 'Asset filenames are not content-hashed',
            'description' => 'Without content hashes (`app-Df8gK3p2.js`), you cannot cache assets aggressively — every change requires invalidation. Vite generates hashed filenames by default; verify your build output.',
        ],
    ],
];
