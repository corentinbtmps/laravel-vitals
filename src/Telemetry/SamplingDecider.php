<?php

declare(strict_types=1);

namespace LaravelVitals\Telemetry;

/**
 * Decides whether a header-less request should be captured under the
 * always_capture opt-in.
 *
 * The randomValue argument is injected so tests can be deterministic. In
 * production code the caller passes mt_rand() / mt_getrandmax().
 */
final class SamplingDecider
{
    public static function shouldCapture(?float $randomValue = null): bool
    {
        if (! (bool) config('vitals.telemetry.always_capture', false)) {
            return false;
        }

        $rate = (float) config('vitals.telemetry.sample_rate', 0.0);

        if ($rate <= 0.0) {
            return false;
        }

        if ($rate >= 1.0) {
            return true;
        }

        $randomValue ??= mt_rand() / mt_getrandmax();

        return $randomValue < $rate;
    }
}
