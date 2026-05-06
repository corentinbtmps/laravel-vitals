<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use LaravelVitals\Models\Url;

final class RegressionDetected extends Notification
{
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
            ->line("Performance score for {$this->url->label} dropped from {$this->baselineScore} to {$this->currentScore} (-{$this->dropPercent}%).")
            ->line('Investigate recent deploys or content changes.');
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage())
            ->headerBlock('📉 Performance regression')
            ->sectionBlock(function (SectionBlock $block): void {
                $block->text("`{$this->url->label}` perf regressed: {$this->baselineScore} → {$this->currentScore} (-{$this->dropPercent}%)");
            });
    }
}
