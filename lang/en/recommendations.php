<?php

declare(strict_types=1);

return [
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
    'outdated-deps' => [
        'title'       => 'Some dependencies are outdated',
        'description' => 'Run `composer outdated` to review updates.',
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
];
