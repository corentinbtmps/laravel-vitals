<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use LaravelVitals\Models\Audit;

/**
 * Sent when an audit completes successfully.
 *
 * The Slack message is the first post in an audit thread.
 * After this notification is sent the caller should persist the returned
 * Slack message `ts` (timestamp) to `vitals_audits.slack_message_ts` so
 * that follow-up notifications (BudgetViolated, RegressionDetected) can
 * reply inside the same thread via `threadTimestamp()`.
 */
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
            ->markdown('vitals::mail.audit-completed', ['audit' => $this->audit]);
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

    /**
     * Persist the Slack message ts to the audit row so that subsequent
     * notifications can thread-reply to this message.
     *
     * Call this after the Slack notification has been sent and you have
     * obtained the `ts` from the Slack API response.
     */
    public function storeSlackTs(string $ts): void
    {
        $this->audit->updateQuietly(['slack_message_ts' => $ts]);
    }
}
