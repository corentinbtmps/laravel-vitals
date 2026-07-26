<?php

declare(strict_types=1);

namespace LaravelVitals\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

/**
 * Lists the prioritised recommendations from a monitored URL's latest audit,
 * including the file:line code references the dashboard surfaces.
 */
final class ListRecommendationsTool extends Tool
{
    protected string $name = 'list_recommendations';

    protected string $description = 'List the actionable recommendations from a monitored URL\'s latest audit, most severe first, with their file:line code references.';

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'url'   => $schema->string()->required(),
            'limit' => $schema->integer()->default(20),
        ];
    }

    public function handle(Request $request): Response
    {
        /** @var array{url: string, limit?: int} $data */
        $data  = $request->validate(['url' => 'required|string', 'limit' => 'sometimes|integer|min:1|max:100']);
        $label = $data['url'];
        $limit = $data['limit'] ?? 20;

        $url = Url::query()->where('label', $label)->first();
        if ($url === null) {
            return Response::error("No monitored URL with label '{$label}'.");
        }

        /** @var Audit|null $audit */
        $audit = Audit::query()
            ->where('url_id', $url->id)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        if ($audit === null) {
            return Response::error("No completed audit for '{$label}' yet.");
        }

        $severityRank = ['critical' => 0, 'warning' => 1, 'info' => 2];

        $recommendations = $audit->recommendations()
            ->get()
            ->sortBy(fn (Recommendation $r): int => $severityRank[$r->severity->value] ?? 3)
            ->take($limit)
            ->map(fn (Recommendation $r): array => [
                'audit_key'       => $r->audit_key,
                'severity'        => $r->severity->value,
                'category'        => $r->category,
                'title'           => __($r->title_key, is_array($r->translation_params) ? $r->translation_params : []),
                'code_references' => array_map(
                    static fn (array $ref): array => [
                        'file' => $ref['file'] ?? null,
                        'line' => $ref['line'] ?? null,
                        'hint' => $ref['hint'] ?? null,
                    ],
                    is_array($r->code_references) ? $r->code_references : [],
                ),
            ])
            ->values()
            ->all();

        return Response::json([
            'url'             => $label,
            'audit_id'        => $audit->id,
            'count'           => count($recommendations),
            'recommendations' => $recommendations,
        ]);
    }
}
