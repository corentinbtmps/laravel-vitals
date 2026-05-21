<?php

declare(strict_types=1);

namespace LaravelVitals\Demo;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Device;
use LaravelVitals\Enums\Severity;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\RumEvent;
use LaravelVitals\Models\Url;

/**
 * Generates fictional but realistic Vitals data for demos and screenshots.
 *
 * Enhanced (alpha.53): 4 URLs × 30 days × 2 devices = 240 audits with realistic
 * patterns including weekend traffic dips, midweek regressions, and occasional spikes.
 * ~500 RUM events per URL per day for 30 days (significant but capped per run).
 *
 * Idempotent — running vitals:demo twice truncates existing demo data first.
 */
final class DemoSeeder
{
    /** @var array<string, array{path: string, profile: string}> */
    private const FIXTURES = [
        'home'      => ['path' => '/',          'profile' => 'stable_high'],
        'product'   => ['path' => '/products',  'profile' => 'improving'],
        'blog'      => ['path' => '/blog',      'profile' => 'bad'],
        'dashboard' => ['path' => '/dashboard', 'profile' => 'regression'],
    ];

    /** @var array<int, string> Common recommendations distributed across audits. */
    private const COMMON_RECOMMENDATIONS = [
        'unused-javascript',
        'n-plus-one-detected',
        'render-blocking-resources',
        'uses-text-compression',
        'uses-optimized-images',
    ];

    public function seed(): void
    {
        // Idempotent: clear existing demo data first.
        // RUM events are tied to URL paths — delete those matching our demo paths.
        $demoPaths = array_map(
            fn (array $fix): string => 'https://example.test' . $fix['path'],
            self::FIXTURES,
        );
        RumEvent::query()->whereIn('url', $demoPaths)->delete();
        Recommendation::query()->where('is_demo', true)->delete();
        BackendTelemetry::query()->where('is_demo', true)->delete();
        Audit::query()->where('is_demo', true)->delete();
        Url::query()->where('is_demo', true)->delete();

        mt_srand(20260506);

        foreach (self::FIXTURES as $label => $fix) {
            $url = Url::create([
                'label'   => $label,
                'path'    => $fix['path'],
                'device'  => Device::Both,
                'enabled' => true,
                'is_demo' => true,
            ]);

            for ($d = 29; $d >= 0; $d--) {
                $when = now()->subDays($d)->setTime(12, mt_rand(0, 59));
                $isWeekend = in_array($when->dayOfWeek, [0, 6], true);

                foreach (['mobile', 'desktop'] as $device) {
                    $this->seedOne($url, $device, $when, $fix['profile'], $isWeekend, $d);
                }

                // Seed RUM events — ~50 per URL per day (scaled down for performance)
                $rumCount = $isWeekend ? mt_rand(20, 35) : mt_rand(40, 60);
                $this->seedRumEvents($url, $when, $rumCount);
            }
        }
    }

