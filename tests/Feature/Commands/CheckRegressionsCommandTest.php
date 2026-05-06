<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Notifications\RegressionDetected;

beforeEach(function (): void {
    config()->set('vitals.notifications', [
        'enabled'  => true,
        'channels' => ['mail'],
        'mail'     => ['to' => 'team@example.com'],
        'triggers' => ['regression' => ['threshold_percent' => 10]],
    ]);
});

function makeRegAudit(int $url, int $score, Carbon $when): void
{
    Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => $score,
        'completed_at'      => $when,
    ]);
}

it('dispatches RegressionDetected when score drops more than threshold', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    makeRegAudit($url->id, 95, now()->subDays(8));   // baseline
    makeRegAudit($url->id, 80, now());               // current — 15.7% drop, > 10%

    Notification::fake();

    $this->artisan('vitals:check-regressions')->assertSuccessful();

    Notification::assertSentOnDemand(RegressionDetected::class);
});

it('does not dispatch when drop is under the threshold', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    makeRegAudit($url->id, 95, now()->subDays(8));
    makeRegAudit($url->id, 92, now());   // 3% drop

    Notification::fake();

    $this->artisan('vitals:check-regressions')->assertSuccessful();

    Notification::assertNothingSent();
});

it('does not dispatch when there is no baseline', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    makeRegAudit($url->id, 80, now());

    Notification::fake();

    $this->artisan('vitals:check-regressions')->assertSuccessful();

    Notification::assertNothingSent();
});
