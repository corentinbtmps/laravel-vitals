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
 * Drives Lighthouse via spatie/browsershot. Calls lighthouseAudit() on the
 * Browsershot instance, which must be available on the installed version or
 * provided via a custom makeBrowsershot() subclass override.
 *
 * NOTE: spatie/browsershot v5 does not include a built-in lighthouseAudit()
 * method — that feature was present in v4 and removed in v5. If you need
 * Lighthouse support with Browsershot v5 you should either:
 *   (a) extend this class and override makeBrowsershot() to return a custom
 *       subclass of Browsershot that adds lighthouseAudit(), or
 *   (b) use the LocalLighthouseDriver instead (recommended for v5+).
 *
 * Non-final so tests can subclass to inject a Browsershot mock via the
 * makeBrowsershot() seam without touching the real Browsershot::url() factory.
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
        return class_exists(Browsershot::class);
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
