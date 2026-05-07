<?php

declare(strict_types=1);

namespace LaravelVitals\Demo;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

/**
 * Generates fictional but realistic Vitals data for demos and screenshots.
 * 4 URLs × 14 days × 2 devices ≈ 112 audits with realistic per-URL trends.
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

    public function seed(): void
    {
        mt_srand(20260506);

        foreach (self::FIXTURES as $label => $fix) {
            $url = Url::updateOrCreate(
                ['label' => $label],
                ['path' => $fix['path'], 'device' => 'both', 'is_demo' => true],
            );

            for ($d = 13; $d >= 0; $d--) {
                foreach (['mobile', 'desktop'] as $device) {
                    $when = now()->subDays($d)->setTime(12, mt_rand(0, 59));
                    $this->seedOne($url, $device, $when, $fix['profile']);
                }
            }
        }
    }

    private function seedOne(Url $url, string $device, Carbon $when, string $profile): void
    {
        [$perf, $lcp, $cls, $inp, $ttfb] = $this->metricsForProfile($profile, $when);

        if ($device === 'desktop') {
            $perf = min(100, $perf + mt_rand(5, 10));
            $lcp = max(500.0, $lcp - 400);
        }

        $audit = Audit::create([
            'id'                => Str::uuid()->toString(),
            'url_id'            => $url->id,
            'driver'            => 'demo',
            'device'            => $device,
            'status'            => 'completed',
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

        BackendTelemetry::create([
            'audit_id'           => $audit->id,
            'sampled_request'    => false,
            'route_name'         => $url->label,
            'http_status'        => 200,
            'duration_ms'        => $ttfb,
            'memory_peak_kb'     => mt_rand(8000, 25000),
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
                'severity'         => 'warning',
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
                'is_demo'          => true,
            ]);
        }

        if ($profile === 'bad') {
            Recommendation::create([
                'audit_id'         => $audit->id,
                'source'           => 'backend',
                'audit_key'        => 'n-plus-one-detected',
                'category'         => 'performance',
                'severity'         => 'warning',
                'title_key'        => 'vitals::vitals.recommendations.n-plus-one-detected.title',
                'description_key'  => 'vitals::vitals.recommendations.n-plus-one-detected.description',
                'translation_params' => ['count' => 87],
                'metrics'          => ['queries_count' => 87, 'queries_unique' => 4],
                'code_references'  => [],
                'is_demo'          => true,
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
                70 + (int) round((13 - $daysAgo) * 1.3),
                (float) max(1500, mt_rand(2500, 3500) - ((13 - $daysAgo) * 80)),
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
                $daysAgo <= 2 ? mt_rand(72, 80) : mt_rand(92, 96),
                $daysAgo <= 2 ? (float) mt_rand(3000, 3800) : (float) mt_rand(1400, 1800),
                round(mt_rand(2, 6) / 100, 2),
                (float) mt_rand(100, 200),
                (float) mt_rand(150, 350),
            ],
            default => [80, 1500.0, 0.05, 100.0, 200.0],
        };
    }
}
