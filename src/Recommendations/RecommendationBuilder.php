<?php

declare(strict_types=1);

namespace LaravelVitals\Recommendations;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Contracts\TelemetrySource;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Support\CodeReferenceCollection;
use LaravelVitals\Support\LighthouseReport;

final class RecommendationBuilder
{
    /** @var array<string, list<CodeAnalyzer>> */
    private array $analyzersByKey = [];

    /**
     * @param iterable<int, CodeAnalyzer> $analyzers
     * @param iterable<int, TelemetrySource> $sources
     */
    public function __construct(
        private readonly RecommendationRegistry $registry,
        iterable $analyzers,
        private readonly iterable $sources = [],
    ) {
        foreach ($analyzers as $analyzer) {
            foreach ($this->registry->allKeys() as $key) {
                if ($analyzer->supports($key)) {
                    $this->analyzersByKey[$key][] = $analyzer;
                }
            }
        }
    }

    public function buildFor(Audit $audit, LighthouseReport $report, ?BackendTelemetry $telemetry): void
    {
        $ctx = $this->buildContext($audit, $report);

        foreach ($report->audits as $entry) {
            $key = $entry['id'] ?? null;
            if (! is_string($key)) {
                continue;
            }
            $this->persist($audit, $key, $entry, $ctx);
        }

        if ($telemetry instanceof \LaravelVitals\Models\BackendTelemetry) {
            if ($telemetry->n_plus_one_suspect) {
                $this->persist($audit, 'n-plus-one-detected', [
                    'queries_count'  => $telemetry->queries_count,
                    'queries_unique' => $telemetry->queries_unique,
                    'top_patterns'   => $this->extractTopPatterns($telemetry),
                ], $ctx);
            }
            if (is_array($telemetry->slow_queries) && $telemetry->slow_queries !== []) {
                $this->persist($audit, 'slow-queries-detected', [
                    'slow_queries' => $telemetry->slow_queries,
                ], $ctx);
            }
        }

        foreach (['config-cache-disabled', 'route-cache-disabled', 'view-cache-disabled', 'debug-on-prod', 'opcache-disabled'] as $key) {
            $this->persist($audit, $key, [], $ctx);
        }

        foreach (['session-driver-file', 'cache-driver-file', 'queue-driver-sync-prod'] as $key) {
            $this->persist($audit, $key, [], $ctx);
        }

        foreach ($this->sources as $source) {
            if (! $source->isAvailable()) {
                continue;
            }

            $stats = $source->getTrendsFor($audit->url->path ?? '/');

            $syntheticTtfb = (float) ($audit->ttfb_ms ?? 0);
            if ($syntheticTtfb > 0 && $stats->p95Ttfb !== null && $stats->p95Ttfb > ($syntheticTtfb * 3)) {
                $this->persist($audit, 'real-world-perf-degraded', [
                    'synthetic_ttfb_ms' => $syntheticTtfb,
                    'p95_ttfb_ms'       => $stats->p95Ttfb,
                    'sample_count'      => $stats->sampleCount,
                ], $ctx);
                break; // one source's signal is enough
            }
        }

        $details = is_array($audit->details) ? $audit->details : [];

        if ($details !== []) {
            // 1. Excessive DOM size (> 1500 elements is Lighthouse's threshold for "warning")
            $domSize = (int) ($details['dom_size'] ?? 0);
            if ($domSize > 1500) {
                $this->persistDetail($audit, 'excessive-dom-size', [
                    'dom_size' => $domSize,
                ]);
            }

            // 2. Cache policy issues (any resource with TTL < 30 days)
            $shortCache = array_filter(
                $details['cache_policy'] ?? [],
                fn ($r): bool => is_array($r) && (int) ($r['ttl_seconds'] ?? 0) < 30 * 86400,
            );
            if (count($shortCache) > 0) {
                $this->persistDetail($audit, 'cache-policy-short', [
                    'count'    => count($shortCache),
                    'examples' => array_slice(array_map(fn (array $r) => $r['url'], array_values($shortCache)), 0, 3),
                ]);
            }

            // 3. Third-party with significant blocking time (> 250ms)
            $heavyTp = array_filter(
                $details['third_parties'] ?? [],
                fn ($t): bool => is_array($t) && (float) ($t['blocking_ms'] ?? 0) > 250,
            );
            if (count($heavyTp) > 0) {
                $entities = array_slice(array_map(fn (array $t) => $t['entity'], array_values($heavyTp)), 0, 3);
                $this->persistDetail($audit, 'third-party-blocking', [
                    'count'    => count($heavyTp),
                    'entities' => $entities,
                ]);
            }

            // 4. Large payload (> 2 MB total)
            $payload = (int) ($details['page_weight_bytes'] ?? 0);
            if ($payload > 2_000_000) {
                $this->persistDetail($audit, 'large-payload', [
                    'mb' => round($payload / 1_048_576, 1),
                ]);
            }

            // 5. High JS bootup time (any single script > 500ms)
            $heavyScripts = array_filter(
                $details['bootup_time'] ?? [],
                fn ($s): bool => is_array($s) && (float) ($s['total_ms'] ?? 0) > 500,
            );
            if (count($heavyScripts) > 0) {
                $worst = array_reduce(
                    $heavyScripts,
                    fn ($carry, $item) => is_array($carry) && (float) ($carry['total_ms'] ?? 0) > (float) ($item['total_ms'] ?? 0) ? $carry : $item,
                );
                $this->persistDetail($audit, 'bootup-time-high', [
                    'ms'  => (int) round((float) ($worst['total_ms'] ?? 0)),
                    'url' => (string) ($worst['url'] ?? '?'),
                ]);
            }
        }

        // Octane not running (best-effort detection — the package probably running Vitals isn't using Octane)
        $octaneRunning = $this->detectOctane();
        if (! $octaneRunning) {
            $this->persistDetail($audit, 'octane-not-running', []);
        }

        // Assets not hashed (Vite default produces hashed names; raw `app.js` without hash suggests no Vite or misconfig)
        if ($this->detectUnhashedAssets($report)) {
            $this->persistDetail($audit, 'assets-not-hashed', []);
        }
    }

