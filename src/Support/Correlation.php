<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;

/**
 * Computes the front-end ↔ back-end performance breakdown that's the
 * unique value proposition of Laravel Vitals.
 */
final class Correlation
{
    /**
     * Splits LCP into TTFB (backend) and render (frontend) components.
     *
     * @return array{lcp_ms: float|null, ttfb_ms: float|null, render_ms: float|null, ttfb_share: float|null}
     */
    public static function lcpBreakdown(Audit $audit): array
    {
        $lcp  = $audit->lcp_ms !== null ? (float) $audit->lcp_ms : null;
        $ttfb = $audit->ttfb_ms !== null ? (float) $audit->ttfb_ms : null;

        if ($lcp === null || $ttfb === null) {
            return ['lcp_ms' => $lcp, 'ttfb_ms' => $ttfb, 'render_ms' => null, 'ttfb_share' => null];
        }

        $render = max(0.0, $lcp - $ttfb);
        $share  = $lcp > 0 ? round(($ttfb / $lcp) * 100, 1) : null;

        return [
            'lcp_ms'    => $lcp,
            'ttfb_ms'   => $ttfb,
            'render_ms' => $render,
            'ttfb_share'=> $share,
        ];
    }

    /**
     * Estimates how much LCP could improve if N+1 / slow queries were fixed.
     * Heuristic: assume reducing query time by 70% (typical N+1 fix gain)
     * translates 1:1 to TTFB reduction, which directly subtracts from LCP.
     */
    public static function estimatedLcpGainFromQueryFix(BackendTelemetry $telemetry): ?int
    {
        if (! $telemetry->n_plus_one_suspect && empty($telemetry->slow_queries)) {
            return null;
        }

        $queryTime = (float) $telemetry->queries_time_ms;
        if ($queryTime <= 0) {
            return null;
        }

        return (int) round($queryTime * 0.7);
    }

    /**
     * Returns true if the backend is the dominant bottleneck (TTFB > 50% of LCP).
     */
    public static function isBackendBound(Audit $audit): bool
    {
        $breakdown = self::lcpBreakdown($audit);
        return $breakdown['ttfb_share'] !== null && $breakdown['ttfb_share'] >= 50.0;
    }
}
