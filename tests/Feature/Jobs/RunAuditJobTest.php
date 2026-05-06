<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;
use LaravelVitals\Jobs\RunAuditJob;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Notifications\Channels\VitalsNotifier;
use LaravelVitals\Recommendations\RecommendationBuilder;
use LaravelVitals\Storage\ReportRepository;

beforeEach(function (): void {
    Storage::fake('vitals');
    config()->set('vitals.storage', ['disk' => 'vitals', 'path' => 'reports']);
    $this->app->bind(LighthouseDriver::class, fn (): \LaravelVitals\Drivers\Stubs\StubLighthouseDriver => new StubLighthouseDriver());
});

it('runs the audit, persists raw JSON, and updates the audit row', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'pending',
    ]);

    (new RunAuditJob($audit->id))->handle(
        app(LighthouseDriver::class),
        app(ReportRepository::class),
        app(RecommendationBuilder::class),
        app(VitalsNotifier::class),
    );

    $audit->refresh();

    expect($audit->status)->toBe('completed')
        ->and($audit->score_performance)->toBe(95)
        ->and((float) $audit->lcp_ms)->toBe(1500.0)
        ->and($audit->report_path)->toBeString()
        ->and($audit->started_at)->not->toBeNull()
        ->and($audit->completed_at)->not->toBeNull();

    Storage::disk('vitals')->assertExists($audit->report_path);
});

it('marks the audit failed when the driver throws', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'pending',
    ]);

    $boomDriver = new class implements LighthouseDriver {
        public function audit($url, $opts): \LaravelVitals\Support\LighthouseReport
        {
            throw new \LaravelVitals\Support\AuditException('boom', driver: 'stub');
        }
        public function isAvailable(): bool { return true; }
    };

    expect(fn () => (new RunAuditJob($audit->id))->handle($boomDriver, app(ReportRepository::class), app(RecommendationBuilder::class), app(VitalsNotifier::class)))
        ->toThrow(\LaravelVitals\Support\AuditException::class);

    $audit->refresh();

    expect($audit->status)->toBe('failed')
        ->and($audit->error)->toContain('boom');
});

it('injects the X-Vitals-Audit-Id header into AuditOptions passed to the driver', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'pending',
    ]);

    $captured = null;
    $spy = new class($captured) implements LighthouseDriver {
        public ?\LaravelVitals\Support\AuditOptions $captured = null;

        public function __construct(?\LaravelVitals\Support\AuditOptions &$captured)
        {
            $this->captured = &$captured;
        }

        public function audit($url, \LaravelVitals\Support\AuditOptions $opts): \LaravelVitals\Support\LighthouseReport
        {
            $this->captured = $opts;
            return (new StubLighthouseDriver())->audit($url, $opts);
        }
        public function isAvailable(): bool { return true; }
    };

    (new RunAuditJob($audit->id))->handle($spy, app(ReportRepository::class), app(RecommendationBuilder::class), app(VitalsNotifier::class));

    expect($spy->captured)->not->toBeNull()
        ->and($spy->captured->extraHeaders)->toHaveKey('X-Vitals-Audit-Id')
        ->and($spy->captured->extraHeaders['X-Vitals-Audit-Id'])->toContain($audit->id);
});
