<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * Driver-agnostic normalised representation of a Lighthouse audit result.
 *
 * Plan 2 populates the body and adds builders from each driver's raw output.
 */
final class LighthouseReport
{
    /**
     * @param array<string, int|null> $scores      keys: performance, accessibility, best_practices, seo
     * @param array<string, float|null> $metrics    keys: lcp_ms, cls, inp_ms, ttfb_ms, fcp_ms, si_ms, tbt_ms
     * @param array<int, array<string, mixed>> $audits raw Lighthouse audit entries (not-passed)
     * @param string $rawJson                        full Lighthouse JSON for archival on disk
     */
    public function __construct(
        public readonly array $scores,
        public readonly array $metrics,
        public readonly array $audits,
        public readonly string $rawJson,
    ) {
    }
}