    private function seedOne(Url $url, string $device, Carbon $when, string $profile, bool $isWeekend = false, int $daysAgoInt = 0): void
    {
        [$perf, $lcp, $cls, $inp, $ttfb] = $this->metricsForProfile($profile, $when);

        // Weekend traffic dip: performance improves slightly (less load)
        if ($isWeekend) {
            $perf = min(100, $perf + mt_rand(2, 5));
            $ttfb = max(80.0, $ttfb - mt_rand(20, 60));
        }

        // Occasional spike (random 1-in-10 days)
        if (mt_rand(1, 10) === 1) {
            $perf = max(40, $perf - mt_rand(10, 20));
            $lcp  = $lcp + mt_rand(500, 1500);
        }

        if ($device === 'desktop') {
            $perf = min(100, $perf + mt_rand(5, 10));
            $lcp = max(500.0, $lcp - 400);
        }

        $audit = Audit::create([
            'id'                => Str::uuid()->toString(),
            'url_id'            => $url->id,
            'driver'            => 'demo',
            'device'            => $device,
            'status'            => AuditStatus::Completed,
            'score_performance' => $perf,
            'score_accessibility'  => mt_rand(85, 98),
            'score_best_practices' => mt_rand(85, 100),
            'score_seo'         => mt_rand(90, 100),
            'lcp_ms'            => $lcp,
            'cls'               => $cls,
            'inp_ms'            => $inp,
            'ttfb_ms'           => $ttfb,
            'fcp_ms'            => max(200.0, $lcp - mt_rand(200, 500)),
            'si_ms'             => $lcp + mt_rand(100, 400),
            'tbt_ms'            => mt_rand(20, 200),
            'started_at'        => $when,
            'completed_at'      => $when->copy()->addSeconds(mt_rand(30, 90)),
            'is_demo'           => true,
            'details'           => $this->fakeDetails($profile, $perf),
        ]);

        // Memory peaks vary 20-80 MB
        $memoryMb = mt_rand(20, 80);

        BackendTelemetry::create([
            'audit_id'           => $audit->id,
            'sampled_request'    => false,
            'route_name'         => $url->label,
            'http_status'        => 200,
            'duration_ms'        => $ttfb,
            'memory_peak_kb'     => $memoryMb * 1024,
            'peak_memory_bytes'  => $memoryMb * 1024 * 1024,
            'queries_count'      => $profile === 'bad' ? mt_rand(80, 120) : mt_rand(8, 30),
            'queries_time_ms'    => mt_rand(50, 300),
            'queries_unique'     => $profile === 'bad' ? 4 : mt_rand(6, 25),
            'n_plus_one_suspect' => $profile === 'bad',
            'views_rendered'     => mt_rand(2, 8),
            'views_time_ms'      => mt_rand(5, 40),
            'jobs_dispatched'    => mt_rand(0, 3),
            'events_fired'       => mt_rand(5, 30),
            'cache_hits'         => mt_rand(10, 60),
            'cache_misses'       => mt_rand(0, 8),
            'is_demo'            => true,
        ]);

        if ($profile === 'bad' || mt_rand(0, 1) === 1) {
            Recommendation::create([
                'audit_id'         => $audit->id,
                'source'           => 'lighthouse',
                'audit_key'        => 'unused-javascript',
                'category'         => 'performance',
                'severity'         => Severity::Warning,
                'title_key'        => 'vitals::vitals.recommendations.unused-javascript.title',
                'description_key'  => 'vitals::vitals.recommendations.unused-javascript.description',
                'translation_params' => ['size' => '180 KB'],
                'metrics'          => ['wasted_bytes' => 180_000, 'total_bytes' => 320_000],
                'code_references'  => [
                    [
                        'file' => 'resources/views/' . $url->label . '.blade.php',
                        'line_start' => 12,
                        'line_end' => 12,
                        'snippet' => '<script src="/build/assets/app-abc.js"></script>',
                        'hint' => 'Use @vite([...]) instead.',
                    ],
                ],
                'detail_items'     => [
                    ['url' => config('app.url', 'https://example.test') . '/build/assets/app-abc.js',    'wasted_bytes' => 96_000, 'total_bytes' => 180_000],
                    ['url' => config('app.url', 'https://example.test') . '/build/assets/vendor.js',     'wasted_bytes' => 62_000, 'total_bytes' => 145_000],
                    ['url' => config('app.url', 'https://example.test') . '/build/assets/charts.js',     'wasted_bytes' => 22_000, 'total_bytes' => 38_000],
                ],
                'is_demo'          => true,
            ]);
        }

        if ($profile === 'bad') {
            Recommendation::create([
                'audit_id'         => $audit->id,
                'source'           => 'backend',
                'audit_key'        => 'n-plus-one-detected',
                'category'         => 'performance',
                'severity'         => Severity::Warning,
                'title_key'        => 'vitals::vitals.recommendations.n-plus-one-detected.title',
                'description_key'  => 'vitals::vitals.recommendations.n-plus-one-detected.description',
                'translation_params' => [
                    'count' => 87,
                    'top_patterns' => [
                        ['sql' => 'SELECT * FROM posts WHERE user_id = ?', 'occurrences' => 24, 'caller' => 'app/Http/Controllers/' . ucfirst($url->label) . 'Controller.php:42'],
                        ['sql' => 'SELECT * FROM comments WHERE post_id = ?', 'occurrences' => 18, 'caller' => 'app/View/Components/PostCard.php:18'],
                    ],
                ],
                'metrics'          => ['queries_count' => 87, 'queries_unique' => 4],
                'code_references'  => [],
                'is_demo'          => true,
            ]);
        }

        // Distribute additional common recommendations
        if (mt_rand(0, 3) === 0) {
            $extraKey = self::COMMON_RECOMMENDATIONS[mt_rand(2, 4)];

            $extraDetailItems = match ($extraKey) {
                'uses-optimized-images' => [
                    ['url' => config('app.url', 'https://example.test') . '/images/hero.jpg',    'wasted_bytes' => 190_000, 'total_bytes' => 380_000],
                    ['url' => config('app.url', 'https://example.test') . '/images/banner.png', 'wasted_bytes' => 87_000,  'total_bytes' => 220_000],
                    ['url' => config('app.url', 'https://example.test') . '/images/avatar.jpg', 'wasted_bytes' => 9_000,   'total_bytes' => 45_000],
                ],
                'uses-text-compression' => [
                    ['url' => config('app.url', 'https://example.test') . '/build/assets/app.css', 'wasted_bytes' => 47_000, 'total_bytes' => 62_000],
                    ['url' => config('app.url', 'https://example.test') . '/api/articles.json',    'wasted_bytes' => 28_000, 'total_bytes' => 38_000],
                ],
                'render-blocking-resources' => [
                    ['url' => config('app.url', 'https://example.test') . '/build/assets/fonts.css',     'wasted_ms' => 320, 'total_bytes' => 12_000],
                    ['url' => config('app.url', 'https://example.test') . '/build/assets/analytics.js', 'wasted_ms' => 180, 'total_bytes' => 45_000],
                ],
                default => null,
            };

            Recommendation::create([
                'audit_id'         => $audit->id,
                'source'           => 'lighthouse',
                'audit_key'        => $extraKey,
                'category'         => 'performance',
                'severity'         => Severity::Info,
                'title_key'        => 'vitals::vitals.recommendations.' . $extraKey . '.title',
                'description_key'  => 'vitals::vitals.recommendations.' . $extraKey . '.description',
                'translation_params' => [],
                'metrics'          => [],
                'code_references'  => [],
                'detail_items'     => $extraDetailItems,
                'is_demo'          => true,
            ]);
        }

        // Distribute SEO check failures so /vitals/seo has demo data.
        $seoFindings = $profile === 'bad'
            ? [
                ['key' => 'meta-description', 'severity' => Severity::Warning, 'actual' => 'missing',       'expected' => 'present (≤ 160 chars)', 'weight' => 9],
                ['key' => 'image-alt',        'severity' => Severity::Warning, 'actual' => '3 missing alt', 'expected' => 'all images have alt',  'weight' => 7],
                ['key' => 'h1',               'severity' => Severity::Critical,'actual' => '2 H1 tags',     'expected' => 'exactly 1 H1',         'weight' => 8],
            ]
            : (mt_rand(0, 1) === 0
                ? [['key' => 'meta-description', 'severity' => Severity::Warning, 'actual' => '187 chars', 'expected' => '≤ 160 chars', 'weight' => 9]]
                : []);

        foreach ($seoFindings as $finding) {
            Recommendation::create([
                'audit_id'           => $audit->id,
                'source'             => 'seo',
                'audit_key'          => 'seo-' . $finding['key'],
                'category'           => 'seo',
                'severity'           => $finding['severity'],
                'title_key'          => 'vitals::vitals.seo.checks.' . $finding['key'] . '.title',
                'description_key'    => 'vitals::vitals.seo.checks.' . $finding['key'] . '.description',
                'translation_params' => ['actual' => $finding['actual'], 'expected' => $finding['expected']],
                'metrics'            => ['weight' => $finding['weight']],
                'code_references'    => [],
                'detail_items'       => [],
                'is_demo'            => true,
            ]);
        }
    }

