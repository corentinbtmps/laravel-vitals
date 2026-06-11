<?php

declare(strict_types=1);

namespace LaravelVitals\Drivers;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\PlaywrightDriver;

/**
 * Resolves a LighthouseDriver by name or via auto-detection.
 *
 * Configuration: config('vitals.driver') — 'auto' | 'local' | 'playwright' | 'pagespeed'.
 *
 * The auto chain probes drivers in this order:
 *     local -> playwright -> pagespeed
 *
 * The first one whose isAvailable() returns true wins.
 */
final readonly class LighthouseDriverManager
{
    /** @var array<string, class-string<LighthouseDriver>> */
    private const MAP = [
        'local'      => LocalLighthouseDriver::class,
        'playwright' => PlaywrightDriver::class,
        'pagespeed'  => PageSpeedApiDriver::class,
    ];

    /** @var array<int, string> */
    private const AUTO_ORDER = ['local', 'playwright', 'pagespeed'];

    /**
     * Actionable "how to make this driver available" hints, surfaced in
     * resolution errors and by `php artisan vitals:doctor`.
     *
     * @var array<string, string>
     */
    private const INSTALL_HINTS = [
        'local'      => 'Install the Lighthouse CLI: `npm install -g lighthouse` (requires Node 18+).',
        'playwright' => 'Install the runner in your project: `npm install --save-dev playwright playwright-lighthouse` then `npx playwright install chromium` (requires Node 18+).',
        'pagespeed'  => 'Set a Google PageSpeed Insights API key: add `VITALS_PAGESPEED_API_KEY=...` to your .env.',
    ];

    public function __construct(
        private Container $container,
    ) {
    }

    /**
     * Resolve a driver by explicit name — alias of driver() for clarity.
     *
     * @throws InvalidArgumentException if the name is unknown
     */
    public function resolveByName(string $name): LighthouseDriver
    {
        return $this->driver($name);
    }

    /**
     * Actionable installation guidance for a driver, or '' if the name is unknown.
     */
    public function installHint(string $name): string
    {
        return self::INSTALL_HINTS[$name] ?? '';
    }

    public function driver(string $name): LighthouseDriver
    {
        if (! isset(self::MAP[$name])) {
            throw new InvalidArgumentException(
                "Unknown LighthouseDriver [$name]. Available: " . implode(', ', array_keys(self::MAP)),
            );
        }

        return $this->container->make(self::MAP[$name]);
    }

    /**
     * Resolve the driver chosen by config (or auto-detect).
     */
    public function resolve(): LighthouseDriver
    {
        $configured = (string) config('vitals.driver', 'auto');

        if ($configured !== 'auto') {
            $driver = $this->driver($configured);

            if (! $driver->isAvailable()) {
                throw new InvalidArgumentException(
                    "LighthouseDriver [$configured] is configured but reports unavailable. " . $this->installHint($configured),
                );
            }

            return $driver;
        }

        foreach (self::AUTO_ORDER as $name) {
            $driver = $this->driver($name);
            if ($driver->isAvailable()) {
                return $driver;
            }
        }

        $hints = array_map(
            fn (string $name): string => "  • {$name}: " . $this->installHint($name),
            self::AUTO_ORDER,
        );

        throw new InvalidArgumentException(
            "No LighthouseDriver is available. Enable one of:\n" . implode("\n", $hints),
        );
    }
}
