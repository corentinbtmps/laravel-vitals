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
        if (! $triggerConfig) {
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

        $slackWebhook = null;
        if (in_array('slack', $channels, true)) {
            $webhook = $config['slack']['webhook_url'] ?? null;
            if (is_string($webhook) && $webhook !== '') {
                $slackWebhook = $webhook;
                $notifiable->route('vitals_slack', $webhook);
            }
        }

        // Only dispatch via Laravel's notification dispatcher for non-slack channels;
        // Slack is handled separately through our custom webhook channel.
        $laravelChannels = array_values(array_filter($channels, fn (string $c): bool => $c !== 'slack'));

        if ($laravelChannels !== []) {
            app(Dispatcher::class)->sendNow($notifiable, $notification, $laravelChannels);
        }

        if ($slackWebhook !== null) {
            app(VitalsSlackChannel::class)->send($notifiable, $notification);
        }
    }
}
