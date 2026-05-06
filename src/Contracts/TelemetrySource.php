<?php

declare(strict_types=1);

namespace LaravelVitals\Contracts;

use LaravelVitals\Telemetry\TrendStats;

/**
 * Read-only source of aggregated real-traffic telemetry for a route.
 *
 * Implementations: PulseSource, TelescopeSource.
 */
interface TelemetrySource
{
    /**
     * Whether this source can produce data right now (e.g. its tables exist).
     */
    public function isAvailable(): bool;

    /**
     * Aggregated trends for the given route name (e.g. 'home').
     * Implementations should return TrendStats::empty() when they have no data
     * rather than throwing.
     */
    public function getTrendsFor(string $routeName): TrendStats;
}
