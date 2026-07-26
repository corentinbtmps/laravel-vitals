<?php

declare(strict_types=1);

namespace LaravelVitals\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use LaravelVitals\Enums\Device;
use LaravelVitals\Vitals;

/**
 * Triggers a fresh, synchronous audit for a monitored URL and returns its scores.
 *
 * This is a write action (it runs Lighthouse and records an audit), so it can be
 * disabled with `vitals.mcp.allow_run_audit`.
 */
final class RunAuditTool extends Tool
{
    protected string $name = 'run_audit';

    protected string $description = 'Run a fresh audit for a monitored URL (by label) and return its scores. Synchronous — may take a few seconds.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'url'    => $schema->string()->required(),
            'device' => $schema->string()->enum(['mobile', 'desktop']),
        ];
    }

    public function handle(Request $request): Response
    {
        if (! (bool) config('vitals.mcp.allow_run_audit', true)) {
            return Response::error('Running audits via MCP is disabled (vitals.mcp.allow_run_audit).');
        }

        /** @var array{url: string, device?: string} $data */
        $data   = $request->validate([
            'url'    => 'required|string',
            'device' => 'sometimes|in:mobile,desktop',
        ]);
        $label  = $data['url'];
        $device = isset($data['device']) ? Device::from($data['device']) : null;

        try {
            $audit = app(Vitals::class)->audit($label, $device, sync: true);
        } catch (\Throwable $e) {
            return Response::error("Could not audit '{$label}': " . $e->getMessage());
        }

        $audit->refresh();

        return Response::json([
            'url'      => $label,
            'audit_id' => $audit->id,
            'status'   => $audit->status instanceof \BackedEnum ? $audit->status->value : $audit->status,
            'scores'   => [
                'performance'    => $audit->score_performance,
                'accessibility'  => $audit->score_accessibility,
                'best_practices' => $audit->score_best_practices,
                'seo'            => $audit->score_seo,
            ],
        ]);
    }
}
