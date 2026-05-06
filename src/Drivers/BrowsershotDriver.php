<?php

declare(strict_types=1);

namespace LaravelVitals\Drivers;

use JsonException;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\AuditOptions;
use LaravelVitals\Support\LighthouseReport;
use Spatie\Browsershot\Browsershot;
use Throwable;

/**
 * Drives Lighthouse via spatie/browsershot.
 *
 * IMPORTANT: spatie/browsershot ^5.0 does NOT ship a built-in Lighthouse
 * helper. This driver is intentionally non-final so users can subclass it
 * and override makeBrowsershot() to return a Browsershot subclass that
 * implements lighthouseAudit() (e.g. via a custom Puppeteer Node script).
 *
 * isAvailable() returns false on a stock install of Browsershot ^5, so the
 * LighthouseDriverManager auto-resolution chain will skip this driver
 * unless a user has wired up a working bridge.
 */
class BrowsershotDriver implements LighthouseDriver
{
    public function audit(Url $url, AuditOptions $options): LighthouseReport
    {
        if (! class_exists(Browsershot::class)) {
            throw new AuditException(
                'spatie/browsershot is not installed; run `composer require spatie/browsershot`.',
                driver: 'browsershot',
            );
        }

        $appUrl = rtrim((string) config('app.url', 'http://localhost'), '/');
        $target = $appUrl . '/' . ltrim($url->path, '/');

        try {
            $instance = $this->makeBrowsershot($target);

            if ($options->extraHeaders !== []) {
                $instance->setExtraHttpHeaders($options->extraHeaders);
            }

            $instance->setOption('formFactor', $options->device);
            $instance->setOption('onlyCategories', array_map(
                static fn (string $c): string => str_replace('_', '-', $c),
                $options->categories,
            ));

            /** @phpstan-ignore-next-line method.notFound — lighthouseAudit() is provided by Browsershot v4 or a custom subclass */
            $json = $instance->lighthouseAudit();
        } catch (Throwable $e) {
            throw new AuditException(
                "Browsershot lighthouse audit failed for {$url->label}: " . $e->getMessage(),
                driver: 'browsershot',
                previous: $e,
            );
        }

        try {
            return LighthouseReport::fromLighthouseJson($json);
        } catch (JsonException $e) {
            throw new AuditException(
                "Browsershot returned invalid Lighthouse JSON for {$url->label}",
                driver: 'browsershot',
                previous: $e,
            );
        }
    }

    public function isAvailable(): bool
    {
        // The class must exist AND expose a real lighthouseAudit() method.
        // Browsershot v5 does not ship with built-in Lighthouse support; users
        // must subclass BrowsershotDriver and override makeBrowsershot() to
        // return a Browsershot subclass that provides the method.
        return class_exists(Browsershot::class)
            && method_exists(Browsershot::class, 'lighthouseAudit');
    }

    /**
     * Override point for tests. Returns a fresh Browsershot instance bound to
     * the given URL. Override in a subclass to inject a mock or custom instance.
     */
    protected function makeBrowsershot(string $url): Browsershot
    {
        $chromePath = config('vitals.drivers.browsershot.chrome_path');

        $instance = Browsershot::url($url);

        if (is_string($chromePath) && $chromePath !== '') {
            $instance->setChromePath($chromePath);
        }

        return $instance;
    }
}