    /**
     * Persist a detail-driven recommendation directly (bypasses analyzer code-ref checks).
     *
     * @param array<string, mixed> $auditData
     */
    private function persistDetail(Audit $audit, string $auditKey, array $auditData): void
    {
        $descriptor = $this->registry->get($auditKey);
        if (! $descriptor instanceof RecommendationDescriptor) {
            return;
        }

        Recommendation::create([
            'audit_id'           => $audit->id,
            'source'             => $descriptor->source,
            'audit_key'          => $auditKey,
            'category'           => $descriptor->category,
            'severity'           => $descriptor->severity,
            'title_key'          => $descriptor->titleKey,
            'description_key'    => $descriptor->descriptionKey,
            'translation_params' => $this->paramsFor($auditKey, $auditData),
            'metrics'            => $this->metricsFor($auditKey, $auditData),
            'code_references'    => [],
            'detail_items'       => null,
        ]);
    }

    /**
     * @param array<string, mixed> $auditData
     */
    private function persist(Audit $audit, string $auditKey, array $auditData, AppContext $ctx): void
    {
        $descriptor = $this->registry->get($auditKey);
        if (!$descriptor instanceof \LaravelVitals\Recommendations\RecommendationDescriptor) {
            return;
        }

        $refs = new CodeReferenceCollection();
        foreach ($this->analyzersByKey[$auditKey] ?? [] as $analyzer) {
            $additional = $analyzer->analyze($auditKey, $auditData, $ctx);
            foreach ($additional->all() as $r) {
                $refs = new CodeReferenceCollection([...$refs->all(), $r]);
            }
        }

        if (in_array($descriptor->source, ['config', 'static'], true) && $refs->count() === 0) {
            return;
        }

        Recommendation::create([
            'audit_id'           => $audit->id,
            'source'             => $descriptor->source,
            'audit_key'          => $auditKey,
            'category'           => $descriptor->category,
            'severity'           => $descriptor->severity,
            'title_key'          => $descriptor->titleKey,
            'description_key'    => $descriptor->descriptionKey,
            'translation_params' => $this->paramsFor($auditKey, $auditData),
            'metrics'            => $this->metricsFor($auditKey, $auditData),
            'code_references'    => $refs->toArray(),
            'detail_items'       => $this->extractDetailItems($auditKey, $auditData),
        ]);
    }

    /**
     * Translation parameters: only what we substitute into title/description strings.
     *
     * @param array<string, mixed> $auditData
     * @return array<string, mixed>
     */
    private function paramsFor(string $key, array $auditData): array
    {
        return match ($key) {
            'unused-javascript', 'unused-css-rules' => [
                'size' => isset($auditData['details']['items'][0]['wastedBytes'])
                    ? round(((int) $auditData['details']['items'][0]['wastedBytes']) / 1024) . ' KB'
                    : 'unknown',
            ],
            'n-plus-one-detected' => [
                'count'        => $auditData['queries_count'] ?? 0,
                'top_patterns' => $auditData['top_patterns'] ?? [],
            ],
            'excessive-dom-size' => [
                'count' => $auditData['dom_size'] ?? 0,
            ],
            'cache-policy-short' => [
                'count' => $auditData['count'] ?? 0,
            ],
            'third-party-blocking' => [
                'count'    => $auditData['count'] ?? 0,
                'entities' => is_array($auditData['entities'] ?? null) ? implode(', ', $auditData['entities']) : '',
            ],
            'large-payload' => [
                'mb' => $auditData['mb'] ?? 0,
            ],
            'bootup-time-high' => [
                'ms' => $auditData['ms'] ?? 0,
            ],
            default => [],
        };
    }

