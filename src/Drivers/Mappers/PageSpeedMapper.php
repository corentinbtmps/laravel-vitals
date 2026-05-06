<?php

declare(strict_types=1);

namespace LaravelVitals\Drivers\Mappers;

use JsonException;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\LighthouseReport;

/**
 * Translates a Google PageSpeed Insights v5 response into a LighthouseReport.
 *
 * PSI embeds a Lighthouse v12 result under "lighthouseResult", so we extract
 * it, re-encode it, and delegate to LighthouseReport::fromLighthouseJson().
 */
final class PageSpeedMapper
{
    public static function fromPageSpeedJson(string $json): LighthouseReport
    {
        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new AuditException(
                'PageSpeed response was not valid JSON.',
                driver: 'pagespeed',
                previous: $e,
            );
        }

        if (! isset($decoded['lighthouseResult']) || ! is_array($decoded['lighthouseResult'])) {
            throw new AuditException(
                'PageSpeed response is missing the lighthouseResult field.',
                driver: 'pagespeed',
            );
        }

        try {
            $rawJson = json_encode($decoded['lighthouseResult'], JSON_THROW_ON_ERROR);
            return LighthouseReport::fromLighthouseJson($rawJson);
        } catch (JsonException $e) {
            throw new AuditException(
                'Failed to re-encode PageSpeed lighthouseResult.',
                driver: 'pagespeed',
                previous: $e,
            );
        }
    }
}
