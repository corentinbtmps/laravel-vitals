<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
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
        $label = $this->audit->url->label ?? 'unknown';
        $lcp   = $this->audit->lcp_ms !== null ? round((float) $this->audit->lcp_ms) . 'ms' : 'n/a';

        return (new SlackMessage())
            ->headerBlock('✅ Audit completed')
            ->sectionBlock(function (SectionBlock $block) use ($label, $lcp): void {
                $block->text("`{$label}` — perf {$this->audit->score_performance}, LCP {$lcp}");
            });
    }
}
