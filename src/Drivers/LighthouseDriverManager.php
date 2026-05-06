<?php

declare(strict_types=1);

namespace LaravelVitals\Drivers;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use LaravelVitals\Contracts\LighthouseDriver;

/**
 * Resolves a LighthouseDriver by name or via auto-detection.
 *
 * Configuration: config('vitals.driver') — 'auto' | 'local' | 'browsershot' | 'pagespeed'.
 *
 * The auto chain probes drivers in this order:
 *     local -> browsershot -> pagespeed
 *
 * The first one whose isAvailable() returns true wins. Note that on a stock
 * install of spatie/browsershot ^5, BrowsershotDriver reports unavailable
 * because Browsershot v5 dropped the built-in lighthouseAudit() helper.
 */
final readonly class LighthouseDriverManager
{
    /** @var array<string, class-string<LighthouseDriver>> */
    private const MAP = [
        'local'       => LocalLighthouseDriver::class,
        'browsershot' => BrowsershotDriver::class,
        'pagespeed'   => PageSpeedApiDriver::class,
    ];

    /** @var array<int, string> */
    private const AUTO_ORDER = ['local', 'browsershot', 'pagespeed'];

    public function __construct(
        private Container $container,
    ) {
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
                    "LighthouseDriver [$configured] is configured but reports unavailable.",
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

        throw new InvalidArgumentException(
            'No LighthouseDriver is available. Install lighthouse + node, or spatie/browsershot with a Lighthouse bridge, or set VITALS_PAGESPEED_API_KEY.',
        );
    }
}
