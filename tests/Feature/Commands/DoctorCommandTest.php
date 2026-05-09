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

// ── Extended checks ────────────────────────────────────────────────────────────

it('reports dist assets section', function (): void {
    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('Dist assets')
        ->assertSuccessful();
});

it('reports Geist fonts as present when woff2 files exist', function (): void {
    // The dist/ directory exists in the package repo with the font files.
    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('geist')
        ->assertSuccessful();
});

it('warns when no vitals.urls are configured', function (): void {
    config()->set('vitals.urls', []);

    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('At least one URL configured');
});

it('passes when vitals.urls has entries', function (): void {
    config()->set('vitals.urls', ['home' => '/']);

    $this->artisan('vitals:doctor')
        ->assertSuccessful();
});

it('shows the Package section', function (): void {
    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('Package')
        ->assertSuccessful();
});

it('shows optional integrations section', function (): void {
    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('Optional integrations')
        ->assertSuccessful();
});

it('mentions Octane in optional integrations', function (): void {
    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('Octane')
        ->assertSuccessful();
});

it('mentions Pulse in optional integrations', function (): void {
    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('Pulse')
        ->assertSuccessful();
});

it('quiet mode returns success exit code when all checks pass', function (): void {
    config()->set('vitals.urls', ['home' => '/']);

    // --quiet (Symfony built-in) silences output; exit code 0 = all pass.
    $exit = $this->artisan('vitals:doctor', ['--quiet' => true])->run();

    expect($exit)->toBe(0);
});

it('quiet mode returns failure exit code when checks fail', function (): void {
    \Illuminate\Support\Facades\Schema::drop('vitals_audits');

    // --quiet (Symfony built-in) silences all output but the exit code still
    // reflects whether any check failed (1 = failure).
    $exit = $this->artisan('vitals:doctor', ['--quiet' => true])->run();

    expect($exit)->toBeGreaterThan(0);
});

it('fails APP_DEBUG check in production', function (): void {
    // Simulate production environment with debug on.
    $app = app();
    $app->detectEnvironment(fn (): string => 'production');
    config()->set('app.env', 'production');
    config()->set('app.debug', true);

    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('APP_DEBUG');
});

it('warns about file cache driver in production', function (): void {
    $app = app();
    $app->detectEnvironment(fn (): string => 'production');
    config()->set('app.env', 'production');
    config()->set('cache.default', 'file');

    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('Cache driver');
});

it('warns about file session driver in production', function (): void {
    $app = app();
    $app->detectEnvironment(fn (): string => 'production');
    config()->set('app.env', 'production');
    config()->set('session.driver', 'file');

    $this->artisan('vitals:doctor')
        ->expectsOutputToContain('Session driver');
});
