<?php

declare(strict_types=1);

namespace LaravelVitals\Drivers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\Mappers\PageSpeedMapper;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\AuditOptions;
use LaravelVitals\Support\LighthouseReport;
use Throwable;

/**
 * Drives Lighthouse via the Google PageSpeed Insights v5 API.
 *
 * Limitations:
 *   - The target URL must be publicly reachable.
 *   - The X-Vitals-Audit-Id header CANNOT be injected via PSI, so backend
 *     telemetry capture (Plan 3) is unavailable when this driver is used.
 */
final class PageSpeedApiDriver implements LighthouseDriver
{
    public function audit(Url $url, AuditOptions $options): LighthouseReport
    {
        $config = (array) config('vitals.drivers.pagespeed', []);

        $apiKey   = (string) ($config['api_key']  ?? '');
        $endpoint = (string) ($config['endpoint'] ?? 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed');

        if ($apiKey === '') {
            throw new AuditException(
                'PageSpeed API key is missing. Set VITALS_PAGESPEED_API_KEY.',
                driver: 'pagespeed',
            );
        }

        $appUrl = rtrim((string) config('app.url', 'http://localhost'), '/');
        $target = $appUrl . '/' . ltrim($url->path, '/');

        $query = [
            'url'      => $target,
            'strategy' => $options->device === \LaravelVitals\Enums\Device::Desktop ? 'desktop' : 'mobile',
            'key'      => $apiKey,
        ];

        foreach ($options->categories as $category) {
            $query['category'][] = strtoupper(str_replace('-', '_', $category));
        }

        try {
            $response = Http::timeout($options->timeoutSeconds)
                ->get($endpoint, $query);
        } catch (ConnectionException $e) {
            throw new AuditException(
                "PageSpeed API request failed for {$url->label}: " . $e->getMessage(),
                driver: 'pagespeed',
                previous: $e,
            );
        }

        if (! $response->successful()) {
            throw new AuditException(
                "PageSpeed API returned HTTP {$response->status()} for {$url->label}",
                driver: 'pagespeed',
            );
        }

        try {
            $report = PageSpeedMapper::fromPageSpeedJson($response->body());

            // Track API usage — increment the audit's call counter.
            if ($options->auditId !== null) {
                \LaravelVitals\Models\Audit::where('id', $options->auditId)
                    ->increment('api_call_count');
            }

            return $report;
        } catch (Throwable $e) {
            if ($e instanceof AuditException) {
                throw $e;
            }
            throw new AuditException(
                "Failed to parse PageSpeed response for {$url->label}",
                driver: 'pagespeed',
                previous: $e,
            );
        }
    }

    public function isAvailable(): bool
    {
        $key = config('vitals.drivers.pagespeed.api_key');
        return is_string($key) && $key !== '';
    }
}
