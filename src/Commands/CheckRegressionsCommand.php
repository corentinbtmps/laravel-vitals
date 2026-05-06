<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Notifications\Channels\VitalsNotifier;
use LaravelVitals\Notifications\RegressionDetected;

/**
 * `php artisan vitals:check-regressions`
 *
 * Compares each URL's most recent completed audit against a baseline (default
 * 7 days ago). Dispatches RegressionDetected when the performance score drops
 * by more than config('vitals.notifications.triggers.regression.threshold_percent').
 */
final class CheckRegressionsCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:check-regressions
        {--baseline-days=7 : Number of days back to use as baseline}';

    /** @var string */
    protected $description = 'Check for performance regressions and notify if any drops exceed the configured threshold.';

    public function handle(VitalsNotifier $notifier): int
    {
        $threshold = (float) (config('vitals.notifications.triggers.regression.threshold_percent') ?? 10);
        $baselineDays = (int) $this->option('baseline-days');

        foreach (Url::query()->where('enabled', true)->get() as $url) {
            $baseline = Audit::query()
                ->where('url_id', $url->id)
                ->where('status', 'completed')
                ->where('completed_at', '<=', now()->subDays($baselineDays))
                ->orderByDesc('completed_at')
                ->first();

            $current = Audit::query()
                ->where('url_id', $url->id)
                ->where('status', 'completed')
                ->where('completed_at', '>', now()->subDays($baselineDays))
                ->orderByDesc('completed_at')
                ->first();

            if ($baseline === null || $current === null) {
                continue;
            }

            $baselineScore = (int) ($baseline->score_performance ?? 0);
            $currentScore  = (int) ($current->score_performance ?? 0);

            if ($baselineScore === 0) {
                continue;
            }

            $dropPercent = round((($baselineScore - $currentScore) / $baselineScore) * 100, 2);

            if ($dropPercent > $threshold) {
                $notifier->send('regression', new RegressionDetected($url, $baselineScore, $currentScore, $dropPercent));
                $this->warn("{$url->label}: regression {$baselineScore} → {$currentScore} (-{$dropPercent}%)");
            }
        }

        return self::SUCCESS;
    }
}
