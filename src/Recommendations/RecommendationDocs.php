<?php

declare(strict_types=1);

namespace LaravelVitals\Recommendations;

use LaravelVitals\Support\LaravelDocs;

/**
 * Static knowledge base for every audit_key.
 *
 * Provides "why it matters", canonical doc links (web.dev, MDN, Laravel docs),
 * and good/bad code examples to help developers fix issues.
 */
final class RecommendationDocs
{
    /**
     * Returns the docs entry for an audit_key, or null when no entry exists.
     *
     * @return array{
     *     why: string,
     *     docs: array<int, array{label: string, url: string}>,
     *     good?: string,
     *     bad?: string,
     *     impact?: string,
     * }|null
     */
    public static function for(string $auditKey): ?array
    {
        return self::all()[$auditKey] ?? null;
    }

    /**
     * @return array<string, array{
     *     why: string,
     *     docs: array<int, array{label: string, url: string}>,
     *     good?: string,
     *     bad?: string,
     *     impact?: string,
     * }>
     */
    public static function all(): array
    {
        return [
            // ============================================================
            // PERFORMANCE - Lighthouse opportunities
            // ============================================================

            'unused-javascript' => [
                'why' => 'Unused JavaScript still costs network bandwidth, parse time, and CPU. The browser must download, parse, and compile every byte you ship — even if it never executes.',
                'docs' => [
                    ['label' => 'Lighthouse: Reduce unused JavaScript', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/unused-javascript'],
                    ['label' => 'Vite: Code splitting', 'url' => 'https://vitejs.dev/guide/features.html#dynamic-import'],
                ],
                'good' => "// Lazy-load route-specific JS\nconst Dashboard = () => import('./Dashboard.js');\n\n// Or use @vite directive in Blade\n@vite(['resources/js/app.js'])",
                'bad' => "<!-- Loading the entire bundle on every page -->\n<script src=\"/build/everything.js\"></script>",
                'impact' => 'Typical savings: 30-60% bundle size, 200-500ms LCP improvement',
            ],

            'unused-css-rules' => [
                'why' => 'Unused CSS rules force the browser to parse selectors that never match, delay first paint, and bloat the critical render path.',
                'docs' => [
                    ['label' => 'Lighthouse: Remove unused CSS', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/unused-css-rules'],
                    ['label' => 'Tailwind: Optimizing for production', 'url' => 'https://tailwindcss.com/docs/optimizing-for-production'],
                ],
                'good' => "// tailwind.config.js — content paths trigger purging\ncontent: ['./resources/**/*.blade.php']",
                'bad' => "/* Loading the full Bootstrap CSS bundle */\n@import 'bootstrap/dist/css/bootstrap.min.css';",
            ],

            'unminified-javascript' => [
                'why' => 'Minified JavaScript reduces transfer size by ~30-50% (whitespace, comments, mangled names) without changing behaviour.',
                'docs' => [
                    ['label' => 'Lighthouse: Minify JavaScript', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/unminified-javascript'],
                ],
                'good' => "// vite.config.js (default in production)\nbuild: { minify: 'esbuild' }",
                'bad' => "// Disabling minification in production\nbuild: { minify: false }",
            ],

            'unminified-css' => [
                'why' => 'Minified CSS shrinks transfer size and parsing time. Vite and Laravel Mix do this by default in production builds.',
                'docs' => [
                    ['label' => 'Lighthouse: Minify CSS', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/unminified-css'],
                ],
            ],

            'render-blocking-resources' => [
                'why' => 'Render-blocking resources in <head> delay first paint until the browser downloads and parses them. Every kilobyte adds latency, especially on slow networks.',
                'docs' => [
                    ['label' => 'Lighthouse: Eliminate render-blocking resources', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/render-blocking-resources'],
                    ['label' => 'web.dev: Defer non-critical CSS', 'url' => 'https://web.dev/articles/defer-non-critical-css'],
                ],
                'good' => "<!-- Defer non-critical scripts -->\n<script src=\"analytics.js\" defer></script>\n\n<!-- Async for independent scripts -->\n<script src=\"ads.js\" async></script>",
                'bad' => "<!-- Blocks parsing until downloaded -->\n<script src=\"jquery.js\"></script>",
            ],

            'modern-image-formats' => [
                'why' => 'WebP and AVIF compress 25-50% smaller than JPEG/PNG at equivalent quality. Lower bytes = faster LCP on image-heavy pages.',
                'docs' => [
                    ['label' => 'Lighthouse: Serve images in next-gen formats', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/uses-webp-images'],
                    ['label' => 'web.dev: Use AVIF for better compression', 'url' => 'https://web.dev/articles/compress-images-avif'],
                ],
                'good' => "<picture>\n  <source srcset=\"hero.avif\" type=\"image/avif\">\n  <source srcset=\"hero.webp\" type=\"image/webp\">\n  <img src=\"hero.jpg\" alt=\"Hero\">\n</picture>",
                'bad' => "<img src=\"hero.jpg\" alt=\"Hero\"><!-- 800 KB JPEG -->",
            ],

            'uses-responsive-images' => [
                'why' => 'Serving a 2400px image to a 360px mobile screen wastes 4-8x the bandwidth. srcset tells the browser which variant to fetch based on viewport.',
                'docs' => [
                    ['label' => 'Lighthouse: Use responsive images', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/uses-responsive-images'],
                    ['label' => 'MDN: srcset', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#srcset'],
                ],
                'good' => "<img\n  src=\"hero-800w.jpg\"\n  srcset=\"hero-400w.jpg 400w, hero-800w.jpg 800w, hero-1600w.jpg 1600w\"\n  sizes=\"(max-width: 600px) 400px, 800px\"\n  alt=\"Hero\">",
                'bad' => "<img src=\"hero-2400w.jpg\" alt=\"Hero\">",
            ],

            'efficient-animated-content' => [
                'why' => 'Animated GIFs are 5-10x larger than equivalent MP4 video. Convert to WebM/MP4 and use a <video> element.',
                'docs' => [
                    ['label' => 'Lighthouse: Use video formats for animated content', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/efficient-animated-content'],
                ],
                'good' => "<video autoplay loop muted playsinline>\n  <source src=\"animation.webm\" type=\"video/webm\">\n  <source src=\"animation.mp4\" type=\"video/mp4\">\n</video>",
                'bad' => "<img src=\"animation.gif\" alt=\"Animation\"><!-- 8 MB -->",
            ],

            'offscreen-images' => [
                'why' => 'Lazy-loading defers off-screen images until the user scrolls near them. Reduces initial payload and frees up the network for above-the-fold content.',
                'docs' => [
                    ['label' => 'Lighthouse: Defer offscreen images', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/offscreen-images'],
                    ['label' => 'MDN: loading=lazy', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#loading'],
                ],
                'good' => "<img src=\"below-fold.jpg\" loading=\"lazy\" alt=\"...\">",
                'bad' => "<img src=\"below-fold.jpg\" alt=\"...\"><!-- loaded eagerly -->",
            ],

            'legacy-javascript' => [
                'why' => 'Polyfilled bundles (for IE11, etc.) bloat modern browser payloads. Use module/nomodule pattern to ship clean ES2017+ to evergreen browsers.',
                'docs' => [
                    ['label' => 'web.dev: Publish, ship, and install modern JavaScript', 'url' => 'https://web.dev/articles/publish-modern-javascript'],
                    ['label' => 'Vite: target option', 'url' => 'https://vitejs.dev/config/build-options.html#build-target'],
                ],
                'good' => "// vite.config.js\nbuild: { target: 'es2017' }",
                'bad' => "// Forcing ES5 transpilation for everyone\nbuild: { target: 'es5' }",
            ],

            'duplicated-javascript' => [
                'why' => 'When multiple bundles include the same module, users download the code twice. Vite vendor splitting deduplicates shared dependencies.',
                'docs' => [
                    ['label' => 'Chrome DevTools: Coverage tab', 'url' => 'https://developer.chrome.com/docs/devtools/coverage'],
                ],
            ],

            // ============================================================
            // ACCESSIBILITY
            // ============================================================

            'color-contrast' => [
                'why' => 'Text below the WCAG AA threshold (4.5:1 for body, 3:1 for large) is unreadable for users with low vision. Affects ~5% of web users.',
                'docs' => [
                    ['label' => 'WCAG 2.1: Contrast (Minimum)', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html'],
                    ['label' => 'WebAIM: Contrast checker', 'url' => 'https://webaim.org/resources/contrastchecker/'],
                ],
            ],

            'image-alt' => [
                'why' => 'Screen readers cannot describe images without alt text. Decorative images should use alt="" to be skipped.',
                'docs' => [
                    ['label' => 'Deque: image-alt', 'url' => 'https://dequeuniversity.com/rules/axe/4.7/image-alt'],
                    ['label' => 'MDN: img alt', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#alt'],
                ],
                'good' => "<img src=\"product.jpg\" alt=\"Red Nike Air Max sneakers\">\n<img src=\"divider.svg\" alt=\"\"><!-- decorative -->",
                'bad' => "<img src=\"product.jpg\"><!-- no alt -->",
            ],

            'document-title' => [
                'why' => 'Pages without <title> are inaccessible (screen readers announce "Untitled") and crippling for SEO. Search engines use it as the result snippet.',
                'docs' => [
                    ['label' => 'Deque: document-title', 'url' => 'https://dequeuniversity.com/rules/axe/4.7/document-title'],
                ],
                'good' => "<title>Product page — Acme Store</title>",
            ],

            'html-has-lang' => [
                'why' => "Without <html lang=\"...\"> screen readers fall back to the user's default language pronunciation, which can mangle text in other languages.",
                'docs' => [
                    ['label' => 'Deque: html-has-lang', 'url' => 'https://dequeuniversity.com/rules/axe/4.7/html-has-lang'],
                ],
                'good' => "<html lang=\"{{ str_replace('_', '-', app()->getLocale()) }}\">",
                'bad' => "<html><!-- no lang -->",
            ],

            // ============================================================
            // BEST PRACTICES
            // ============================================================

            'errors-in-console' => [
                'why' => 'Console errors signal runtime bugs that degrade UX silently. Treat them as bugs to fix before they reach production.',
                'docs' => [
                    ['label' => 'Lighthouse: Browser errors logged to the console', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/errors-in-console'],
                ],
            ],

            'no-vulnerable-libraries' => [
                'why' => 'A bundled JS library with a known CVE puts every visitor at risk. Run `npm audit` regularly and ship updates promptly.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoid vulnerable libraries', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/no-vulnerable-libraries'],
                    ['label' => 'npm audit docs', 'url' => 'https://docs.npmjs.com/cli/v10/commands/npm-audit'],
                ],
            ],

            // ============================================================
            // SEO
            // ============================================================

            'meta-description' => [
                'why' => 'Search engines display meta descriptions in result snippets. A clear 50-160 char description significantly improves click-through rate.',
                'docs' => [
                    ['label' => 'Google Search: meta description', 'url' => 'https://developers.google.com/search/docs/appearance/snippet'],
                    ['label' => 'Lighthouse: Document has a meta description', 'url' => 'https://developer.chrome.com/docs/lighthouse/seo/meta-description'],
                ],
                'good' => "<meta name=\"description\" content=\"Buy authentic Nike Air Max sneakers — fast shipping, free returns.\">",
            ],

            // ============================================================
            // LARAVEL CONFIG (custom)
            // ============================================================

            'config-cache-disabled' => [
                'why' => 'Without `php artisan config:cache`, Laravel parses every config file on every request. The cached version is loaded once and stays in OPcache.',
                'docs' => [
                    ['label' => 'Laravel: Configuration caching', 'url' => LaravelDocs::url('configuration#configuration-caching')],
                ],
                'good' => "# In your deploy script\nphp artisan config:cache",
                'impact' => 'Typical savings: 5-15ms per request',
            ],

            'route-cache-disabled' => [
                'why' => "Route registration is one of Laravel's slowest boot operations. Caching pre-compiles all routes into a single file.",
                'docs' => [
                    ['label' => 'Laravel: Route caching', 'url' => LaravelDocs::url('routing#route-caching')],
                ],
                'good' => "# In your deploy script\nphp artisan route:cache",
                'impact' => 'Typical savings: 10-30ms per request on apps with 100+ routes',
            ],

            'view-cache-disabled' => [
                'why' => 'Pre-compiling Blade views avoids per-request compilation. Views are still compiled on first render, but `view:cache` warms them up at deploy time.',
                'docs' => [
                    ['label' => 'Laravel: Blade caching', 'url' => LaravelDocs::url('views#optimizing-views')],
                ],
                'good' => "# In your deploy script\nphp artisan view:cache",
            ],

            'debug-on-prod' => [
                'why' => 'APP_DEBUG=true exposes internal stack traces (database credentials, file paths) on errors AND inflates response time by collecting debug data.',
                'docs' => [
                    ['label' => 'Laravel: Debug mode', 'url' => LaravelDocs::url('configuration#debug-mode')],
                ],
                'good' => "# .env in production\nAPP_DEBUG=false\nAPP_ENV=production",
                'bad' => "# .env in production — leaks stack traces!\nAPP_DEBUG=true",
            ],

            'opcache-disabled' => [
                'why' => 'Without OPcache, PHP recompiles every script on every request. With OPcache, compiled bytecode is cached in shared memory — typically 2-3x speedup on the application layer.',
                'docs' => [
                    ['label' => 'PHP: OPcache', 'url' => 'https://www.php.net/manual/en/book.opcache.php'],
                    ['label' => 'Laravel deployment', 'url' => LaravelDocs::url('deployment#optimizing-configuration-loading')],
                ],
                'good' => "; In production php.ini\nopcache.enable=1\nopcache.memory_consumption=256\nopcache.max_accelerated_files=20000\nopcache.validate_timestamps=0",
            ],

            'missing-php-version' => [
                'why' => 'An explicit PHP constraint in composer.json prevents `composer install` from succeeding on incompatible production servers — fail fast at deploy time, not in runtime.',
                'docs' => [
                    ['label' => 'Composer: require schema', 'url' => 'https://getcomposer.org/doc/04-schema.md#require'],
                ],
                'good' => "{\n  \"require\": {\n    \"php\": \"^8.2\"\n  }\n}",
            ],

            'session-driver-file' => [
                'why' => 'File-based sessions create lock contention and disk I/O on every request. Multi-process production hosts (PHP-FPM with > 1 worker) need a centralised store like Redis.',
                'docs' => [
                    ['label' => 'Laravel: Session config', 'url' => LaravelDocs::url('session#configuration')],
                ],
                'good' => "# .env\nSESSION_DRIVER=redis\nSESSION_CONNECTION=default",
                'bad' => "# .env (single-server only)\nSESSION_DRIVER=file",
            ],

            'cache-driver-file' => [
                'why' => 'File cache reads/writes hit disk on every operation. Redis or Memcached operate from RAM, typically 10-100x faster.',
                'docs' => [
                    ['label' => 'Laravel: Cache config', 'url' => LaravelDocs::url('cache#configuration')],
                ],
                'good' => "# .env\nCACHE_STORE=redis",
            ],

            'queue-driver-sync-prod' => [
                'why' => 'Sync queue runs jobs inline within the HTTP request, blocking the response until the job completes. Defeats the purpose of queueing.',
                'docs' => [
                    ['label' => 'Laravel: Queue drivers', 'url' => LaravelDocs::url('queues#driver-prerequisites')],
                ],
                'good' => "# .env\nQUEUE_CONNECTION=redis",
                'bad' => "# .env (dev-only)\nQUEUE_CONNECTION=sync",
            ],

            // ============================================================
            // BACKEND TELEMETRY
            // ============================================================

            'n-plus-one-detected' => [
                'why' => 'N+1 queries happen when accessing a relation inside a loop without eager loading. Each iteration triggers a separate SQL query — 100 items = 101 queries.',
                'docs' => [
                    ['label' => 'Laravel: Eager loading', 'url' => LaravelDocs::url('eloquent-relationships#eager-loading')],
                    ['label' => 'Beyond Code: N+1 detector', 'url' => 'https://github.com/beyondcode/laravel-query-detector'],
                ],
                'good' => "// Eager-load the relationship\n\$users = User::with('posts')->get();\nforeach (\$users as \$user) {\n    echo \$user->posts->count();\n}",
                'bad' => "// 1 + N queries — fires a SELECT for each user\n\$users = User::all();\nforeach (\$users as \$user) {\n    echo \$user->posts->count();\n}",
                'impact' => 'Typical savings: cuts query count by 90%, 200-1500ms TTFB improvement',
            ],

            'slow-queries-detected' => [
                'why' => 'Queries over 50ms typically indicate missing indexes, full table scans, or inefficient joins. Use EXPLAIN to diagnose, then add indexes or rewrite.',
                'docs' => [
                    ['label' => 'Laravel: Listening for queries', 'url' => LaravelDocs::url('database#listening-for-query-events')],
                    ['label' => 'MySQL: EXPLAIN', 'url' => 'https://dev.mysql.com/doc/refman/8.0/en/explain.html'],
                ],
                'good' => "// In a migration — add an index\nSchema::table('orders', fn (\$t) => \$t->index(['user_id', 'created_at']));",
            ],

            'slow-views' => [
                'why' => 'Views that take > 50ms to render usually loop over collections without `lazy()`, render expensive partials, or call relationships not eager-loaded.',
                'docs' => [
                    ['label' => 'Laravel: Blade caching', 'url' => LaravelDocs::url('blade#caching')],
                    ['label' => 'Laravel: Lazy collections', 'url' => LaravelDocs::url('collections#lazy-collections')],
                ],
            ],

            'real-world-perf-degraded' => [
                'why' => 'Synthetic Lighthouse audits run on a clean machine with stable network. Real production traffic faces variable load, geographic latency, and contention — surfacing problems synthetic tests miss.',
                'docs' => [
                    ['label' => 'web.dev: Optimize LCP', 'url' => 'https://web.dev/articles/optimize-lcp'],
                    ['label' => 'Laravel Pulse', 'url' => LaravelDocs::url('pulse')],
                ],
            ],

            // ============================================================
            // DETAIL-DRIVEN (alpha.12)
            // ============================================================

            'excessive-dom-size' => [
                'why' => 'Large DOMs (>1500 nodes) slow style recalculations and layout, causing jank during scroll and interactions. Often caused by deeply nested templates or rendering huge lists without virtualization.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoid an excessive DOM size', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/dom-size'],
                ],
                'good' => "// Paginate or virtualize large lists\n\$items = \$query->paginate(50);",
                'bad' => "// Rendering 5000 items at once\n@foreach (\$thousandsOfItems as \$item)\n  <div>...</div>\n@endforeach",
            ],

            'cache-policy-short' => [
                'why' => 'Short cache TTL (< 30 days) on static assets forces repeat downloads on return visits. Long TTL combined with hashed filenames (Vite default) is safe and cuts repeat-visit load times to near zero.',
                'docs' => [
                    ['label' => 'Lighthouse: Use efficient cache policy on static assets', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/uses-long-cache-ttl'],
                ],
                'good' => "# nginx — long cache for hashed assets\nlocation ~* \\.(js|css|webp|woff2)\$ {\n    expires 1y;\n    add_header Cache-Control \"public, immutable\";\n}",
            ],

            'third-party-blocking' => [
                'why' => 'Third-party scripts (analytics, ads, chat widgets) often run synchronously on page load and steal main-thread time, delaying interactivity. Defer or self-host critical ones.',
                'docs' => [
                    ['label' => 'Lighthouse: Reduce the impact of third-party code', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/third-party-summary'],
                    ['label' => 'web.dev: Loading third-party scripts', 'url' => 'https://web.dev/articles/efficiently-load-third-party-javascript'],
                ],
                'good' => "<!-- Defer non-critical 3rd-party JS -->\n<script async src=\"https://www.googletagmanager.com/gtag/js?id=GA_ID\"></script>",
            ],

            'large-payload' => [
                'why' => 'Large pages (> 2 MB) hurt LCP on 3G/4G connections. Typical culprits: uncompressed images, oversized JS bundles, unused vendor libraries shipped to all pages.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoid enormous network payloads', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/total-byte-weight'],
                ],
            ],

            'bootup-time-high' => [
                'why' => 'A single script taking >500ms to evaluate blocks the main thread for that duration. Code-splitting separates critical and deferred logic so the page becomes interactive sooner.',
                'docs' => [
                    ['label' => 'Lighthouse: Reduce JavaScript execution time', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/bootup-time'],
                    ['label' => 'Vite: Manual chunks', 'url' => 'https://vitejs.dev/guide/build.html#chunking-strategy'],
                ],
                'good' => "// vite.config.js — split vendor chunks\nrollupOptions: {\n    output: {\n        manualChunks: { vendor: ['react', 'react-dom'] }\n    }\n}",
            ],

            // ============================================================
            // LARAVEL-SPECIFIC CWV (alpha.14)
            // ============================================================

            'unsized-images' => [
                'why' => 'When the browser parses an <img> without explicit dimensions, it has to wait until the image downloads before knowing how much space to reserve. The result: visible content jumps when the image arrives — high CLS.',
                'docs' => [
                    ['label' => 'web.dev: Image dimensions', 'url' => 'https://web.dev/articles/optimize-cls#images_without_dimensions'],
                    ['label' => 'CSS aspect-ratio', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/CSS/aspect-ratio'],
                ],
                'good' => "<!-- Browser reserves space immediately -->\n<img src=\"hero.jpg\" width=\"1200\" height=\"600\" alt=\"...\">\n\n<!-- Or with CSS aspect-ratio -->\n<img src=\"hero.jpg\" style=\"aspect-ratio: 2/1; width: 100%;\" alt=\"...\">",
                'bad' => "<img src=\"hero.jpg\" alt=\"...\"><!-- causes layout shift -->",
                'impact' => 'Typical savings: cuts CLS from 0.2-0.4 to under 0.1',
            ],

            'font-display' => [
                'why' => 'Without `font-display: swap`, browsers may show invisible text until the webfont loads (FOIT — Flash of Invisible Text). With `swap`, fallback text is visible immediately and the webfont swaps in when ready.',
                'docs' => [
                    ['label' => 'Lighthouse: All text remains visible during webfont loads', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/font-display'],
                    ['label' => 'MDN: font-display', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display'],
                ],
                'good' => "@font-face {\n    font-family: 'Inter';\n    src: url('/fonts/inter.woff2') format('woff2');\n    font-display: swap;\n}",
                'bad' => "@font-face {\n    font-family: 'Inter';\n    src: url('/fonts/inter.woff2') format('woff2');\n    /* no font-display => browser default 'block' = FOIT */\n}",
            ],

            'uses-rel-preload' => [
                'why' => 'The browser discovers resources by parsing HTML and CSS in order. Late-discovered resources (e.g. fonts referenced inside @font-face) start downloading too late. `<link rel="preload">` tells the browser to fetch them in parallel with the initial HTML.',
                'docs' => [
                    ['label' => 'web.dev: Preload critical assets', 'url' => 'https://web.dev/articles/preload-critical-assets'],
                    ['label' => 'Laravel Vite: Asset prefetching', 'url' => LaravelDocs::url('vite#asset-prefetching')],
                ],
                'good' => "{{-- In Blade layout --}}\n<head>\n    @vite(['resources/js/app.js'])\n    <link rel=\"preload\" as=\"font\" type=\"font/woff2\" href=\"/fonts/inter.woff2\" crossorigin>\n    <link rel=\"preload\" as=\"image\" href=\"/images/hero.webp\">\n</head>",
                'impact' => 'Typical savings: 100-400ms LCP on font-heavy pages',
            ],

            'uses-http2' => [
                'why' => 'HTTP/1.1 establishes one connection per resource. HTTP/2 multiplexes many requests over a single connection, drastically reducing handshake overhead. Most managed Laravel hosts (Forge, Vapor, Cloudflare) enable HTTP/2 by default — but a misconfigured custom server may not.',
                'docs' => [
                    ['label' => 'Lighthouse: Use HTTP/2', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/uses-http2'],
                    ['label' => 'Cloudflare: HTTP/2 vs HTTP/3', 'url' => 'https://www.cloudflare.com/learning/performance/http2-vs-http1.1/'],
                ],
                'good' => "# nginx — enable HTTP/2\nlisten 443 ssl http2;\nssl_certificate /path/to/cert.pem;",
            ],

            'octane-not-running' => [
                'why' => 'Each Laravel request bootstraps the framework: loading config, registering service providers, and parsing routes. Octane keeps the application in memory across requests via Swoole / FrankenPHP / RoadRunner, slashing bootstrap time. Most useful for high-traffic apps where TTFB matters.',
                'docs' => [
                    ['label' => 'Laravel Octane', 'url' => LaravelDocs::url('octane')],
                    ['label' => 'FrankenPHP — modern PHP server', 'url' => 'https://frankenphp.dev/'],
                ],
                'good' => "# .env\nOCTANE_SERVER=frankenphp\n\n# Install + run\ncomposer require laravel/octane\nphp artisan octane:install\nphp artisan octane:start",
                'impact' => 'Typical savings: 40-200ms per request TTFB',
            ],

            'assets-not-hashed' => [
                'why' => 'Hashed asset names (`app-Df8gK3p2.js`) let browsers cache assets forever — when content changes, the hash changes, the URL changes, the browser fetches the new version. Without hashes, you cannot use long cache TTLs without serving stale content. Vite\'s default config produces hashed filenames automatically.',
                'docs' => [
                    ['label' => 'Vite: Build options', 'url' => 'https://vitejs.dev/config/build-options.html'],
                    ['label' => 'Laravel Vite directive', 'url' => LaravelDocs::url('vite')],
                ],
                'good' => "{{-- Blade layout — Vite handles hashed filenames automatically --}}\n@vite(['resources/css/app.css', 'resources/js/app.js'])",
                'bad' => "{{-- Hardcoded asset paths bypass Vite's hashing --}}\n<script src=\"/build/app.js\"></script>",
            ],
        ];
    }
}