    /**
     * Raw measurable metrics: numeric values for charts and aggregations.
     *
     * @param array<string, mixed> $auditData
     * @return array<string, mixed>
     */
    private function metricsFor(string $key, array $auditData): array
    {
        return match ($key) {
            'unused-javascript', 'unused-css-rules' => [
                'wasted_bytes' => $auditData['details']['items'][0]['wastedBytes'] ?? null,
                'total_bytes'  => $auditData['details']['items'][0]['totalBytes'] ?? null,
            ],
            'n-plus-one-detected' => [
                'queries_count'  => $auditData['queries_count'] ?? null,
                'queries_unique' => $auditData['queries_unique'] ?? null,
            ],
            'real-world-perf-degraded' => [
                'synthetic_ttfb_ms' => $auditData['synthetic_ttfb_ms'] ?? null,
                'p95_ttfb_ms'       => $auditData['p95_ttfb_ms'] ?? null,
                'sample_count'      => $auditData['sample_count'] ?? null,
            ],
            'excessive-dom-size'    => ['dom_size'        => $auditData['dom_size'] ?? null],
            'cache-policy-short'    => ['count'           => $auditData['count'] ?? null],
            'third-party-blocking'  => ['count'           => $auditData['count'] ?? null],
            'large-payload'         => ['page_weight_mb'  => $auditData['mb'] ?? null],
            'bootup-time-high'      => ['bootup_ms'       => $auditData['ms'] ?? null],
            default => [],
        };
    }

    /**
     * Extract the most actionable detail items from a Lighthouse audit entry.
     *
     * Copies up to 10 items from audits[key].details.items, keeping only the
     * columns that are useful for the "What to fix" display: url, wastedBytes,
     * wastedMs, totalBytes. Returns null when no items are available.
     *
     * @param array<string, mixed> $auditData
     * @return array<int, array<string, mixed>>|null
     */
    private function extractDetailItems(string $auditKey, array $auditData): ?array
    {
        $rawItems = $auditData['details']['items'] ?? null;

        if (! is_array($rawItems) || $rawItems === []) {
            return null;
        }

        $extracted = [];

        foreach (array_slice($rawItems, 0, 10) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $entry = [];

            // URL — present in most audit items
            if (isset($item['url']) && is_string($item['url']) && $item['url'] !== '') {
                $entry['url'] = $item['url'];
            } elseif (isset($item['node']['snippet']) && is_string($item['node']['snippet'])) {
                // For DOM-based audits, use the element snippet as identifier
                $entry['url'] = $item['node']['snippet'];
            }

            // Wasted bytes
            if (isset($item['wastedBytes']) && (int) $item['wastedBytes'] > 0) {
                $entry['wasted_bytes'] = (int) $item['wastedBytes'];
            }

            // Wasted milliseconds (e.g. render-blocking-resources)
            if (isset($item['wastedMs']) && (float) $item['wastedMs'] > 0) {
                $entry['wasted_ms'] = (float) $item['wastedMs'];
            }

            // Total bytes
            if (isset($item['totalBytes']) && (int) $item['totalBytes'] > 0) {
                $entry['total_bytes'] = (int) $item['totalBytes'];
            }

            // For unused-css-rules, also capture selector / source map info
            if ($auditKey === 'unused-css-rules' && isset($item['label']) && is_string($item['label'])) {
                $entry['label'] = $item['label'];
            }

            if ($entry !== []) {
                $extracted[] = $entry;
            }
        }

        return $extracted !== [] ? $extracted : null;
    }

    /**
     * Best-effort detection: Octane runs the same PHP process across requests,
     * so we check the octane config key or the presence of the published config file.
     *
     * Most reliable: presence of octane.server config value or config/octane.php.
     * Returns true if Octane is configured / detected, false otherwise.
     */
    private function detectOctane(): bool
    {
        // When config is cached, config('octane.server') resolves the OCTANE_SERVER env value.
        $server = config('octane.server');
        if (is_string($server) && $server !== '') {
            return true;
        }

        // Check if octane config file exists (means it was at least published)
        if (file_exists(base_path('config/octane.php'))) {
            return true;
        }

        // Fall back to $_SERVER which is always available regardless of config caching
        $serverEnv = $_SERVER['OCTANE_SERVER'] ?? '';
        return is_string($serverEnv) && $serverEnv !== '';
    }

