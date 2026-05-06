<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;
use LaravelVitals\Facades\Vitals as VitalsFacade;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;

beforeEach(function (): void {
    Storage::fake('vitals');
    config()->set('vitals.storage', ['disk' => 'vitals', 'path' => 'reports']);
    $this->app->bind(LighthouseDriver::class, fn () => new StubLighthouseDriver());
});

it('Vitals::audit creates an Audit row and runs synchronously when sync is true', function (): void {
    config()->set('vitals.urls', ['home' => '/']);
    Url::create(['label' => 'home', 'path' => '/']);

    $audit = VitalsFacade::audit('home');

    expect($audit)->toBeInstanceOf(Audit::class)
        ->and($audit->refresh()->status)->toBe('completed');
});

it('Vitals::auditAll dispatches a Bus batch of RunAuditJobs', function (): void {
    config()->set('vitals.urls', ['home' => '/', 'product' => '/products/1']);

    Bus::fake();

    VitalsFacade::auditAll();

    Bus::assertBatched(function (\Illuminate\Bus\PendingBatch $batch) {
        return $batch->jobs->count() === 2;
    });
});

it('Vitals::driver overrides the resolved driver for the next call', function (): void {
    config()->set('vitals.urls', ['home' => '/']);
    Url::create(['label' => 'home', 'path' => '/']);

    $captured = false;
    $spy = new class($captured) implements LighthouseDriver {
        public bool $captured;
        public function __construct(bool &$captured) { $this->captured = &$captured; }
        public function audit($url, $options): \LaravelVitals\Support\LighthouseReport {
            $this->captured = true;
            return (new StubLighthouseDriver())->audit($url, $options);
        }
        public function isAvailable(): bool { return true; }
    };

    $this->app->bind(LighthouseDriver::class, fn () => $spy);

    VitalsFacade::audit('home');

    expect($spy->captured)->toBeTrue();
});
