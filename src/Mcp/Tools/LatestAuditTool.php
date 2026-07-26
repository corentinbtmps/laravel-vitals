<?php

declare(strict_types=1);

namespace LaravelVitals\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;

/**
 * Returns the latest completed audit for a monitored URL: Lighthouse scores,
 * Core Web Vitals, and a snapshot of the backend telemetry.
 */
final class LatestAuditTool extends Tool
{
    protected string $name = 'latest_audit';

    protected string $description = 'Get the latest completed audit for a monitored URL (referenced by its label): Lighthouse scores, Core Web Vitals, and backend telemetry.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'url' => $schema->string()->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        /** @var array{url: string} $data */
        $data  = $request->validate(['url' => 'required|string']);
        $label = $data['url'];

        $url = Url::query()->where('label', $label)->first();
        if ($url === null) {
            return Response::error("No monitored URL with label '{$label}'. Labels are defined in config/vitals.php.");
        }

        /** @var Audit|null $audit */
        $audit = Audit::query()
            ->with('telemetry')
            ->where('url_id', $url->id)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        if ($audit === null) {
            return Response::error("No completed audit for '{$label}' yet. Run one with the run_audit tool.");
        }

        $telemetry = $audit->telemetry;

        return Response::json([
            'url'          => $label,
            'audit_id'     => $audit->id,
            'completed_at' => $audit->completed_at?->toIso8601String(),
            'device'       => $audit->device instanceof \BackedEnum ? $audit->device->value : $audit->device,
            'scores'       => [
                'performance'    => $audit->score_performance,
                'accessibility'  => $audit->score_accessibility,
                'best_practices' => $audit->score_best_practices,
                'seo'            => $audit->score_seo,
                'vitals_seo'     => $audit->vitals_seo_score,
            ],
            'core_web_vitals' => [
                'lcp_ms' => $audit->lcp_ms,
                'inp_ms' => $audit->inp_ms,
                'cls'    => $audit->cls,
                'ttfb_ms' => $audit->ttfb_ms,
            ],
            'backend' => $telemetry === null ? null : [
                'queries_count'      => $telemetry->queries_count,
                'queries_time_ms'    => $telemetry->queries_time_ms,
                'n_plus_one_suspect' => $telemetry->n_plus_one_suspect,
                'peak_memory_kb'     => $telemetry->memory_peak_kb,
            ],
        ]);
    }
}
