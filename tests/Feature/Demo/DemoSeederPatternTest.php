<?php

declare(strict_types=1);

use LaravelVitals\Demo\DemoSeeder;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;

it('seeds 4 urls with demo flag', function (): void {
    (new DemoSeeder())->seed();

    $urls = Url::where('is_demo', true)->get();

    expect($urls)->toHaveCount(4);
    expect($urls->pluck('label')->sort()->values()->all())
        ->toBe(['blog', 'dashboard', 'home', 'product']);
});

it('seeds 30 days of audits per url and device', function (): void {
    (new DemoSeeder())->seed();

    // 4 URLs × 30 days × 2 devices = 240 audits
    $count = Audit::where('is_demo', true)->count();

    expect($count)->toBe(240);
});

it('is idempotent — running twice does not double the data', function (): void {
    $seeder = new DemoSeeder();
    $seeder->seed();
    $seeder->seed();

    $count = Audit::where('is_demo', true)->count();

    // Should still be 240, not 480.
    expect($count)->toBe(240);
});
