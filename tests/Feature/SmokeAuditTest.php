<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;
use LaravelVitals\Models\Audit;

it('runs a full happy-path audit end-to-end via the artisan command', function (): void {
    Storage::fake('vitals');
    config()->set('vitals.storage', ['disk' => 'vitals', 'path' => 'reports']);
    $this->app->bind(LighthouseDriver::class, fn () => new StubLighthouseDriver());

    config()->set('vitals.urls', ['home' => '/']);

    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true])
        ->assertSuccessful();

    $audit = Audit::first();

    expect($audit)->not->toBeNull()
        ->and($audit->status)->toBe('completed')
        ->and($audit->score_performance)->toBe(95)
        ->and($audit->report_path)->toBeString();

    Storage::disk('vitals')->assertExists($audit->report_path);
    expect(Storage::disk('vitals')->get($audit->report_path))
        ->toContain('"lighthouseVersion"');
});