    /**
     * Heuristic: Vite-built assets contain a hash before extension (app.abc123.js).
     * Lighthouse's network-requests details list every loaded resource.
     * If JS/CSS files load without a hash pattern, likely no asset versioning.
     */
    private function detectUnhashedAssets(LighthouseReport $report): bool
    {
        // Use the raw audit data — already extracted in details by alpha.10's pipeline
        // but we re-parse to access network-requests if not in details.
        $detailRaw = $report->rawJson;
        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($detailRaw, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return false;
        }

        $items = $decoded['audits']['network-requests']['details']['items'] ?? [];
        if (! is_array($items)) {
            return false;
        }

        $jsCount = 0;
        $unhashedJs = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $url = (string) ($item['url'] ?? '');
            $type = (string) ($item['resourceType'] ?? '');

            if ($type !== 'Script' || $url === '') {
                continue;
            }
            if (str_starts_with($url, 'data:') || ! str_contains($url, '.js')) {
                continue;
            }

            $jsCount++;

            // Hashed assets have an 8+ hex/base64 sequence before .js (Vite default)
            // Patterns: app-abc12345.js, app.abc12345.js, app-Df8gK3p2.js
            if (! preg_match('/[a-zA-Z0-9_-]{8,}\.js(\?|$)/', basename(parse_url($url, PHP_URL_PATH) ?: ''))) {
                $unhashedJs++;
            }
        }

        // Flag only if at least 2 unhashed JS resources are loaded (1 might be intentional inline tag).
        return $jsCount >= 2 && $unhashedJs >= 2;
    }

    /**
     * From the queries_log, group by normalized SQL, count occurrences, and
     * return the top 3 repeated patterns with their occurrence count and
     * the first observed caller file:line.
     *
     * @return array<int, array{sql: string, occurrences: int, caller: string|null}>
     */
    private function extractTopPatterns(\LaravelVitals\Models\BackendTelemetry $telemetry): array
    {
        $log = $telemetry->queries_log;
        if (! is_array($log) || $log === []) {
            return [];
        }

        /** @var array<string, array{count: int, caller: string|null}> $grouped */
        $grouped = [];

        foreach ($log as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $sql = (string) ($entry['sql'] ?? '');
            if ($sql === '') {
                continue;
            }

            if (! isset($grouped[$sql])) {
                $caller = null;
                if (($entry['caller_file'] ?? null) !== null) {
                    $caller = $entry['caller_file'];
                    if (($entry['caller_line'] ?? null) !== null) {
                        $caller .= ':' . $entry['caller_line'];
                    }
                }
                $grouped[$sql] = ['count' => 0, 'caller' => $caller];
            }

            $grouped[$sql]['count']++;
        }

        // Sort by occurrences descending, take top 3 repeated patterns (count > 1)
        arsort($grouped);

        $result = [];
        foreach ($grouped as $sql => $data) {
            if ($data['count'] <= 1) {
                continue;
            }
            $result[] = [
                'sql'         => $sql,
                'occurrences' => $data['count'],
                'caller'      => $data['caller'],
            ];
            if (count($result) >= 3) {
                break;
            }
        }

        return $result;
    }

    private function buildContext(Audit $audit, LighthouseReport $report): AppContext
    {
        $assetUrls = [];
        foreach ($report->audits as $entry) {
            foreach ($entry['details']['items'] ?? [] as $item) {
                if (isset($item['url']) && is_string($item['url'])) {
                    $assetUrls[] = $item['url'];
                }
            }
        }

        return new AppContext(
            basePath: base_path(),
            auditedPath: $audit->url->path ?? '/',
            assetUrls: array_values(array_unique($assetUrls)),
            configSnapshot: $this->configSnapshot(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function configSnapshot(): array
    {
        return [
            'app_env'         => (string) config('app.env', 'production'),
            'app_debug'       => (bool) config('app.debug', false),
            'session_driver'  => (string) config('session.driver', 'file'),
            'cache_driver'    => (string) config('cache.default', 'file'),
            'queue_default'   => (string) config('queue.default', 'sync'),
            'config_cached'   => app()->configurationIsCached(),
            'route_cached'    => app()->routesAreCached(),
            'view_cached'     => $this->detectViewsCached(),
            'opcache_enabled' => function_exists('opcache_get_status') && (bool) @opcache_get_status(false),
            'slow_views'      => [],
        ];
    }

    private function detectViewsCached(): bool
    {
        $compiledPath = (string) config('view.compiled', '');
        if ($compiledPath === '' || ! is_dir($compiledPath)) {
            return false;
        }

        $files = @scandir($compiledPath) ?: [];
        foreach ($files as $file) {
            if (str_ends_with($file, '.php')) {
                return true;
            }
        }

        return false;
    }
}
