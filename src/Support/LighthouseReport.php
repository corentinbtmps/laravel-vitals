<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * Driver-agnostic normalised representation of a Lighthouse audit result.
 *
 * Built either from raw Lighthouse JSON (LocalLighthouseDriver,
 * BrowsershotDriver) or from a PSI response transformed by PageSpeedMapper.
 */
final readonly class LighthouseReport
{
    /**
     * @param array{performance: int|null, accessibility: int|null, best_practices: int|null, seo: int|null} $scores
     * @param array{lcp_ms: float|null, cls: float|null, inp_ms: float|null, ttfb_ms: float|null, fcp_ms: float|null, si_ms: float|null, tbt_ms: float|null} $metrics
     * @param array<int, array<string, mixed>> $audits   non-passed Lighthouse audit entries
     * @param string $rawJson                            full Lighthouse JSON for archival on disk
     */
    public function __construct(
        public array $scores,
        public array $metrics,
        public array $audits,
        public string $rawJson,
    ) {
    }

    /**
     * Build a report from a raw Lighthouse v12 JSON string.
     *
     * @throws \JsonException
     */
    public static function fromLighthouseJson(string $json): self
    {
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $categories = $decoded['categories'] ?? [];
        $audits     = $decoded['audits'] ?? [];

        $scores = [
            'performance'    => self::scoreFor($categories, 'performance'),
            'accessibility'  => self::scoreFor($categories, 'accessibility'),
            'best_practices' => self::scoreFor($categories, 'best-practices'),
            'seo'            => self::scoreFor($categories, 'seo'),
        ];

        $metrics = [
            'lcp_ms'  => self::numericFor($audits, 'largest-contentful-paint'),
            'cls'     => self::numericFor($audits, 'cumulative-layout-shift'),
            'inp_ms'  => self::numericFor($audits, 'interaction-to-next-paint'),
            'ttfb_ms' => self::numericFor($audits, 'server-response-time'),
            'fcp_ms'  => self::numericFor($audits, 'first-contentful-paint'),
            'si_ms'   => self::numericFor($audits, 'speed-index'),
            'tbt_ms'  => self::numericFor($audits, 'total-blocking-time'),
        ];

        $nonPassed = array_values(array_filter(
            $audits,
            static fn (array $a): bool => isset($a['score']) && is_numeric($a['score']) && (float) $a['score'] < 0.9,
        ));

        return new self($scores, $metrics, $nonPassed, $json);
    }

    /**
     * @param array<string, array<string, mixed>> $categories
     */
    private static function scoreFor(array $categories, string $key): ?int
    {
        $raw = $categories[$key]['score'] ?? null;

        if (! is_numeric($raw)) {
            return null;
        }

        return (int) round(((float) $raw) * 100);
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     */
    private static function numericFor(array $audits, string $key): ?float
    {
        $raw = $audits[$key]['numericValue'] ?? null;

        return is_numeric($raw) ? (float) $raw : null;
    }
}
