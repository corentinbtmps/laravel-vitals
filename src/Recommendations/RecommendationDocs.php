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

            'uses-text-compression' => [
                'why' => 'Text-based resources (HTML, CSS, JS, JSON) compress 60-80% with gzip and even better with Brotli. Without compression every byte travels over the wire as-is, slowing FCP and LCP directly.',
                'docs' => [
                    ['label' => 'Lighthouse: Enable text compression', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/uses-text-compression'],
                    ['label' => 'web.dev: Minify and compress network payloads', 'url' => 'https://web.dev/articles/reduce-network-payloads-using-text-compression'],
                ],
                'good' => "# nginx — enable gzip + Brotli\ngzip on;\ngzip_types text/plain text/css application/javascript application/json;\ngzip_min_length 1024;\n\n# Brotli (ngx_brotli module)\nbrotli on;\nbrotli_types text/plain text/css application/javascript;",
                'bad' => "# No gzip / Brotli block in nginx.conf\n# Responses delivered uncompressed — 3-5x larger than necessary",
                'impact' => 'Typical savings: 60-80% transfer size on JS/CSS, 150-400ms LCP improvement',
            ],

            'uses-optimized-images' => [
                'why' => 'Unoptimized JPEG/PNG images contain metadata and redundant pixel data. Lossless or lossy compression can cut size 30-60% with no perceptible quality difference — directly improving LCP.',
                'docs' => [
                    ['label' => 'Lighthouse: Efficiently encode images', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/uses-optimized-images'],
                    ['label' => 'Intervention Image for Laravel', 'url' => 'https://image.intervention.io/v3'],
                ],
                'good' => "// Intervention Image v3 — optimize on upload\nuse Intervention\\Image\\ImageManager;\nuse Intervention\\Image\\Drivers\\Gd\\Driver;\n\n\$manager = new ImageManager(new Driver());\n\$manager->read(\$path)\n    ->toWebp(quality: 80)\n    ->save(storage_path('app/public/images/hero.webp'));",
                'bad' => "// Storing raw upload with no compression\n\$request->file('image')->store('images'); // may be a 4 MB JPEG",
                'impact' => 'Typical savings: 30-70% image size, significant LCP improvement on image-heavy pages',
            ],

            'uses-rel-preconnect' => [
                'why' => 'Each connection to a third-party origin (fonts.googleapis.com, cdn.jsdelivr.net) requires a DNS lookup + TCP handshake + TLS negotiation — often 100-300ms. `<link rel="preconnect">` initiates these during HTML parsing, before the resource is actually requested.',
                'docs' => [
                    ['label' => 'Lighthouse: Preconnect to required origins', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/uses-rel-preconnect'],
                    ['label' => 'web.dev: Establish network connections early', 'url' => 'https://web.dev/articles/preconnect-and-dns-prefetch'],
                ],
                'good' => "{{-- In Blade <head> --}}\n<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n<link rel=\"dns-prefetch\" href=\"https://cdn.jsdelivr.net\">",
                'bad' => "{{-- No preconnect: browser discovers the origin only when parsing the font link --}}\n<link href=\"https://fonts.googleapis.com/css2?family=Inter\" rel=\"stylesheet\">",
                'impact' => 'Typical savings: 100-300ms per third-party origin on initial load',
            ],

            'prioritize-lcp-image' => [
                'why' => 'The LCP element is the most critical resource for user-perceived load speed. Adding `fetchpriority="high"` tells the browser to fetch it immediately, above other resources with the same priority, shaving 100-500ms off LCP.',
                'docs' => [
                    ['label' => 'web.dev: Optimize LCP', 'url' => 'https://web.dev/articles/optimize-lcp'],
                    ['label' => 'web.dev: LCP — Largest Contentful Paint', 'url' => 'https://web.dev/articles/lcp'],
                ],
                'good' => "{{-- Hero image — the likely LCP element --}}\n<img\n  src=\"/images/hero.webp\"\n  fetchpriority=\"high\"\n  loading=\"eager\"\n  decoding=\"async\"\n  width=\"1200\" height=\"600\"\n  alt=\"Hero\">",
                'bad' => "{{-- No priority hint — browser treats it equally with other resources --}}\n<img src=\"/images/hero.webp\" alt=\"Hero\">",
                'impact' => 'Typical savings: 100-500ms LCP',
            ],

            'mainthread-work-breakdown' => [
                'why' => 'Heavy JavaScript parsing, compiling, and executing on the main thread blocks all rendering and user interaction. Tasks over 50ms are "long tasks" that cause jank. Code-splitting and deferring non-critical JS are the primary fixes.',
                'docs' => [
                    ['label' => 'Lighthouse: Minimize main-thread work', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/mainthread-work-breakdown'],
                    ['label' => 'web.dev: Long tasks', 'url' => 'https://web.dev/articles/optimize-long-tasks'],
                ],
                'good' => "// Break up heavy work with scheduler.yield() (Chrome 115+)\nasync function processData(items) {\n    for (const item of items) {\n        process(item);\n        await scheduler.yield(); // yield between items\n    }\n}\n\n// Vite manual chunks to split vendor code\nrollupOptions: { output: { manualChunks: { vendor: ['lodash', 'moment'] } } }",
                'bad' => "// Heavy synchronous loop on page load blocks the main thread\nconst result = massiveDataSet.map(computeExpensive);",
                'impact' => 'Typical improvement: reduces TBT by 200-800ms, INP drops to < 200ms',
            ],

            'dom-size' => [
                'why' => 'Pages with > 1500 DOM nodes suffer slower style recalculation, layout, and paint. Every queried selector must traverse the whole tree. Paginate large lists or use virtual scrolling.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoid an excessive DOM size', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/dom-size'],
                ],
                'good' => "// Paginate results in Laravel\n\$items = Item::query()->paginate(50);\n\n// Livewire lazy loading for heavy components\n#[Lazy]\nclass HeavyList extends Component {}",
                'bad' => "@foreach (\$allItems as \$item)\n    {{-- Rendering 5000+ items at once bloats the DOM --}}\n    <div class=\"item\">{{ \$item->name }}</div>\n@endforeach",
                'impact' => 'Typical improvement: style recalculation 2-5x faster, smoother scrolling',
            ],

            'redirects' => [
                'why' => 'Each HTTP redirect adds a full round-trip — typically 100-300ms on mobile. Redirect chains multiply this cost. The most common Laravel culprit is HTTP→HTTPS redirect that could be eliminated with HSTS or server-level HTTPS enforcement.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoid multiple page redirects', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/redirects'],
                ],
                'good' => "# Enforce HTTPS at the server level (not in PHP)\n# nginx:\nserver {\n    listen 80;\n    return 301 https://\$host\$request_uri;\n}\n\n# With HSTS, browsers skip the redirect entirely on repeat visits:\nadd_header Strict-Transport-Security \"max-age=31536000; includeSubDomains\";",
                'bad' => "// Multiple redirect hops:\n// http://example.com -> https://example.com -> https://www.example.com -> /home\n// Each hop adds a full RTT",
                'impact' => 'Typical savings: 100-300ms per eliminated redirect',
            ],

            'server-response-time' => [
                'why' => 'A TTFB > 600ms directly drags down LCP — the browser cannot start rendering until the first byte arrives. Laravel-specific causes include disabled OPcache, no config/route cache, slow database queries, and no Redis for sessions/cache.',
                'docs' => [
                    ['label' => 'Lighthouse: Reduce server response time (TTFB)', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/server-response-time'],
                    ['label' => 'web.dev: Time to First Byte', 'url' => 'https://web.dev/articles/ttfb'],
                    ['label' => 'Laravel: Deployment optimization', 'url' => LaravelDocs::url('deployment#optimization')],
                ],
                'good' => "# Full deploy-time optimization\nphp artisan optimize   # config + route + view cache\n\n; php.ini (critical for any PHP app)\nopcache.enable=1\nopcache.validate_timestamps=0\nopcache.memory_consumption=256\n\n# Redis for sessions + cache\nSESSION_DRIVER=redis\nCACHE_STORE=redis",
                'bad' => "# No caches, default file session/cache, APP_DEBUG=true\n# Every request re-reads config files and re-registers routes",
                'impact' => 'Typical savings: 100-500ms TTFB with OPcache + config cache',
            ],

            'uses-passive-event-listeners' => [
                'why' => 'Touch and wheel event listeners without `{ passive: true }` block the browser from scrolling until the handler completes. This forces the browser to wait up to 50ms per scroll event, causing visible jank.',
                'docs' => [
                    ['label' => 'Lighthouse: Use passive event listeners', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/uses-passive-event-listeners'],
                    ['label' => 'MDN: passive event listeners', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener#passive'],
                ],
                'good' => "// Passive listener — browser can scroll immediately\ndocument.addEventListener('touchstart', handler, { passive: true });\ndocument.addEventListener('wheel', handler, { passive: true });",
                'bad' => "// Non-passive — browser waits for handler before scrolling\ndocument.addEventListener('touchstart', handler); // defaults to passive: false",
                'impact' => 'Typical improvement: scroll jank eliminated, INP improves by 30-100ms',
            ],

            'no-document-write' => [
                'why' => 'The deprecated `document.write()` API blocks HTML parsing because the browser cannot continue until the injected content is processed. On slow connections this can pause rendering by seconds. Use DOM manipulation instead.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoid document.write()', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/no-document-write'],
                    ['label' => 'MDN: Document.write()', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/API/Document/write'],
                ],
                'good' => "// Use DOM manipulation instead\nconst el = document.createElement('script');\nel.src = 'analytics.js';\nel.async = true;\ndocument.head.appendChild(el);",
                'bad' => "// Deprecated API that blocks HTML parsing\n// document.write('<script src=\"analytics.js\"></script>');",
                'impact' => 'Removes potentially seconds of blocking time on 2G/3G connections',
            ],

            'uses-long-cache-ttl' => [
                'why' => 'Short cache TTLs on static assets force re-downloads on repeat visits, wasting bandwidth and slowing pages for returning users. Vite\'s content-hashed filenames make it safe to cache assets for a year.',
                'docs' => [
                    ['label' => 'Lighthouse: Serve static assets with an efficient cache policy', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/uses-long-cache-ttl'],
                ],
                'good' => "# nginx — 1-year cache for Vite-hashed assets\nlocation ~* /build/assets/ {\n    expires 1y;\n    add_header Cache-Control \"public, immutable\";\n}",
                'bad' => "# No explicit cache headers — browsers re-validate every request\nlocation /build/ {\n    # missing expires / Cache-Control\n}",
                'impact' => 'Repeat visits load 90-100% from cache — near-instant for returning users',
            ],

            'lcp-lazy-loaded' => [
                'why' => 'The LCP image has `loading="lazy"` which intentionally delays its fetch. The browser won\'t load it until it becomes visible — but it\'s the most important resource on the page. Remove `loading="lazy"` from above-the-fold images.',
                'docs' => [
                    ['label' => 'web.dev: Optimize LCP', 'url' => 'https://web.dev/articles/optimize-lcp'],
                    ['label' => 'web.dev: Browser-level lazy-loading for the web', 'url' => 'https://web.dev/articles/browser-level-image-lazy-loading'],
                ],
                'good' => "{{-- Hero / LCP image: eager + high priority --}}\n<img src=\"hero.webp\" fetchpriority=\"high\" loading=\"eager\" alt=\"Hero\">",
                'bad' => "{{-- Do NOT lazy-load your LCP image --}}\n<img src=\"hero.webp\" loading=\"lazy\" alt=\"Hero\"><!-- delays LCP! -->",
                'impact' => 'Typical savings: 200-800ms LCP',
            ],

            'largest-contentful-paint-element' => [
                'why' => 'Knowing which element is the LCP lets you focus optimization efforts. Common fixes: preload the image, remove lazy-load, upgrade to WebP, preconnect to its origin, or inline its critical CSS.',
                'docs' => [
                    ['label' => 'Lighthouse: Largest Contentful Paint element', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/largest-contentful-paint-element'],
                    ['label' => 'web.dev: Optimize LCP', 'url' => 'https://web.dev/articles/optimize-lcp'],
                ],
                'good' => "{{-- Once you know the LCP element, apply these optimizations --}}\n<img\n  src=\"/images/hero.webp\"\n  fetchpriority=\"high\"\n  loading=\"eager\"\n  width=\"1200\" height=\"600\"\n  alt=\"Hero\">",
            ],

            'layout-shift-elements' => [
                'why' => 'CLS is caused by elements shifting after initial render — typically images without dimensions, dynamically injected banners, or web fonts causing text reflow. Identifying the shifting element is the first step to fixing CLS.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoid large layout shifts', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/layout-shift-elements'],
                    ['label' => 'web.dev: Optimize CLS', 'url' => 'https://web.dev/articles/optimize-cls'],
                ],
                'good' => "{{-- Reserve space before the image loads --}}\n<img src=\"product.jpg\" width=\"400\" height=\"300\" alt=\"Product\">\n\n{{-- Or with CSS aspect-ratio --}}\n<div style=\"aspect-ratio: 4/3; overflow: hidden\">\n  <img src=\"product.jpg\" alt=\"Product\">\n</div>",
                'bad' => "{{-- No dimensions — causes layout shift when image loads --}}\n<img src=\"product.jpg\" alt=\"Product\">",
                'impact' => 'Fixing the top shift element typically drops CLS from 0.2-0.4 to under 0.1',
            ],

            'non-composited-animations' => [
                'why' => 'Animations that trigger layout or paint (animating `top`, `left`, `width`, `height`) run on the main thread and cause jank. Animating only `transform` and `opacity` allows the browser to offload work to the GPU compositor thread.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoid non-composited animations', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/non-composited-animations'],
                    ['label' => 'web.dev: High-performance animations', 'url' => 'https://web.dev/articles/animations-guide'],
                ],
                'good' => "/* GPU-composited — runs off main thread */\n@keyframes slideIn {\n    from { transform: translateX(-100%); }\n    to   { transform: translateX(0); }\n}",
                'bad' => "/* Triggers layout — runs on main thread, causes jank */\n@keyframes slideIn {\n    from { left: -100%; }\n    to   { left: 0; }\n}",
                'impact' => 'Typical improvement: animation FPS from 30-40 to 60fps',
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

            'link-name' => [
                'why' => 'Links without discernible text (e.g. icon-only links, empty `<a>` tags) are announced as "link" by screen readers with no destination context. Add visible text, `aria-label`, or `aria-labelledby`.',
                'docs' => [
                    ['label' => 'Deque: link-name', 'url' => 'https://dequeuniversity.com/rules/axe/4.7/link-name'],
                    ['label' => 'MDN: The Anchor element', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML/Element/a'],
                ],
                'good' => "<!-- Visible text -->\n<a href=\"/dashboard\">Go to dashboard</a>\n\n<!-- Or aria-label for icon links -->\n<a href=\"/settings\" aria-label=\"Open settings\">\n    <svg aria-hidden=\"true\">...</svg>\n</a>",
                'bad' => "<!-- Empty link — screen reader says 'link' with no destination -->\n<a href=\"/settings\"><svg>...</svg></a>",
            ],

            'button-name' => [
                'why' => 'Buttons without accessible names are announced as "button" by screen readers. Users with visual impairments cannot tell what action will occur. Always provide text content or `aria-label`.',
                'docs' => [
                    ['label' => 'Deque: button-name', 'url' => 'https://dequeuniversity.com/rules/axe/4.7/button-name'],
                    ['label' => 'MDN: The Button element', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button'],
                ],
                'good' => "<!-- With visible label -->\n<button type=\"button\">Save changes</button>\n\n<!-- Icon-only button with aria-label -->\n<button type=\"button\" aria-label=\"Delete item\">\n    <svg aria-hidden=\"true\">...</svg>\n</button>",
                'bad' => "<!-- Screen reader says 'button' with no context -->\n<button type=\"button\"><svg>...</svg></button>",
            ],

            'meta-viewport' => [
                'why' => 'Without a correct viewport meta tag, mobile browsers render the page at desktop width and scale it down. Users see tiny text and must pinch-to-zoom. `user-scalable=no` also fails accessibility requirements.',
                'docs' => [
                    ['label' => 'Lighthouse: Has a meta viewport tag', 'url' => 'https://developer.chrome.com/docs/lighthouse/accessibility/meta-viewport'],
                    ['label' => 'MDN: Viewport meta tag', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML/Viewport_meta_tag'],
                ],
                'good' => "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">",
                'bad' => "<!-- Missing viewport tag, or disabling zoom -->\n<meta name=\"viewport\" content=\"width=device-width, user-scalable=no\"><!-- fails a11y -->",
            ],

            'html-lang-valid' => [
                'why' => 'An invalid BCP47 language code (e.g. `lang="en-123"` or `lang="fr-INVALID"`) means assistive technologies fall back to default pronunciation. Use the standard code for your language.',
                'docs' => [
                    ['label' => 'Deque: html-lang-valid', 'url' => 'https://dequeuniversity.com/rules/axe/4.7/html-lang-valid'],
                    ['label' => 'IANA: Language subtag registry', 'url' => 'https://www.iana.org/assignments/language-subtag-registry/language-subtag-registry'],
                ],
                'good' => "<!-- Valid BCP47 codes: en, fr, de, es, zh-Hans, pt-BR -->\n<html lang=\"en\">\n<html lang=\"fr\">\n<html lang=\"pt-BR\">",
                'bad' => "<!-- Incorrect / made-up subtags -->\n<html lang=\"EN-US-INVALID\">",
            ],

            'aria-required-attr' => [
                'why' => 'ARIA roles require certain attributes to function correctly. For example, `role="checkbox"` requires `aria-checked`. Missing required attributes break assistive technology semantics entirely.',
                'docs' => [
                    ['label' => 'Deque: aria-required-attr', 'url' => 'https://dequeuniversity.com/rules/axe/4.7/aria-required-attr'],
                    ['label' => 'MDN: ARIA', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA'],
                ],
                'good' => "<!-- role=checkbox requires aria-checked -->\n<div role=\"checkbox\" aria-checked=\"false\" tabindex=\"0\">Option A</div>\n\n<!-- role=combobox requires aria-expanded -->\n<input role=\"combobox\" aria-expanded=\"false\" aria-autocomplete=\"list\">",
                'bad' => "<!-- Missing required attribute -->\n<div role=\"checkbox\">Option A</div><!-- no aria-checked -->",
            ],

            'aria-valid-attr-value' => [
                'why' => 'ARIA attribute values must be from the allowed set. An invalid value (e.g. `aria-live="sometimes"`) is ignored by screen readers, silently breaking the intended accessible behaviour.',
                'docs' => [
                    ['label' => 'Deque: aria-valid-attr-value', 'url' => 'https://dequeuniversity.com/rules/axe/4.7/aria-valid-attr-value'],
                    ['label' => 'W3C: ARIA in HTML', 'url' => 'https://www.w3.org/TR/html-aria/'],
                ],
                'good' => "<!-- Valid values only -->\n<div aria-live=\"polite\">Loading…</div>\n<button aria-expanded=\"true\">Menu</button>",
                'bad' => "<!-- Invalid values — ignored by assistive tech -->\n<div aria-live=\"sometimes\">Loading…</div>\n<button aria-expanded=\"yes\">Menu</button>",
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

            'is-on-https' => [
                'why' => 'HTTP pages cannot use many modern browser features (Service Workers, HTTP/2 push, geolocation). Browsers mark them as "Not Secure" — destroying user trust. All production Laravel apps should be served over HTTPS.',
                'docs' => [
                    ['label' => 'Lighthouse: Use HTTPS', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/is-on-https'],
                    ['label' => "Let's Encrypt: Free SSL certificates", 'url' => 'https://letsencrypt.org/'],
                ],
                'good' => "# nginx with Let's Encrypt\nserver {\n    listen 443 ssl;\n    ssl_certificate /etc/letsencrypt/live/example.com/fullchain.pem;\n    ssl_certificate_key /etc/letsencrypt/live/example.com/privkey.pem;\n}\n\n# Force HTTPS in Laravel\n// AppServiceProvider::boot()\n\\URL::forceScheme('https');",
                'bad' => "# Serving over plain HTTP — browser shows 'Not Secure'\nserver {\n    listen 80;\n    # no SSL block\n}",
            ],

            'geolocation-on-start' => [
                'why' => 'Requesting geolocation permission immediately on page load (before any user interaction) is considered intrusive. Browsers show a permission prompt that most users dismiss — then the feature is blocked forever. Request only after a clear user action.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoids requesting geolocation on page load', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/geolocation-on-start'],
                    ['label' => 'MDN: Geolocation API', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API'],
                ],
                'good' => "// Request on explicit user action\ndocument.getElementById('find-me').addEventListener('click', () => {\n    navigator.geolocation.getCurrentPosition(success, error);\n});",
                'bad' => "// Requesting immediately on DOMContentLoaded — intrusive\ndocument.addEventListener('DOMContentLoaded', () => {\n    navigator.geolocation.getCurrentPosition(success, error);\n});",
            ],

            'notification-on-start' => [
                'why' => 'Requesting push notification permission immediately on page load results in 90%+ rejection rates and trains users to deny all notifications. Request after demonstrating value — e.g. after a successful action.',
                'docs' => [
                    ['label' => 'Lighthouse: Avoids requesting notification permission on page load', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/notification-on-start'],
                    ['label' => 'web.dev: Permission UX', 'url' => 'https://web.dev/articles/permission-ux'],
                ],
                'good' => "// Request after user opts in\ndocument.getElementById('enable-notifications').addEventListener('click', () => {\n    Notification.requestPermission();\n});",
                'bad' => "// Requesting on page load — dismissed by almost all users\nwindow.addEventListener('load', () => Notification.requestPermission());",
            ],

            'password-inputs-can-be-pasted-into' => [
                'why' => 'Blocking paste on password fields prevents password manager use, forces manual typing of complex passwords, and drives users toward weaker passwords. Never prevent paste — it reduces security, not increases it.',
                'docs' => [
                    ['label' => 'Lighthouse: Allows users to paste into password fields', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/password-inputs-can-be-pasted-into'],
                ],
                'good' => "<!-- Standard password input — paste is allowed by default -->\n<input type=\"password\" name=\"password\" autocomplete=\"current-password\">",
                'bad' => "<!-- Never do this -->\n<input type=\"password\" onpaste=\"return false;\">",
            ],

            'image-aspect-ratio' => [
                'why' => 'When the CSS display size has a different aspect ratio than the intrinsic image size, the image appears distorted. Setting `width` and `height` attributes and using `aspect-ratio` CSS prevents this.',
                'docs' => [
                    ['label' => 'Lighthouse: Image display dimensions match natural size', 'url' => 'https://developer.chrome.com/docs/lighthouse/best-practices/image-aspect-ratio'],
                ],
                'good' => "<!-- Intrinsic 16:9 image displayed at 16:9 -->\n<img src=\"video-thumb.jpg\" width=\"800\" height=\"450\" alt=\"Video\">\n\n/* CSS */\nimg { aspect-ratio: 16/9; width: 100%; height: auto; }",
                'bad' => "<!-- 16:9 image displayed in a square container — distorted -->\n<img src=\"video-thumb.jpg\" style=\"width:200px; height:200px;\">",
            ],

            'image-size-responsive' => [
                'why' => 'Serving a 2000px image to a 400px mobile device wastes 4-5× bandwidth. Use `srcset` and `sizes` to let the browser choose the best-fit image for the current viewport.',
                'docs' => [
                    ['label' => 'Lighthouse: Image size responsive', 'url' => 'https://developer.chrome.com/docs/lighthouse/performance/uses-responsive-images'],
                    ['label' => 'MDN: Responsive images guide', 'url' => 'https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Structuring_content/Responsive_images'],
                ],
                'good' => "<img\n  src=\"/images/hero-800w.jpg\"\n  srcset=\"/images/hero-400w.jpg 400w, /images/hero-800w.jpg 800w, /images/hero-1600w.jpg 1600w\"\n  sizes=\"(max-width: 600px) 400px, (max-width: 1200px) 800px, 1600px\"\n  alt=\"Hero image\">",
                'bad' => "<!-- Always serving the full-resolution image -->\n<img src=\"/images/hero-4000w.jpg\" alt=\"Hero\">",
                'impact' => 'Typical savings: 60-80% bytes on mobile viewports',
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

            'hreflang' => [
                'why' => 'For multilingual sites, `hreflang` tells search engines which page to show for which language and region. Incorrect or missing `hreflang` leads to the wrong language version appearing in search results.',
                'docs' => [
                    ['label' => 'Lighthouse: Document has a valid hreflang', 'url' => 'https://developer.chrome.com/docs/lighthouse/seo/hreflang'],
                    ['label' => 'Google Search: Localized versions', 'url' => 'https://developers.google.com/search/docs/specialty/international/localized-versions'],
                ],
                'good' => "<link rel=\"alternate\" hreflang=\"en\" href=\"https://example.com/en/page\">\n<link rel=\"alternate\" hreflang=\"fr\" href=\"https://example.com/fr/page\">\n<link rel=\"alternate\" hreflang=\"x-default\" href=\"https://example.com/page\">",
                'bad' => "<!-- Missing hreflang — search engines guess the target language -->\n<!-- Or invalid language code -->\n<link rel=\"alternate\" hreflang=\"english\" href=\"...\"><!-- invalid BCP47 -->",
            ],

            'canonical' => [
                'why' => 'Duplicate content across URLs (with/without trailing slash, www vs non-www, UTM parameters) confuses search engines and dilutes PageRank. A canonical tag tells Google which is the authoritative URL.',
                'docs' => [
                    ['label' => 'Lighthouse: Document has a canonical link', 'url' => 'https://developer.chrome.com/docs/lighthouse/seo/canonical'],
                    ['label' => 'Google Search: Canonical URL', 'url' => 'https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls'],
                ],
                'good' => "<!-- In Blade layout -->\n<link rel=\"canonical\" href=\"{{ url()->current() }}\">\n\n<!-- Or with Laravel SEO packages -->\n// In AppServiceProvider: URL::forceRootUrl(config('app.url'));",
                'bad' => "<!-- No canonical — search engines may index many duplicate versions -->\n<!-- /page, /page/, /page?utm_source=twitter all treated as separate URLs -->",
            ],

            'robots-txt' => [
                'why' => 'An invalid or missing robots.txt can prevent search engines from crawling your site, or allow crawling of sensitive paths. Lighthouse flags when robots.txt is unreachable or syntactically invalid.',
                'docs' => [
                    ['label' => 'Lighthouse: robots.txt is valid', 'url' => 'https://developer.chrome.com/docs/lighthouse/seo/invalid-robots-txt'],
                    ['label' => 'Google Search: robots.txt specification', 'url' => 'https://developers.google.com/search/docs/crawling-indexing/robots/create-robots-txt'],
                ],
                'good' => "# public/robots.txt — sensible defaults\nUser-agent: *\nDisallow: /admin/\nDisallow: /api/\nSitemap: https://example.com/sitemap.xml",
                'bad' => "# Accidentally blocking all crawlers\nUser-agent: *\nDisallow: /\n\n# Or missing robots.txt — crawlers get no guidance",
            ],

            'tap-targets' => [
                'why' => 'Touch targets smaller than 48×48 CSS pixels are hard to tap accurately on mobile. Users mis-tap neighbouring elements, triggering unintended actions. Google\'s mobile-friendliness criteria require adequate tap target sizes.',
                'docs' => [
                    ['label' => 'Lighthouse: Tap targets are sized appropriately', 'url' => 'https://developer.chrome.com/docs/lighthouse/seo/tap-targets'],
                    ['label' => 'web.dev: Accessible tap targets', 'url' => 'https://web.dev/articles/accessible-tap-targets'],
                ],
                'good' => "/* Minimum 48×48px tap target with padding trick */\n.nav-link {\n    display: inline-flex;\n    align-items: center;\n    min-height: 48px;\n    padding: 0 12px;\n}\n\n/* Or use Tailwind */\n<a class=\"py-3 px-4 min-h-12\">...</a>",
                'bad' => "/* Too small — hard to tap on mobile */\n.icon-btn { width: 20px; height: 20px; }",
                'impact' => 'Improves Google mobile-friendliness score and reduces accidental tap errors',
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
