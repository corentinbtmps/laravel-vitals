<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use LaravelVitals\Storage\ReportRepository;

it('persists raw Lighthouse JSON to the configured disk under storage/path', function (): void {
    Storage::fake('vitals');
    config()->set('vitals.storage', ['disk' => 'vitals', 'path' => 'reports']);

    $repo = new ReportRepository();

    $path = $repo->store('audit-uuid-1', '{"hello":"world"}');

    expect($path)->toBe('reports/audit-uuid-1.json');
    Storage::disk('vitals')->assertExists($path);
    expect(Storage::disk('vitals')->get($path))->toBe('{"hello":"world"}');
});

it('reads back a stored report', function (): void {
    Storage::fake('vitals');
    config()->set('vitals.storage', ['disk' => 'vitals', 'path' => 'reports']);

    $repo = new ReportRepository();
    $repo->store('audit-uuid-1', '{"hello":"world"}');

    expect($repo->read('reports/audit-uuid-1.json'))->toBe('{"hello":"world"}');
});

it('throws when reading a missing report', function (): void {
    Storage::fake('vitals');
    config()->set('vitals.storage', ['disk' => 'vitals', 'path' => 'reports']);

    $repo = new ReportRepository();

    expect(fn () => $repo->read('reports/missing.json'))->toThrow(RuntimeException::class);
});
