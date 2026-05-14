<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use LaravelVitals\Budgets\BudgetViolations;
use LaravelVitals\Enums\Severity;
use LaravelVitals\Models\Audit;
use LaravelVitals\Notifications\Concerns\ResolvesAuditThread;

final class BudgetViolated extends Notification
{
    use ResolvesAuditThread;

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
        $worst = $this->violations->worstSeverity() ?? Severity::Warning;

        return (new MailMessage())
            ->error()
            ->subject("Budget violation ({$worst->value}): {$this->audit->url?->label}")
            ->markdown('vitals::mail.budget-violated', [
                'audit' => $this->audit,
                'violations' => $this->violations,
            ]);
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $worst = $this->violations->worstSeverity() ?? Severity::Warning;
        $emoji = $worst === Severity::Critical ? '🚨' : '⚠️';
        $label = $this->audit->url->label ?? 'unknown';

        $list = collect($this->violations->all())
            ->map(fn ($v): string => "{$v['metric']}={$v['actual']} (>{$v['threshold']})")
            ->implode(', ');

        $message = (new SlackMessage())
            ->headerBlock("{$emoji} Budget violation")
            ->sectionBlock(function (SectionBlock $block) use ($label, $list): void {
                $block->text("`{$label}`: {$list}");
            });

        // Reply in the audit's Slack thread when a prior message exists.
        if ($this->audit->url_id !== null) {
            $ts = $this->resolveThreadTs((int) $this->audit->url_id);
            if ($ts !== null) {
                $message->threadTimestamp($ts);
            }
        }

        return $message;
    }
}
