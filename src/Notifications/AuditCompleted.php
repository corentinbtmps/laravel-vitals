<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use LaravelVitals\Models\Audit;

final class AuditCompleted extends Notification
{
    public function __construct(
        public readonly Audit $audit,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return (array) config('vitals.notifications.channels', ['mail']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Audit completed: {$this->audit->url?->label}")
            ->line("Audit completed for {$this->audit->url?->label} ({$this->audit->device}).")
            ->line("Performance: {$this->audit->score_performance}")
            ->line("LCP: " . ($this->audit->lcp_ms !== null ? round((float) $this->audit->lcp_ms) . ' ms' : 'n/a'));
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage())
            ->success()
            ->content("✅ Audit `{$this->audit->url?->label}` completed — perf {$this->audit->score_performance}, LCP "
                . ($this->audit->lcp_ms !== null ? round((float) $this->audit->lcp_ms) . 'ms' : 'n/a'));
    }
}