    /**
     * Seed a batch of synthetic RUM events for a URL on a given day.
     */
    private function seedRumEvents(Url $url, Carbon $when, int $count): void
    {
        $metrics = ['LCP', 'FID', 'CLS', 'INP', 'TTFB', 'FCP'];
        $ratings = ['good', 'needs-improvement', 'poor'];

        for ($i = 0; $i < $count; $i++) {
            $metric = $metrics[mt_rand(0, count($metrics) - 1)];
            $minuteOffset = mt_rand(0, 1439);

            $value = match ($metric) {
                'LCP'  => mt_rand(800, 5000) / 1.0,
                'CLS'  => mt_rand(0, 50) / 100.0,
                'INP'  => mt_rand(50, 600) / 1.0,
                'TTFB' => mt_rand(100, 2000) / 1.0,
                'FCP'  => mt_rand(300, 3000) / 1.0,
                default => mt_rand(100, 1000) / 1.0,
            };

            $rating = match (true) {
                $metric === 'LCP' && $value <= 2500 => 'good',
                $metric === 'LCP' && $value <= 4000 => 'needs-improvement',
                $metric === 'CLS' && $value <= 0.1  => 'good',
                $metric === 'CLS' && $value <= 0.25 => 'needs-improvement',
                default => $ratings[mt_rand(0, 2)],
            };

            RumEvent::create([
                'url'             => 'https://example.test' . $url->path,
                'metric'          => $metric,
                'value'           => $value,
                'rating'          => $rating,
                'device'          => mt_rand(0, 1) === 0 ? 'mobile' : 'desktop',
                'navigation_type' => 'navigate',
                'connection'      => null,
                'attribution'     => null,
                'user_agent'      => 'Mozilla/5.0 (demo)',
                'occurred_at'     => $when->copy()->addMinutes($minuteOffset),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fakeDetails(string $profile, int $perf): array
    {
        $sizeMul = $profile === 'bad' ? 2.5 : ($profile === 'improving' ? 1.5 : 1.0);

        return [
            'page_weight_bytes'       => (int) (mt_rand(800_000, 1_500_000) * $sizeMul),
            'request_count'           => mt_rand(20, 60) + ($profile === 'bad' ? 30 : 0),
            'dom_size'                => mt_rand(800, 1200) + ($profile === 'bad' ? 800 : 0),
            'render_blocking_time_ms' => $profile === 'bad' ? mt_rand(400, 800) : mt_rand(100, 300),
            'lcp_element'             => [
                'snippet'  => '<img src="/images/hero.jpg" loading="eager">',
                'selector' => 'main > section.hero > img',
            ],
            'resource_summary' => [
                ['type' => 'script',     'count' => mt_rand(6, 14),  'bytes' => mt_rand(300_000, 800_000)],
                ['type' => 'stylesheet', 'count' => mt_rand(2, 5),   'bytes' => mt_rand(60_000, 180_000)],
                ['type' => 'image',      'count' => mt_rand(8, 24),  'bytes' => mt_rand(200_000, 800_000)],
                ['type' => 'font',       'count' => mt_rand(1, 4),   'bytes' => mt_rand(40_000, 120_000)],
                ['type' => 'document',   'count' => 1,               'bytes' => mt_rand(10_000, 40_000)],
            ],
            'third_parties' => $profile === 'bad' ? [
                ['entity' => 'Google Tag Manager', 'transfer_bytes' => 56000, 'blocking_ms' => 320.0, 'main_thread_ms' => 410.0],
                ['entity' => 'Facebook Pixel',     'transfer_bytes' => 38000, 'blocking_ms' => 180.0, 'main_thread_ms' => 220.0],
                ['entity' => 'Google Analytics',   'transfer_bytes' => 24000, 'blocking_ms' => 90.0,  'main_thread_ms' => 140.0],
            ] : [
                ['entity' => 'Google Analytics',   'transfer_bytes' => 24000, 'blocking_ms' => 60.0,  'main_thread_ms' => 100.0],
            ],
            'main_thread' => [
                ['category' => 'Script Evaluation', 'duration_ms' => $profile === 'bad' ? mt_rand(800, 1500) : mt_rand(200, 500)],
                ['category' => 'Style & Layout',     'duration_ms' => mt_rand(150, 400)],
                ['category' => 'Rendering',          'duration_ms' => mt_rand(80, 250)],
                ['category' => 'Parse HTML & CSS',   'duration_ms' => mt_rand(40, 120)],
            ],
            'bootup_time' => [
                ['url' => 'https://example.test/build/assets/app.js',    'total_ms' => mt_rand(200, 600)],
                ['url' => 'https://example.test/build/assets/vendor.js', 'total_ms' => mt_rand(100, 300)],
            ],
            'cache_policy' => $profile === 'bad' ? [
                ['url' => 'https://example.test/old.js',     'ttl_seconds' => 3600],
                ['url' => 'https://example.test/font.woff2', 'ttl_seconds' => 0],
            ] : [],
            'slow_requests' => [
                ['url' => 'https://example.test/build/assets/vendor.js', 'transfer_bytes' => 380_000, 'duration_ms' => $profile === 'bad' ? mt_rand(800, 1500) : mt_rand(300, 600), 'resource_type' => 'Script'],
                ['url' => 'https://example.test/api/products',           'transfer_bytes' => 25_000,  'duration_ms' => mt_rand(150, 400),  'resource_type' => 'XHR'],
            ],
            'critical_chain_depth' => $profile === 'bad' ? mt_rand(4, 7) : mt_rand(2, 3),
        ];
    }

    /**
     * @return array{0: int, 1: float, 2: float, 3: float, 4: float}
     */
    private function metricsForProfile(string $profile, Carbon $when): array
    {
        $daysAgo = max(0, (int) round($when->diffInDays(now())));

        return match ($profile) {
            'stable_high' => [
                mt_rand(88, 95),
                (float) mt_rand(1300, 1800),
                round(mt_rand(1, 5) / 100, 2),
                (float) mt_rand(80, 150),
                (float) mt_rand(150, 300),
            ],
            'improving' => [
                70 + (int) round((29 - $daysAgo) * 0.65),
                (float) max(1500, mt_rand(2500, 3500) - ((29 - $daysAgo) * 40)),
                round(mt_rand(5, 12) / 100, 2),
                (float) mt_rand(150, 280),
                (float) mt_rand(300, 600),
            ],
            'bad' => [
                mt_rand(60, 72),
                (float) mt_rand(4200, 5500),
                round(mt_rand(15, 28) / 100, 2),
                (float) mt_rand(280, 450),
                (float) mt_rand(800, 1500),
            ],
            'regression' => [
                $daysAgo <= 3 ? mt_rand(72, 80) : mt_rand(92, 96),
                $daysAgo <= 3 ? (float) mt_rand(3000, 3800) : (float) mt_rand(1400, 1800),
                round(mt_rand(2, 6) / 100, 2),
                (float) mt_rand(100, 200),
                (float) mt_rand(150, 350),
            ],
            default => [80, 1500.0, 0.05, 100.0, 200.0],
        };
    }
}
