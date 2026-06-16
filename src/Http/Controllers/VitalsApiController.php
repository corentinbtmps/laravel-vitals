<?php

declare(strict_types=1);

namespace LaravelVitals\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

/**
 * JSON API v1 — external consumers (Datadog, Sentry, custom dashboards).
 *
 * Auth: same `Authorize` middleware as the dashboard (`viewVitals` Gate).
 * Pagination: ?page=N&per_page=M (default 25, max 100).
 * Date filter: ?since=YYYY-MM-DD&until=YYYY-MM-DD.
 */
final class VitalsApiController
{
    /**
     * GET /vitals/api/v1/audits
     */
    public function audits(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $page    = max(1, (int) $request->query('page', 1));

        $query = Audit::query()
            ->with('url')
            ->where('status', AuditStatus::Completed)
            ->orderByDesc('completed_at');

        if ($since = $request->query('since')) {
            $query->where('completed_at', '>=', $since);
        }

        if ($until = $request->query('until')) {
            $query->where('completed_at', '<=', $until . ' 23:59:59');
        }

        $total   = $query->count();
        $audits  = $query->forPage($page, $perPage)->get();

        $data = $audits->map(fn (Audit $a): array => $this->formatAudit($a, $request))->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],
        ]);
    }

    /**
     * GET /vitals/api/v1/audits/{audit}
     */
    public function audit(Request $request, string $audit): JsonResponse
    {
        // Guard malformed ids: querying a uuid column with a non-uuid string
        // throws on strict drivers (PostgreSQL 22P02) instead of returning null.
        $record = Str::isUuid($audit)
            ? Audit::query()->with(['url', 'recommendations'])->find($audit)
            : null;

        if ($record === null) {
            return response()->json(
                ['error' => __('vitals::vitals.api.not_found')],
                404,
            );
        }

        return response()->json(['data' => $this->formatAudit($record, $request, detailed: true)]);
    }

    /**
     * GET /vitals/api/v1/urls
     */
    public function urls(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $page    = max(1, (int) $request->query('page', 1));

        $total = Url::query()->count();
        $urls  = Url::query()
            ->orderBy('label')
            ->forPage($page, $perPage)
            ->get();

        $data = $urls->map(fn (Url $u): array => [
            'id'      => $u->id,
            'label'   => $u->label,
            'path'    => $u->path,
            'device'  => $u->device->value,
            'enabled' => $u->enabled,
            '_links'  => [
                'html'        => url(route('vitals.url', $u->id, false)),
                'latest_audit' => url(route('vitals.api.url.latest', $u->id, false)),
            ],
        ])->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],
        ]);
    }

    /**
     * GET /vitals/api/v1/urls/{url}/latest
     */
    public function urlLatest(Request $request, string $url): JsonResponse
    {
        // url ids are integers; a non-numeric path segment crashes a strict
        // driver (PostgreSQL 22P02) rather than simply not matching.
        $urlRecord = ctype_digit($url)
            ? Url::query()->find($url)
            : null;

        if ($urlRecord === null) {
            return response()->json(
                ['error' => __('vitals::vitals.api.not_found')],
                404,
            );
        }

        $audit = Audit::query()
            ->with('url')
            ->where('url_id', $urlRecord->id)
            ->where('status', AuditStatus::Completed)
            ->orderByDesc('completed_at')
            ->first();

        if ($audit === null) {
            return response()->json(
                ['error' => __('vitals::vitals.api.no_audits')],
                404,
            );
        }

        return response()->json(['data' => $this->formatAudit($audit, $request)]);
    }

    /**
     * GET /vitals/api/v1/recommendations
     */
    public function recommendations(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $page    = max(1, (int) $request->query('page', 1));

        $query = Recommendation::query()
            ->with('audit.url')
            ->orderByDesc('created_at');

        if ($since = $request->query('since')) {
            $query->where('created_at', '>=', $since);
        }

        if ($until = $request->query('until')) {
            $query->where('created_at', '<=', $until . ' 23:59:59');
        }

        $total = $query->count();
        $recos = $query->forPage($page, $perPage)->get();

        $data = $recos->map(fn (Recommendation $r): array => [
            'id'          => $r->id,
            'audit_id'    => $r->audit_id,
            'url'         => $r->audit?->url ? [
                'id'    => $r->audit->url->id,
                'label' => $r->audit->url->label,
                'path'  => $r->audit->url->path,
            ] : null,
            'audit_key'   => $r->audit_key,
            'category'    => $r->category,
            'severity'    => $r->severity->value,
            'title'       => __($r->title_key, $r->translation_replace_params),
            'description' => __($r->description_key, $r->translation_replace_params),
            '_links'      => [
                'audit_html' => $r->audit_id ? url(route('vitals.audit', $r->audit_id, false)) : null,
            ],
        ])->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],
        ]);
    }

    /**
     * @param  bool  $detailed  include recommendations array
     * @return array<string, mixed>
     */
    private function formatAudit(Audit $audit, Request $request, bool $detailed = false): array
    {
        $base = [
            'id'                   => $audit->id,
            'url'                  => $audit->url ? [
                'id'    => $audit->url->id,
                'label' => $audit->url->label,
                'path'  => $audit->url->path,
            ] : null,
            'device'               => $audit->device->value,
            'score_performance'    => $audit->score_performance,
            'score_accessibility'  => $audit->score_accessibility,
            'score_best_practices' => $audit->score_best_practices,
            'score_seo'            => $audit->score_seo,
            'lcp_ms'               => $audit->lcp_ms !== null ? (float) $audit->lcp_ms : null,
            'inp_ms'               => $audit->inp_ms !== null ? (float) $audit->inp_ms : null,
            'cls'                  => $audit->cls !== null ? (float) $audit->cls : null,
            'ttfb_ms'              => $audit->ttfb_ms !== null ? (float) $audit->ttfb_ms : null,
            'fcp_ms'               => $audit->fcp_ms !== null ? (float) $audit->fcp_ms : null,
            'completed_at'         => $audit->completed_at?->toIso8601String(),
            '_links'               => [
                'self' => url(route('vitals.api.audit', $audit->id, false)),
                'html' => url(route('vitals.audit', $audit->id, false)),
            ],
        ];

        if ($detailed && $audit->relationLoaded('recommendations')) {
            $base['recommendations'] = $audit->recommendations->map(fn (Recommendation $r): array => [
                'id'        => $r->id,
                'audit_key' => $r->audit_key,
                'category'  => $r->category,
                'severity'  => $r->severity->value,
                'title'     => __($r->title_key, $r->translation_replace_params),
            ])->values()->all();
        }

        return $base;
    }
}
