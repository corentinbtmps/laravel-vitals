<?php

declare(strict_types=1);

it('runs vitals:doctor and prints all expected check sections', function (): void {
    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('Database')
        ->expectsOutputToContain('Lighthouse drivers')
        ->expectsOutputToContain('Storage')
        ->expectsOutputToContain('Notifications')
        ->expectsOutputToContain('Telemetry sources')
        ->assertSuccessful();
});

it('returns non-zero when migrations are not run', function (): void {
    \Illuminate\Support\Facades\Schema::drop('vitals_audits');

    $exit = $this->artisan('vitals:doctor')->run();

    expect($exit)->toBeGreaterThan(0);
});
