<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications\Channels;

use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;

/**
 * Central dispatcher: takes a trigger key and a Notification instance,
 * checks config('vitals.notifications.triggers.{key}'), routes the
 * notification to the configured channels.
 */
final class VitalsNotifier
{
    public function send(string $trigger, Notification $notification): void
    {
        $config = (array) config('vitals.notifications', []);

        if (! ($config['enabled'] ?? false)) {
            return;
        }

        $triggerConfig = $config['triggers'][$trigger] ?? false;
        if ($triggerConfig === false || $triggerConfig === null) {
            return;
        }

        $channels = (array) ($config['channels'] ?? []);
        if ($channels === []) {
            return;
        }

        $notifiable = new AnonymousNotifiable();

        if (in_array('mail', $channels, true)) {
            $to = $config['mail']['to'] ?? null;
            if (is_string($to) && $to !== '') {
                $notifiable->route('mail', $to);
            }
        }

        if (in_array('slack', $channels, true)) {
            $webhook = $config['slack']['webhook_url'] ?? null;
            if (is_string($webhook) && $webhook !== '') {
                $notifiable->route('slack', $webhook);
            }
        }

        app(Dispatcher::class)->sendNow($notifiable, $notification, $channels);
    }
}
