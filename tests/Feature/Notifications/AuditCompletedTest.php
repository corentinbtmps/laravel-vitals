<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\Stubs\StubLighthouseDriver;
use LaravelVitals\Notifications\AuditCompleted;

beforeEach(function (): void {
    Storage::fake('vitals');
    config()->set('vitals.urls', ['home' => '/']);
    $this->app->bind(LighthouseDriver::class, fn () => new StubLighthouseDriver());

    config()->set('vitals.notifications', [
        'enabled'  => true,
        'channels' => ['mail'],
        'mail'     => ['to' => 'team@example.com'],
        'triggers' => ['audit_completed' => true],
    ]);
});

it('dispatches AuditCompleted after a successful audit when the trigger is on', function (): void {
    Notification::fake();

    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true])->assertSuccessful();

    Notification::assertSentOnDemand(AuditCompleted::class);
});

it('does not dispatch when audit_completed trigger is off', function (): void {
    config()->set('vitals.notifications.triggers.audit_completed', false);

    Notification::fake();

    $this->artisan('vitals:audit', ['label' => 'home', '--sync' => true])->assertSuccessful();

    Notification::assertNothingSent();
});
