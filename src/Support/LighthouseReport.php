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
     * Parse the raw Lighthouse JSON to extract richer structured details
     * (resource summary, third parties, main thread, LCP element, etc.).
     *
     * Returns null if extraction fails — the report parses fine for scores
     * but the extras are best-effort.
     *
     * @return array<string, mixed>|null
     */
    public static function extractDetails(string $rawJson): ?array
    {
        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($rawJson, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        $audits = $decoded['audits'] ?? [];

        return [
            'page_weight_bytes'       => self::numericFromAudit($audits, 'total-byte-weight'),
            'request_count'           => self::countFromAudit($audits, 'network-requests'),
            'dom_size'                => self::numericFromAudit($audits, 'dom-size'),
            'render_blocking_time_ms' => self::numericFromAudit($audits, 'render-blocking-resources'),
            'lcp_element'             => self::lcpElement($audits),
            'resource_summary'        => self::resourceSummary($audits),
            'third_parties'           => self::thirdParties($audits),
            'main_thread'             => self::mainThread($audits),
            'bootup_time'             => self::bootupTime($audits),
            'cache_policy'            => self::cachePolicy($audits),
            'slow_requests'           => self::slowRequests($audits),
            'critical_chain_depth'    => self::criticalChainDepth($audits),
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     */
    private static function numericFromAudit(array $audits, string $key): ?float
    {
        $val = $audits[$key]['numericValue'] ?? null;

        return is_numeric($val) ? (float) $val : null;
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     */
    private static function countFromAudit(array $audits, string $key): ?int
    {
        $items = $audits[$key]['details']['items'] ?? null;

        return is_array($items) ? count($items) : null;
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     * @return array{snippet: string, selector: string}|null
     */
    private static function lcpElement(array $audits): ?array
    {
        $items = $audits['largest-contentful-paint-element']['details']['items'][0]['items'] ?? null;
        if (! is_array($items) || count($items) === 0) {
            // Some Lighthouse versions nest differently; try the flat form.
            $node = $audits['largest-contentful-paint-element']['details']['items'][0]['node'] ?? null;
            if (is_array($node)) {
                return [
                    'snippet'  => (string) ($node['snippet']  ?? ''),
                    'selector' => (string) ($node['selector'] ?? ''),
                ];
            }

            return null;
        }

        $node = $items[0]['node'] ?? null;
        if (! is_array($node)) {
            return null;
        }

        return [
            'snippet'  => (string) ($node['snippet']  ?? ''),
            'selector' => (string) ($node['selector'] ?? ''),
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     * @return array<int, array{type: string, count: int, bytes: int}>
     */
    private static function resourceSummary(array $audits): array
    {
        $items = $audits['resource-summary']['details']['items'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $type  = (string) ($item['resourceType'] ?? $item['label'] ?? 'other');
            $count = (int) ($item['requestCount'] ?? 0);
            $bytes = (int) ($item['transferSize'] ?? 0);

            if ($type === 'total' || $type === 'Total') {
                continue; // skip the synthetic total row
            }

            $result[] = ['type' => $type, 'count' => $count, 'bytes' => $bytes];
        }

        return $result;
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     * @return array<int, array{entity: string, transfer_bytes: int, blocking_ms: float, main_thread_ms: float}>
     */
    private static function thirdParties(array $audits): array
    {
        $items = $audits['third-party-summary']['details']['items'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $entity   = is_array($item['entity'] ?? null)
                ? (string) ($item['entity']['text'] ?? '')
                : (string) ($item['entity'] ?? '');
            $result[] = [
                'entity'         => $entity,
                'transfer_bytes' => (int) ($item['transferSize'] ?? 0),
                'blocking_ms'    => (float) ($item['blockingTime'] ?? 0),
                'main_thread_ms' => (float) ($item['mainThreadTime'] ?? 0),
            ];
        }

        return array_slice($result, 0, 10);
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     * @return array<int, array{category: string, duration_ms: float}>
     */
    private static function mainThread(array $audits): array
    {
        $items = $audits['mainthread-work-breakdown']['details']['items'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $result[] = [
                'category'    => (string) ($item['groupLabel'] ?? $item['group'] ?? 'other'),
                'duration_ms' => (float) ($item['duration'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     * @return array<int, array{url: string, total_ms: float}>
     */
    private static function bootupTime(array $audits): array
    {
        $items = $audits['bootup-time']['details']['items'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $result[] = [
                'url'      => (string) ($item['url'] ?? ''),
                'total_ms' => (float) ($item['total'] ?? 0),
            ];
        }

        return array_slice($result, 0, 10);
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     * @return array<int, array{url: string, ttl_seconds: int}>
     */
    private static function cachePolicy(array $audits): array
    {
        $items = $audits['uses-long-cache-ttl']['details']['items'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $ms       = (int) ($item['cacheLifetimeMs'] ?? 0);
            $result[] = [
                'url'         => (string) ($item['url'] ?? ''),
                'ttl_seconds' => (int) ($ms / 1000),
            ];
        }

        return array_slice($result, 0, 10);
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     * @return array<int, array{url: string, transfer_bytes: int, duration_ms: float, resource_type: string}>
     */
    private static function slowRequests(array $audits): array
    {
        $items = $audits['network-requests']['details']['items'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $rows = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $start  = (float) ($item['startTime'] ?? 0);
            $end    = (float) ($item['endTime'] ?? 0);
            $rows[] = [
                'url'            => (string) ($item['url'] ?? ''),
                'transfer_bytes' => (int) ($item['transferSize'] ?? 0),
                'duration_ms'    => max(0.0, $end - $start),
                'resource_type'  => (string) ($item['resourceType'] ?? 'other'),
            ];
        }

        // Sort by duration desc, take top 20.
        usort($rows, fn (array $a, array $b): int => $b['duration_ms'] <=> $a['duration_ms']);

        return array_slice($rows, 0, 20);
    }

    /**
     * @param array<string, array<string, mixed>> $audits
     */
    private static function criticalChainDepth(array $audits): ?int
    {
        $chains = $audits['critical-request-chains']['details']['chains'] ?? null;
        if (! is_array($chains)) {
            return null;
        }

        $maxDepth = 0;
        $walk     = function (mixed $chain, int $depth) use (&$walk, &$maxDepth): void {
            $maxDepth = max($maxDepth, $depth);
            if (! is_array($chain)) {
                return;
            }
            foreach ($chain as $node) {
                if (! is_array($node)) {
                    continue;
                }
                if (! empty($node['children']) && is_array($node['children'])) {
                    $walk($node['children'], $depth + 1);
                }
            }
        };
        $walk($chains, 1);

        return $maxDepth > 0 ? $maxDepth : null;
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
