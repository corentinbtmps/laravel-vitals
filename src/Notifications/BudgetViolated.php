<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use LaravelVitals\Budgets\BudgetViolations;
use LaravelVitals\Models\Audit;

final class BudgetViolated extends Notification
{
    public function __construct(
        public readonly Audit $audit,
        public readonly BudgetViolations $violations,
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
        $worst = $this->violations->worstSeverity() ?? 'warning';

        $msg = (new MailMessage())
            ->error()
            ->subject("Budget violation ({$worst}): {$this->audit->url?->label}")
            ->line("The audit for {$this->audit->url?->label} violated " . count($this->violations->all()) . ' budget(s).');

        foreach ($this->violations->all() as $v) {
            $msg->line("- {$v['metric']} = {$v['actual']} (>{$v['severity']} threshold {$v['threshold']})");
        }

        return $msg;
    }

    /**
     * @return array<string, mixed>
     */
    public function toVitalsSlack(object $notifiable): array
    {
        $worst = $this->violations->worstSeverity() ?? 'warning';
        $emoji = $worst === 'critical' ? '🚨' : '⚠️';
        $label = $this->audit->url->label ?? 'unknown';

        $list = collect($this->violations->all())
            ->map(fn ($v): string => "{$v['metric']}={$v['actual']} (>{$v['threshold']})")
            ->implode(', ');

        return [
            'text' => "{$emoji} {$label}: {$list}",
        ];
    }
}
