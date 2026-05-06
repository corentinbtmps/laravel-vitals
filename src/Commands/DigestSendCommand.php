<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use LaravelVitals\Models\Audit;
use LaravelVitals\Notifications\Channels\VitalsNotifier;
use LaravelVitals\Notifications\WeeklyDigest;

/**
 * `php artisan vitals:digest:send`
 *
 * Aggregates audits from the last 7 days per URL and sends a digest
 * notification (mail / Slack).
 */
final class DigestSendCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:digest:send {--days=7 : Number of days to summarise}';

    /** @var string */
    protected $description = 'Send a weekly digest notification summarising recent audits.';

    public function handle(VitalsNotifier $notifier): int
    {
        $days = (int) $this->option('days');

        $audits = Audit::query()
            ->with('url')
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays($days))
            ->get();

        if ($audits->isEmpty()) {
            $this->info("No audits in the last {$days} days. Nothing to send.");
            return self::SUCCESS;
        }

        $rows = $audits
            ->groupBy(fn ($a) => $a->url?->label ?? 'unknown')
            ->map(fn ($group, $label) => [
                'label'    => (string) $label,
                'audits'   => $group->count(),
                'avg_perf' => (int) round((float) $group->avg('score_performance')),
            ])
            ->values()
            ->all();

        $notifier->send('weekly_digest', new WeeklyDigest($audits->count(), $rows));

        $this->info("Digest sent: {$audits->count()} audits across " . count($rows) . ' URL(s).');

        return self::SUCCESS;
    }
}
