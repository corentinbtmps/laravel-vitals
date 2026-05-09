<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelVitals\Http\Middleware\CaptureVitalsTelemetry;
use LaravelVitals\Jobs\PersistTelemetryJob;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Url;
use LaravelVitals\Telemetry\SignedHeader;
use LaravelVitals\Models\Audit;
use Illuminate\Support\Str;

beforeEach(function (): void {
    config()->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');

    Route::middleware(CaptureVitalsTelemetry::class)
        ->get('/_memory-test', fn (): string => 'ok')
        ->name('memory-test');

    Url::create(['label' => 'home', 'path' => '/']);
});

it('stores peak_memory_bytes on telemetry record', function (): void {
    config()->set('vitals.telemetry.always_capture', true);
    config()->set('vitals.telemetry.sample_rate', 1.0);

    $this->get('/_memory-test')->assertOk();

    $record = BackendTelemetry::query()->first();
    expect($record)->not->toBeNull();
    expect($record->peak_memory_bytes)->toBeInt()->toBeGreaterThan(0);
});

it('peak_memory_bytes in snapshot is non-zero', function (): void {
    config()->set('vitals.telemetry.always_capture', true);
    config()->set('vitals.telemetry.sample_rate', 1.0);

    $url   = Url::where('label', 'home')->first();
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'pending',
    ]);

    \Illuminate\Support\Facades\Bus::fake();

    $this->withHeader('X-Vitals-Audit-Id', SignedHeader::sign($audit->id))
        ->get('/_memory-test')
        ->assertOk();

    \Illuminate\Support\Facades\Bus::assertDispatched(
        PersistTelemetryJob::class,
        fn (PersistTelemetryJob $job): bool => $job->snapshot->peakMemoryBytes !== null
            && $job->snapshot->peakMemoryBytes > 0,
    );
});

it('peak_memory_bytes matches memory_peak_kb × 1024 approximately', function (): void {
    config()->set('vitals.telemetry.always_capture', true);
    config()->set('vitals.telemetry.sample_rate', 1.0);

    $this->get('/_memory-test')->assertOk();

    $record = BackendTelemetry::query()->first();
    expect($record)->not->toBeNull();

    // peak_memory_bytes / 1024 should be within a few KB of memory_peak_kb
    $derivedKb = (int) round($record->peak_memory_bytes / 1024);
    expect(abs($derivedKb - $record->memory_peak_kb))->toBeLessThanOrEqual(2);
});
