<?php

declare(strict_types=1);

namespace LaravelVitals\Contracts;

use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditOptions;
use LaravelVitals\Support\LighthouseReport;

/**
 * Drives an actual Lighthouse audit against a URL.
 *
 * Implementations:
 *   - LocalLighthouseDriver  — invokes the `lighthouse` CLI locally
 *   - BrowsershotDriver      — drives Lighthouse via spatie/browsershot
 *   - PageSpeedApiDriver     — calls Google PageSpeed Insights v5
 *
 * Implementations MUST be deterministic about thrown exceptions: any failure
 * must surface as a LaravelVitals\Support\AuditException with the audit id
 * embedded so the controller can correlate failures with the persisted row.
 */
interface LighthouseDriver
{
    /**
     * Run a Lighthouse audit and return a normalised report.
     *
     * @throws \LaravelVitals\Support\AuditException
     */
    public function audit(Url $url, AuditOptions $options): LighthouseReport;

    /**
     * Whether this driver can run on the current host. Used by the auto
     * resolution chain. SHOULD be cheap (no network calls, no spawned
     * processes beyond a simple `which`).
     */
    public function isAvailable(): bool;
}
