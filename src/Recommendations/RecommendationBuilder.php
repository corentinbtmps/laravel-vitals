<?php

declare(strict_types=1);

namespace LaravelVitals\Recommendations;

use LaravelVitals\Contracts\CodeAnalyzer;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\BackendTelemetry;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Support\CodeReferenceCollection;
use LaravelVitals\Support\LighthouseReport;

final readonly class RecommendationBuilder
{
    /**
     * @param iterable<int, CodeAnalyzer> $analyzers
     */
    public function __construct(
        private RecommendationRegistry $registry,
        private iterable $analyzers,
    ) {
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
        foreach ($this->analyzers as $analyzer) {
            if ($analyzer->supports($auditKey)) {
                $additional = $analyzer->analyze($auditKey, $auditData, $ctx);
                foreach ($additional->all() as $r) {
                    $refs = new CodeReferenceCollection([...$refs->all(), $r]);
                }
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
            'metrics'            => $this->paramsFor($auditKey, $auditData),
            'code_references'    => $refs->toArray(),
        ]);
    }

    /**
     * @param array<string, mixed> $auditData
     * @return array<string, mixed>
     */
    private function paramsFor(string $key, array $auditData): array
    {
        return match ($key) {
            'unused-javascript', 'unused-css-rules' => [
                'wasted_bytes' => $auditData['details']['items'][0]['wastedBytes'] ?? null,
            ],
            'n-plus-one-detected' => [
                'queries_count' => $auditData['queries_count'] ?? null,
            ],
            default => [],
        };
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
            'view_cached'     => false,
            'opcache_enabled' => function_exists('opcache_get_status') && (bool) @opcache_get_status(false),
            'slow_views'      => [],
        ];
    }
}
