<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        $msg = (new MailMessage())
            ->subject('Laravel Vitals — weekly digest')
            ->line("Total audits this week: {$this->totalAudits}");

        foreach ($this->rows as $r) {
            $msg->line("- {$r['label']}: {$r['audits']} audit(s), avg perf {$r['avg_perf']}");
        }

        return $msg;
    }

    /**
     * @return array<string, mixed>
     */
    public function toVitalsSlack(object $notifiable): array
    {
        $list = collect($this->rows)
            ->map(fn ($r): string => "{$r['label']} (avg perf {$r['avg_perf']})")
            ->take(10)
            ->implode(' • ');

        return [
            'text' => "📊 Weekly Vitals digest — {$this->totalAudits} audits — {$list}",
        ];
    }
}
