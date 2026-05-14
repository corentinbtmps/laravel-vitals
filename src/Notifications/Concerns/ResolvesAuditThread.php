<?php

declare(strict_types=1);

namespace LaravelVitals\Notifications\Concerns;

use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Models\Audit;

/**
 * Resolves the Slack thread_ts for a given URL's most-recent completed audit.
 *
 * Notifications that want to reply inside an existing Slack thread (e.g.
 * BudgetViolated or RegressionDetected) use this trait to retrieve the
 * `slack_message_ts` stored on the original `AuditCompleted` post.
 */
trait ResolvesAuditThread
{
    /**
     * Return the Slack ts of the most-recent completed audit for the given
     * url_id that has a stored slack_message_ts, or null if none exists.
     */
    protected function resolveThreadTs(int $urlId): ?string
    {
        $audit = Audit::query()
            ->where('url_id', $urlId)
            ->where('status', AuditStatus::Completed)
            ->whereNotNull('slack_message_ts')
            ->orderByDesc('completed_at')
            ->value('slack_message_ts');

        return $audit !== null ? (string) $audit : null;
    }
}
