<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;
use LaravelVitals\Notifications\BudgetViolated;

beforeEach(function (): void {
    Storage::fake('vitals');
    config()->set('vitals.urls', ['home' => '/']);
    $this->app->bind(LighthouseDriver::class, fn (): \LaravelVitals\Drivers\Stubs\StubLighthouseDriver => new StubLighthouseDriver());

    config()->set('vitals.notifications', [
        'enabled'  => true,
        'channels' => ['mail'],
        'mail'     => ['to' => 'team@example.com'],
        'triggers' => ['budget_violation' => true],
    ]);
});

it('dispatches BudgetViolated when a critical budget is breached', function (): void {
    config()->set('vitals.budgets', [
        'lcp_ms'            => ['warning' => 100, 'critical' => 200],
        'score_performance' => ['warning' => 99, 'critical' => 50],
        'per_url'           => [],
    ]);

    Notification::fake();

    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true, '--fail-on-budget' => true])
        ->assertExitCode(2);

    Notification::assertSentOnDemand(BudgetViolated::class);
});

it('does not dispatch when no violations exist', function (): void {
    config()->set('vitals.budgets', [
        'lcp_ms'            => ['warning' => 5000, 'critical' => 10000],
        'score_performance' => ['warning' => 50, 'critical' => 30],
        'per_url'           => [],
    ]);

    Notification::fake();

    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true, '--fail-on-budget' => true])
        ->assertExitCode(0);

    Notification::assertNothingSent();
});
