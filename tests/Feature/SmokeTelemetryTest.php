<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Device;
use LaravelVitals\Http\Middleware\CaptureVitalsTelemetry;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Url;
use LaravelVitals\Telemetry\SignedHeader;
use Workbench\App\Models\SampleRecord;

beforeEach(function (): void {
    Storage::fake('vitals');
    config()->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
    config()->set('vitals.telemetry.always_capture', false);

    // Run the workbench migration so /vitals-test has its sample_records table.
    $this->loadMigrationsFrom(__DIR__ . '/../../workbench/database/migrations');

    // Register the workbench /vitals-test route (mirrors workbench/routes/web.php).
    Route::middleware(['web', CaptureVitalsTelemetry::class])
        ->get('/vitals-test', function (): \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response {
            SampleRecord::create(['name' => 'a']);
            SampleRecord::create(['name' => 'b']);
            SampleRecord::create(['name' => 'c']);
            $records = SampleRecord::query()->orderBy('id')->get();
            return response('ok');
        })->name('vitals-test');
});

it('captures a request from /vitals-test into vitals_backend_telemetry', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/vitals-test']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => Device::Mobile,
        'status' => AuditStatus::Pending,
    ]);

    $this->withHeader('X-Vitals-Audit-Id', SignedHeader::sign($audit->id))
        ->get('/vitals-test')
        ->assertOk();

    // The PersistTelemetryJob is dispatched with afterResponse() — flush it.
    $this->app->terminate();

    $telemetry = BackendTelemetry::where('audit_id', $audit->id)->first();

    expect($telemetry)->not->toBeNull()
        ->and((int) $telemetry->queries_count)->toBeGreaterThan(0)
        ->and($telemetry->http_status)->toBe(200);
});
