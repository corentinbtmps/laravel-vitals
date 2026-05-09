<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use LaravelVitals\Models\Url;
use LaravelVitals\Notifications\Concerns\ResolvesAuditThread;

final class RegressionDetected extends Notification
{
    use ResolvesAuditThread;

    public function __construct(
        public readonly Url $url,
        public readonly int $baselineScore,
        public readonly int $currentScore,
        public readonly float $dropPercent,
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
            ->error()
            ->subject("Performance regression: {$this->url->label}")
            ->markdown('vitals::mail.regression-detected', [
                'url' => $this->url,
                'baselineScore' => $this->baselineScore,
                'currentScore' => $this->currentScore,
                'dropPercent' => $this->dropPercent,
            ]);
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $message = (new SlackMessage())
            ->headerBlock('📉 Performance regression')
            ->sectionBlock(function (SectionBlock $block): void {
                $block->text("`{$this->url->label}` perf regressed: {$this->baselineScore} → {$this->currentScore} (-{$this->dropPercent}%)");
            });

        // Reply in the URL's Slack audit thread when a prior message exists.
        $ts = $this->resolveThreadTs((int) $this->url->id);
        if ($ts !== null) {
            $message->threadTimestamp($ts);
        }

        return $message;
    }
}
