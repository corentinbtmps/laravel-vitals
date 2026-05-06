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
            'No LighthouseDriver is available. Install lighthouse + node (local or playwright driver), or set VITALS_PAGESPEED_API_KEY.',
        );
    }
}
