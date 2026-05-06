<?php

declare(strict_types=1);

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use LaravelVitals\Notifications\Channels\VitalsNotifier;

it('dispatches notifications to all configured channels with the configured recipient', function (): void {
    config()->set('vitals.notifications', [
        'enabled'  => true,
        'channels' => ['mail'],
        'mail'     => ['to' => 'team@example.com'],
        'slack'    => ['webhook_url' => null],
        'triggers' => ['budget_violation' => true],
    ]);

    Notification::fake();

    $notification = new class extends \Illuminate\Notifications\Notification {
        public function via(object $notifiable): array { return ['mail']; }
    };

    app(VitalsNotifier::class)->send('budget_violation', $notification);

    Notification::assertSentOnDemand($notification::class, fn($n, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'team@example.com');
});

it('does nothing when the trigger is disabled', function (): void {
    config()->set('vitals.notifications', [
        'enabled'  => true,
        'channels' => ['mail'],
        'mail'     => ['to' => 'team@example.com'],
        'triggers' => ['budget_violation' => false],
    ]);

    Notification::fake();

    app(VitalsNotifier::class)->send('budget_violation', new class extends \Illuminate\Notifications\Notification {});

    Notification::assertNothingSent();
});

it('does nothing when notifications are globally disabled', function (): void {
    config()->set('vitals.notifications', [
        'enabled'  => false,
        'triggers' => ['budget_violation' => true],
    ]);

    Notification::fake();

    app(VitalsNotifier::class)->send('budget_violation', new class extends \Illuminate\Notifications\Notification {});

    Notification::assertNothingSent();
});
