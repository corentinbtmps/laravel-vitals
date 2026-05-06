<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

use LaravelVitals\Models\Url;

/**
 * Synchronises the `vitals_urls` table with config('vitals.urls').
 *
 * - Adds rows for labels in config that don't yet exist.
 * - Updates the path of existing labels whose config value changed.
 * - Never removes rows: hosts can manage URLs via the dashboard later, and
 *   we don't want to drop manually-added entries on every config reload.
 */
final class UrlSeeder
{
    public function sync(): void
    {
        $configured = (array) config('vitals.urls', []);

        foreach ($configured as $label => $path) {
            Url::updateOrCreate(
                ['label' => (string) $label],
                ['path'  => (string) $path],
            );
        }
    }
}
