<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
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

        return (new MailMessage())
            ->error()
            ->subject("Budget violation ({$worst}): {$this->audit->url?->label}")
            ->markdown('vitals::mail.budget-violated', [
                'audit' => $this->audit,
                'violations' => $this->violations,
            ]);
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $worst = $this->violations->worstSeverity() ?? 'warning';
        $emoji = $worst === 'critical' ? '🚨' : '⚠️';
        $label = $this->audit->url->label ?? 'unknown';

        $list = collect($this->violations->all())
            ->map(fn ($v): string => "{$v['metric']}={$v['actual']} (>{$v['threshold']})")
            ->implode(', ');

        return (new SlackMessage())
            ->headerBlock("{$emoji} Budget violation")
            ->sectionBlock(function (SectionBlock $block) use ($label, $list): void {
                $block->text("`{$label}`: {$list}");
            });
    }
}
