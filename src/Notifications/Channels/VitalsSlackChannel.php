<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications\Channels;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Notifications\Notification;

/**
 * Minimal Slack webhook channel.
 *
 * Reads a `toVitalsSlack()` method from the notification that returns an
 * array<string, mixed> Slack payload (text / blocks), and POSTs it to the
 * webhook URL that was routed on the notifiable.
 */
final readonly class VitalsSlackChannel
{
    public function __construct(
        private HttpFactory $http,
    ) {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        /** @var string|null $url */
        $url = $notifiable->routeNotificationFor('vitals_slack', $notification); // @phpstan-ignore-line

        if (! is_string($url) || $url === '') {
            return;
        }

        if (! method_exists($notification, 'toVitalsSlack')) {
            return;
        }

        /** @var array<string, mixed> $payload */
        $payload = $notification->toVitalsSlack($notifiable); // @phpstan-ignore-line

        $this->http->post($url, $payload);
    }
}
