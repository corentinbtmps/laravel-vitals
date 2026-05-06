<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Notifications\WeeklyDigest;

beforeEach(function (): void {
    config()->set('vitals.notifications', [
        'enabled'  => true,
        'channels' => ['mail'],
        'mail'     => ['to' => 'team@example.com'],
        'triggers' => ['weekly_digest' => true],
    ]);
});

it('sends a WeeklyDigest summarising the last 7 days when audits exist', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => 90,
        'completed_at'      => now()->subDays(2),
    ]);

    Notification::fake();

    $this->artisan('vitals:digest:send')->assertSuccessful();

    Notification::assertSentOnDemand(WeeklyDigest::class);
});

it('does nothing when no audits in the last 7 days', function (): void {
    Notification::fake();

    $this->artisan('vitals:digest:send')->assertSuccessful();

    Notification::assertNothingSent();
});
