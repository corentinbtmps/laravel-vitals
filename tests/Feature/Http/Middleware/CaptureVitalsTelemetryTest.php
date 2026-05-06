<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use LaravelVitals\Http\Middleware\CaptureVitalsTelemetry;
use LaravelVitals\Jobs\PersistTelemetryJob;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Telemetry\SignedHeader;

beforeEach(function (): void {
    config()->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');

    Route::middleware(CaptureVitalsTelemetry::class)
        ->get('/_telemetry-test', fn (): string => 'ok')
        ->name('telemetry-test');

    Route::middleware(CaptureVitalsTelemetry::class)
        ->get('/_telemetry-test-q', function (): string {
            \LaravelVitals\Models\Url::query()->where('label', 'home')->get();
            return 'ok';
        })
        ->name('telemetry-test-q');

    Url::create(['label' => 'home', 'path' => '/']);
});

it('does NOT dispatch a PersistTelemetryJob when no header is present and always_capture is off', function (): void {
    config()->set('vitals.telemetry.always_capture', false);

    Bus::fake();

    $this->get('/_telemetry-test')->assertOk();

    Bus::assertNothingDispatched();
});

it('dispatches PersistTelemetryJob when a valid signed header is present', function (): void {
    config()->set('vitals.telemetry.always_capture', false);

    $url = Url::where('label', 'home')->first();
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'pending',
    ]);

    Bus::fake();

    $this->withHeader('X-Vitals-Audit-Id', SignedHeader::sign($audit->id))
        ->get('/_telemetry-test-q')
        ->assertOk();

    Bus::assertDispatched(PersistTelemetryJob::class, fn(PersistTelemetryJob $job): bool => $job->snapshot->auditId === $audit->id
        && $job->snapshot->httpStatus === 200
        && $job->snapshot->queriesCount >= 1);
});

it('does NOT dispatch when the header signature is invalid', function (): void {
    config()->set('vitals.telemetry.always_capture', false);

    Bus::fake();

    $this->withHeader('X-Vitals-Audit-Id', 'fake-id.bad-sig')
        ->get('/_telemetry-test')
        ->assertOk();

    Bus::assertNothingDispatched();
});

it('dispatches a sampled telemetry job when always_capture is on and dice rolls in', function (): void {
    config()->set('vitals.telemetry.always_capture', true);
    config()->set('vitals.telemetry.sample_rate', 1.0);

    Bus::fake();

    $this->get('/_telemetry-test')->assertOk();

    Bus::assertDispatched(PersistTelemetryJob::class, fn(PersistTelemetryJob $job): bool => $job->snapshot->auditId === null
        && $job->snapshot->sampledRequest);
});
