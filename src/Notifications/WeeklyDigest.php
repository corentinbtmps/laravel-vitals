<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;

final class WeeklyDigest extends Notification
{
    /**
     * @param array<int, array{label: string, audits: int, avg_perf: int}> $rows
     */
    public function __construct(
        public readonly int $totalAudits,
        public readonly array $rows,
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
            ->subject('Laravel Vitals — weekly digest')
            ->markdown('vitals::mail.weekly-digest', [
                'totalAudits' => $this->totalAudits,
                'rows' => $this->rows,
            ]);
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $list = collect($this->rows)
            ->map(fn ($r): string => "{$r['label']} (avg perf {$r['avg_perf']})")
            ->take(10)
            ->implode(' • ');

        return (new SlackMessage())
            ->headerBlock('📊 Weekly Vitals digest')
            ->sectionBlock(function (SectionBlock $block) use ($list): void {
                $block->text("{$this->totalAudits} audits — {$list}");
            });
    }
}
