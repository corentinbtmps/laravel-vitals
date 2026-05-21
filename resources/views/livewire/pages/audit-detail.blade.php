@php
    $overallScore = (int) round((($audit->score_performance ?? 0) + ($audit->score_accessibility ?? 0) + ($audit->score_best_practices ?? 0) + ($audit->score_seo ?? 0)) / 4);
    $overallGrade = \LaravelVitals\Support\Health::grade($overallScore);
    $overallColor = \LaravelVitals\Support\Health::colorForScore($overallScore);
    $perfGrade    = $audit->performance_grade;
    $perfColor    = \LaravelVitals\Support\Health::colorForScore($audit->score_performance);

    $delta = function ($current, $prev) {
        if ($current === null || $prev === null) return null;
        $diff = (float) $current - (float) $prev;
        if ($diff == 0) return null;
        return ['diff' => $diff, 'is_better' => $diff > 0];
    };
@endphp

<div class="space-y-6">
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('vitals.urls') }}">URLs</flux:breadcrumbs.item>
        @if ($audit->url)
            <flux:breadcrumbs.item href="{{ route('vitals.url', $audit->url->id) }}">{{ $audit->url->label }}</flux:breadcrumbs.item>
        @endif
        <flux:breadcrumbs.item>audit · {{ $audit->completed_at?->format('M j, H:i') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Hero --}}
    <div class="rounded-3xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-8">
        <div class="flex items-start justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 text-sm text-ink-500">
                    <flux:icon.link class="size-4" />
                    <code class="text-ink-700 dark:text-ink-300">{{ $audit->url?->path }}</code>
                </div>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $audit->url?->label }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-ink-500">
                    <span class="inline-flex items-center gap-1.5">
                        <flux:icon name="{{ $audit->device === 'mobile' ? 'device-phone-mobile' : 'computer-desktop' }}" class="size-4" />
                        {{ $audit->device }}
                    </span>
                    <span>·</span>
                    <span class="inline-flex items-center gap-1.5">
                        <flux:icon.clock class="size-4" />
                        {{ $audit->completed_at?->toDayDateTimeString() }}
                    </span>
                    <span>·</span>
                    <flux:badge color="zinc" size="sm">{{ $audit->driver }}</flux:badge>
                </div>
            </div>
            <div class="text-right shrink-0 flex items-start gap-4">
                {{-- Performance grade --}}
                @if ($perfGrade !== null)
                    <div class="text-center">
                        <div class="text-xs font-semibold uppercase tracking-wide text-ink-400 mb-1">{{ __('vitals::vitals.tables.perf_grade') }}</div>
                        <div @class([
                            'text-5xl font-semibold tabular-nums leading-none',
                            'text-emerald-500' => $perfColor === 'emerald',
                            'text-amber-500'   => $perfColor === 'amber',
                            'text-accent-500'  => $perfColor === 'accent',
                            'text-ink-400'     => $perfColor === 'ink',
                        ])>{{ $perfGrade }}</div>
                        <div class="mt-1 text-xl font-semibold tabular-nums text-ink-500">{{ $audit->score_performance }}<span class="text-sm font-normal">/100</span></div>
                    </div>
                    <div class="w-px bg-ink-200/60 dark:bg-ink-700/60 self-stretch mx-1"></div>
                @endif
                {{-- Overall grade --}}
                <div class="text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-ink-400 mb-1">{{ __('vitals::vitals.tables.global') }}</div>
                    <div @class([
                        'text-6xl font-semibold tabular-nums leading-none',
                        'text-emerald-500' => $overallColor === 'emerald',
                        'text-amber-500'   => $overallColor === 'amber',
                        'text-accent-500'  => $overallColor === 'accent',
                        'text-ink-400'     => $overallColor === 'ink',
                    ])>{{ $overallGrade }}</div>
                    <div class="mt-1 text-2xl font-semibold tabular-nums text-ink-500">{{ $overallScore }}<span class="text-base font-normal">/100</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Score breakdown: 4 cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            'score_performance'    => ['label' => __('vitals::vitals.compare.performance'),    'icon' => 'bolt'],
            'score_accessibility'  => ['label' => __('vitals::vitals.compare.accessibility'),  'icon' => 'eye'],
            'score_best_practices' => ['label' => __('vitals::vitals.compare.best_practices'), 'icon' => 'shield-check'],
            'score_seo'            => ['label' => __('vitals::vitals.compare.seo'),            'icon' => 'magnifying-glass'],
        ] as $col => $meta)
            @php
                $value = $audit->{$col};
                $color = \LaravelVitals\Support\Health::colorForScore($value);
                $axisGrade = $value !== null ? \LaravelVitals\Support\Health::grade($value) : null;
            @endphp
            <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-4 relative overflow-hidden">
                <div @class([
                    'absolute top-0 left-0 right-0 h-0.5 rounded-t-2xl',
                    'bg-emerald-500' => $color === 'emerald',
                    'bg-amber-500'   => $color === 'amber',
                    'bg-accent-500'  => $color === 'accent',
                    'bg-ink-400'     => $color === 'ink',
                ])></div>
                <div class="flex items-center justify-between gap-2 text-xs text-ink-500 mt-1">
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="{{ $meta['icon'] }}" class="size-3.5" />
                        <flux:tooltip :content="__('vitals::vitals.tooltip.score_label', ['label' => $meta['label']])">
                            <span class="cursor-help underline decoration-dotted decoration-ink-300 dark:decoration-ink-700 underline-offset-2">{{ $meta['label'] }}</span>
                        </flux:tooltip>
                    </div>
                    @if ($axisGrade !== null)
                        <span @class([
                            'font-bold text-sm leading-none',
                            'text-emerald-600 dark:text-emerald-400' => $color === 'emerald',
                            'text-amber-600 dark:text-amber-400'     => $color === 'amber',
                            'text-accent-600 dark:text-accent-400'   => $color === 'accent',
                            'text-ink-400'                           => $color === 'ink',
                        ])>{{ $axisGrade }}</span>
                    @endif
                </div>
                @if ($value !== null)
                    @php
                        $chartId    = 'score-' . $col . '-' . uniqid();
                        $chartColor = match ($color) {
                            'emerald' => '#10b981',
                            'amber'   => '#f59e0b',
                            'accent'  => '#f43f5e',
                            default   => '#71717a',
                        };
                        $trackColor = match ($color) {
                            'emerald' => '#d1fae5',
                            'amber'   => '#fef3c7',
                            'accent'  => '#ffe4e6',
                            default   => '#e4e4e7',
                        };
                        $labelColor = match ($color) {
                            'emerald' => '#059669',
                            'amber'   => '#d97706',
                            'accent'  => '#e11d48',
                            default   => '#52525b',
                        };
                    @endphp
                    <div id="{{ $chartId }}" class="mt-1 -mx-1"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            new ApexCharts(document.querySelector('#{{ $chartId }}'), {
                                chart: { type: 'radialBar', height: 140, sparkline: { enabled: true }, animations: { enabled: false } },
                                series: [{{ (int) $value }}],
                                labels: [@json($meta['label'])],
                                colors: ['{{ $chartColor }}'],
                                plotOptions: {
                                    radialBar: {
                                        hollow: { size: '62%' },
                                        track: { background: '{{ $trackColor }}' },
                                        dataLabels: {
                                            name: { show: false },
                                            value: { show: true, fontSize: '24px', fontWeight: 700, offsetY: 8, color: '{{ $labelColor }}' },
                                        },
                                    },
                                },
                            }).render();
                        });
                    </script>
                    @php
                        $prevValue = $previous?->{$col};
                        $scoreDelta = $value !== null && $prevValue !== null ? (int) $value - (int) $prevValue : null;
                    @endphp
                    @if ($scoreDelta !== null && $scoreDelta !== 0)
                        <div class="mt-2 flex items-center justify-center gap-1 text-xs">
                            @if ($scoreDelta > 0)
                                <flux:icon.arrow-trending-up class="size-3 text-emerald-500" />
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">+{{ $scoreDelta }}</span>
                            @else
                                <flux:icon.arrow-trending-down class="size-3 text-accent-500" />
                                <span class="text-accent-600 dark:text-accent-400 font-medium">{{ $scoreDelta }}</span>
                            @endif
                            <span class="text-ink-400">{{ __('vitals::vitals.audit_detail.vs_prev') }}</span>
                        </div>
                    @endif
                @else
                    <div class="mt-2 text-3xl font-semibold tabular-nums text-ink-400">—</div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Core Web Vitals --}}
    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon.heart class="size-5 text-accent-500" />
            <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.cwv_title') }}</h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['col' => 'lcp_ms',  'label' => 'LCP',  'unit' => 'ms', 'desc' => 'Largest Contentful Paint',
                 'tooltip_key' => 'vitals::vitals.tooltip.cwv_lcp',
                 'doc' => 'https://web.dev/articles/lcp'],
                ['col' => 'cls',     'label' => 'CLS',  'unit' => '',   'desc' => 'Cumulative Layout Shift',
                 'tooltip_key' => 'vitals::vitals.tooltip.cwv_cls',
                 'doc' => 'https://web.dev/articles/cls'],
                ['col' => 'inp_ms',  'label' => 'INP',  'unit' => 'ms', 'desc' => 'Interaction to Next Paint',
                 'tooltip_key' => 'vitals::vitals.tooltip.cwv_inp',
                 'doc' => 'https://web.dev/articles/inp'],
                ['col' => 'ttfb_ms', 'label' => 'TTFB', 'unit' => 'ms', 'desc' => 'Time to First Byte',
                 'tooltip_key' => 'vitals::vitals.tooltip.cwv_ttfb',
                 'doc' => 'https://web.dev/articles/ttfb'],
                // Note: 'desc' is the technical abbreviation expansion shown inline — intentionally not translated
            ] as $cwv)
                @php
                    $val = $audit->{$cwv['col']};
                    $valNumeric = $val !== null ? (float) $val : null;
                    $status = \LaravelVitals\Support\Health::cwvStatus($cwv['col'], $valNumeric);
                    $color = \LaravelVitals\Support\Health::colorForStatus($status);
                    $icon  = \LaravelVitals\Support\Health::iconForStatus($status);
                @endphp
                <div @class([
                    'rounded-lg p-4',
                    'border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/40 dark:bg-emerald-900/10' => $color === 'emerald',
                    'border border-amber-200 dark:border-amber-900/40 bg-amber-50/40 dark:bg-amber-900/10'         => $color === 'amber',
                    'border border-accent-200 dark:border-accent-900/40 bg-accent-50/40 dark:bg-accent-900/10'     => $color === 'accent',
                    'border border-ink-200 dark:border-ink-900/40 bg-ink-50/40 dark:bg-ink-900/10'                 => $color === 'ink',
                ])>
                    <div class="flex items-center justify-between mb-2">
                        <flux:tooltip :content="__($cwv['tooltip_key'])">
                            <span class="text-xs font-semibold text-ink-500 cursor-help underline decoration-dotted decoration-ink-300 dark:decoration-ink-700 underline-offset-2">{{ $cwv['label'] }}</span>
                        </flux:tooltip>
                        <flux:icon name="{{ $icon }}" @class([
                            'size-4',
                            'text-emerald-500' => $color === 'emerald',
                            'text-amber-500'   => $color === 'amber',
                            'text-accent-500'  => $color === 'accent',
                            'text-ink-400'     => $color === 'ink',
                        ]) />
                    </div>
                    <div @class([
                        'text-2xl font-bold',
                        'text-emerald-700 dark:text-emerald-300' => $color === 'emerald',
                        'text-amber-700 dark:text-amber-300'     => $color === 'amber',
                        'text-accent-700 dark:text-accent-300'   => $color === 'accent',
                        'text-ink-700 dark:text-ink-300'         => $color === 'ink',
                    ])>
                        @if ($valNumeric !== null)
                            {{ $cwv['col'] === 'cls' ? number_format($valNumeric, 2) : (int) round($valNumeric) }}{{ $cwv['unit'] }}
                        @else
                            —
                        @endif
                    </div>
                    <div class="mt-1 text-[11px] text-ink-500">{{ $cwv['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Front-end ↔ Back-end correlation panel --}}
    @if ($audit->telemetry && $breakdown['lcp_ms'] !== null && $breakdown['ttfb_ms'] !== null)
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.signal class="size-5 text-sky-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.frontend_backend_title') }}</h2>
            </div>

            {{-- Stacked horizontal bar --}}
            <div class="space-y-3">
                <div class="flex items-baseline justify-between">
                    <span class="text-sm text-ink-500">{{ __('vitals::vitals.audit_detail.lcp_composition') }}</span>
                    <span class="text-sm font-semibold">{{ (int) round($breakdown['lcp_ms']) }}ms total</span>
                </div>
                <div class="h-8 w-full bg-ink-100 dark:bg-ink-800 rounded-md overflow-hidden flex">
                    <div class="h-full bg-sky-500 flex items-center justify-center text-xs text-white font-medium" style="width: {{ $breakdown['ttfb_share'] }}%">
                        @if ($breakdown['ttfb_share'] >= 15)
                            backend {{ (int) round($breakdown['ttfb_ms']) }}ms ({{ $breakdown['ttfb_share'] }}%)
                        @endif
                    </div>
                    <div class="h-full bg-violet-500 flex items-center justify-center text-xs text-white font-medium" style="width: {{ 100 - $breakdown['ttfb_share'] }}%">
                        @if ((100 - $breakdown['ttfb_share']) >= 15)
                            frontend {{ (int) round($breakdown['render_ms']) }}ms ({{ 100 - $breakdown['ttfb_share'] }}%)
                        @endif
                    </div>
                </div>

                @if ($isBackendBound)
                    <flux:callout variant="warning" icon="cpu-chip">
                        <flux:callout.heading>{{ __('vitals::vitals.audit_detail.backend_bottleneck') }}</flux:callout.heading>
                        <flux:callout.text>
                            {{ __('vitals::vitals.audit_detail.backend_bottleneck_body', ['pct' => $breakdown['ttfb_share']]) }}
                        </flux:callout.text>
                    </flux:callout>
                @endif

                @if ($audit->telemetry->n_plus_one_suspect)
                    <flux:callout variant="danger" icon="circle-stack">
                        <flux:callout.heading>{{ __('vitals::vitals.audit_detail.nplus1_heading') }}</flux:callout.heading>
                        <flux:callout.text>
                            {{ __('vitals::vitals.audit_detail.nplus1_body', ['queries' => $audit->telemetry->queries_count, 'time' => (int) round((float) $audit->telemetry->queries_time_ms), 'unique' => $audit->telemetry->queries_unique]) }}
                            @if ($estimatedGain !== null)
                                {{ __('vitals::vitals.audit_detail.nplus1_gain', ['gain' => $estimatedGain]) }}
                            @endif

                            @php
                                $topPatterns = [];
                                if (is_array($audit->telemetry->queries_log)) {
                                    // Build patterns directly from queries_log stored on telemetry
                                    $grouped = [];
                                    foreach ($audit->telemetry->queries_log as $entry) {
                                        if (! is_array($entry)) continue;
                                        $sql = (string) ($entry['sql'] ?? '');
                                        if ($sql === '') continue;
                                        if (! isset($grouped[$sql])) {
                                            $caller = null;
                                            if (($entry['caller_file'] ?? null) !== null) {
                                                $caller = $entry['caller_file'];
                                                if (($entry['caller_line'] ?? null) !== null) $caller .= ':' . $entry['caller_line'];
                                            }
                                            $grouped[$sql] = ['count' => 0, 'caller' => $caller];
                                        }
                                        $grouped[$sql]['count']++;
                                    }
                                    arsort($grouped);
                                    foreach ($grouped as $sql => $data) {
                                        if ($data['count'] <= 1) continue;
                                        $topPatterns[] = ['sql' => $sql, 'occurrences' => $data['count'], 'caller' => $data['caller']];
                                        if (count($topPatterns) >= 3) break;
                                    }
                                }
                            @endphp
                            @if (! empty($topPatterns))
                                <div class="mt-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-500 mb-2">{{ __('vitals::vitals.audit_detail.repeated_queries') }}</p>
                                    <ul class="space-y-2">
                                        @foreach ($topPatterns as $pattern)
                                            <li class="rounded-lg border border-ink-200 dark:border-ink-800 bg-canvas dark:bg-ink-950 p-3">
                                                <code class="text-xs font-mono text-accent-700 dark:text-accent-300 break-all">{{ $pattern['sql'] }}</code>
                                                <div class="mt-1 flex items-center gap-3 text-xs text-ink-500">
                                                    <span class="tabular-nums font-medium">×{{ $pattern['occurrences'] }}</span>
                                                    @if (! empty($pattern['caller']))
                                                        @php
                                                            [$callerFile, $callerLine] = array_pad(explode(':', $pattern['caller'], 2), 2, null);
                                                            $callerEditor = \LaravelVitals\Support\EditorUrl::for($callerFile, $callerLine ? (int) $callerLine : null);
                                                        @endphp
                                                        @if ($callerEditor)
                                                            <a href="{{ $callerEditor }}" class="inline-flex items-center gap-1 text-[11px] font-medium text-accent-600 dark:text-accent-400 hover:text-accent-700 dark:hover:text-accent-300 underline decoration-accent-500/40 hover:decoration-accent-500 decoration-1 underline-offset-2 transition-colors" title="{{ __('vitals::vitals.actions.open_in_editor') }}"><code>{{ $pattern['caller'] }}</code><flux:icon name="arrow-top-right-on-square" class="size-3" /></a>
                                                        @else
                                                            <code class="text-[11px] text-ink-500">{{ $pattern['caller'] }}</code>
                                                        @endif
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </flux:callout.text>
                    </flux:callout>
                @endif
            </div>
        </div>
    @endif

    {{-- Backend telemetry stats --}}
    @if ($audit->telemetry)
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="server-stack" class="size-5 text-violet-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.telemetry_title') }}</h2>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div>
                    <div class="text-xs text-ink-500 mb-1">{{ __('vitals::vitals.audit_detail.queries') }}</div>
                    <div class="text-xl font-bold flex items-baseline gap-1">
                        {{ $audit->telemetry->queries_count }}
                        <span class="text-xs text-ink-400">({{ $audit->telemetry->queries_unique }} {{ __('vitals::vitals.audit_detail.unique') }})</span>
                    </div>
                </div>
                <div>
                    <div class="text-xs text-ink-500 mb-1">{{ __('vitals::vitals.audit_detail.query_time') }}</div>
                    <div class="text-xl font-bold">{{ (int) round((float) $audit->telemetry->queries_time_ms) }}ms</div>
                </div>
                <div>
                    <div class="text-xs text-ink-500 mb-1">{{ __('vitals::vitals.audit_detail.memory_peak') }}</div>
                    <div class="text-xl font-bold">{{ number_format($audit->telemetry->memory_peak_kb / 1024, 1) }}MB</div>
                </div>
                <div>
                    <div class="text-xs text-ink-500 mb-1">{{ __('vitals::vitals.audit_detail.cache_hit_rate') }}</div>
                    @php
                        $hits = (int) $audit->telemetry->cache_hits;
                        $misses = (int) $audit->telemetry->cache_misses;
                        $total = $hits + $misses;
                        $rate = $total > 0 ? round(($hits / $total) * 100) : null;
                    @endphp
                    <div class="text-xl font-bold">{{ $rate !== null ? $rate . '%' : '—' }}</div>
                </div>
            </div>

            @if (! empty($audit->telemetry->slow_queries))
                <div class="mt-6">
                    <div class="text-sm font-semibold text-ink-700 dark:text-ink-300 mb-2">{{ __('vitals::vitals.audit_detail.slowest_queries') }}</div>
                    <div class="space-y-2">
                        @foreach (array_slice($audit->telemetry->slow_queries, 0, 5) as $q)
                            <div class="rounded border border-ink-200 dark:border-ink-800 bg-ink-50 dark:bg-ink-900 p-3">
                                <div class="flex items-baseline justify-between gap-3 mb-1">
                                    <code class="text-xs text-ink-700 dark:text-ink-300 truncate flex-1">{{ $q['sql'] ?? '' }}</code>
                                    <flux:badge color="rose" size="sm">{{ (int) round((float) ($q['time_ms'] ?? 0)) }}ms</flux:badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Recommendations grouped by category --}}
    @if ($groupedRecos->isNotEmpty())
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <flux:icon.light-bulb class="size-5 text-amber-500" />
                    <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.recommendations_title') }}</h2>
                </div>
                <flux:badge color="amber">{{ $audit->recommendations->count() }}</flux:badge>
            </div>

            <div class="space-y-6">
                @foreach (['performance', 'accessibility', 'best_practices', 'seo'] as $category)
                    @if ($groupedRecos->has($category))
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-ink-500 mb-3">{{ str_replace('_', ' ', $category) }}</h3>
                            <div class="space-y-3">
                                @foreach ($groupedRecos[$category] as $reco)
                                    @php
                                        $sev          = $reco->severity;
                                        $sevFluxColor = $sev->fluxBadgeColor();
                                        $sevIcon      = $sev->fluxCalloutIcon();
                                    @endphp
                                    <div @class([
                                        'rounded-lg border p-4',
                                        ...$sev->containerClasses(),
                                    ])>
                                        <div class="flex items-start gap-3">
                                            <flux:icon name="{{ $sevIcon }}" @class([
                                                'size-5 shrink-0 mt-0.5',
                                                $sev->iconTextColor(),
                                            ]) />
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                    <h4 class="font-semibold">{{ __($reco->title_key, $reco->translation_replace_params) }}</h4>
                                                    <flux:badge color="{{ $sevFluxColor }}" size="sm">{{ $reco->severity->label() }}</flux:badge>
                                                    <flux:button href="{{ route('vitals.issue.detail', ['auditKey' => $reco->audit_key]) }}" variant="ghost" size="xs" icon="map-pin" class="ml-auto">{{ __('vitals::vitals.issue_detail.view_all_occurrences') }}</flux:button>
                                                </div>
                                                <p class="text-sm text-ink-500 dark:text-ink-400">{{ __($reco->description_key, $reco->translation_replace_params) }}</p>

                                                @php $docs = \LaravelVitals\Recommendations\RecommendationDocs::for($reco->audit_key); @endphp

                                                @if ($docs)
                                                    {{-- Why it matters --}}
                                                    <div class="mt-3 flex items-start gap-2 text-sm">
                                                        <flux:icon.information-circle class="size-4 text-sky-500 shrink-0 mt-0.5" />
                                                        <p class="text-ink-700 dark:text-ink-300">{{ $docs['why'] }}</p>
                                                    </div>

                                                    {{-- Estimated impact --}}
                                                    @if (! empty($docs['impact']))
                                                        <div class="mt-2 flex items-center gap-2 text-xs">
                                                            <flux:icon.bolt class="size-3.5 text-amber-500" />
                                                            <span class="text-amber-700 dark:text-amber-400 font-medium">{{ $docs['impact'] }}</span>
                                                        </div>
                                                    @endif

                                                    {{-- Doc links as buttons --}}
                                                    @if (! empty($docs['docs']))
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @foreach ($docs['docs'] as $doc)
                                                                <flux:button
                                                                    href="{{ $doc['url'] }}"
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    size="sm"
                                                                    variant="ghost"
                                                                    icon="arrow-top-right-on-square"
                                                                >{{ $doc['label'] }}</flux:button>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    {{-- Good vs bad code examples --}}
                                                    @if (! empty($docs['good']) || ! empty($docs['bad']))
                                                        <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-3">
                                                            @if (! empty($docs['good']))
                                                                <div class="rounded border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/40 dark:bg-emerald-900/10 overflow-hidden">
                                                                    <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 border-b border-emerald-200 dark:border-emerald-900/40">
                                                                        <flux:icon.check-circle class="size-3.5" />
                                                                        {{ __('vitals::vitals.audit_detail.recommended') }}
                                                                    </div>
                                                                    <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-emerald-800 dark:text-emerald-200">{{ $docs['good'] }}</code></pre>
                                                                </div>
                                                            @endif
                                                            @if (! empty($docs['bad']))
                                                                <div class="rounded border border-accent-200 dark:border-accent-900/40 bg-accent-50/40 dark:bg-accent-900/10 overflow-hidden">
                                                                    <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-accent-700 dark:text-accent-300 border-b border-accent-200 dark:border-accent-900/40">
                                                                        <flux:icon.x-circle class="size-3.5" />
                                                                        {{ __('vitals::vitals.audit_detail.avoid') }}
                                                                    </div>
                                                                    <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-accent-800 dark:text-accent-200">{{ $docs['bad'] }}</code></pre>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif

                                                {{-- N+1 repeated query patterns --}}
                                                @if ($reco->audit_key === 'n-plus-one-detected' && ! empty($reco->translation_params['top_patterns'] ?? null))
                                                    <div class="mt-4">
                                                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-500 mb-2">{{ __('vitals::vitals.audit_detail.repeated_queries') }}</p>
                                                        <ul class="space-y-2">
                                                            @foreach ($reco->translation_params['top_patterns'] as $pattern)
                                                                <li class="rounded-lg border border-ink-200 dark:border-ink-800 bg-canvas dark:bg-ink-950 p-3">
                                                                    <code class="text-xs font-mono text-accent-700 dark:text-accent-300 break-all">{{ $pattern['sql'] }}</code>
                                                                    <div class="mt-1 flex items-center gap-3 text-xs text-ink-500">
                                                                        <span class="tabular-nums font-medium">×{{ $pattern['occurrences'] }}</span>
                                                                        @if (! empty($pattern['caller']))
                                                                            @php
                                                                                [$callerFile, $callerLine] = array_pad(explode(':', $pattern['caller'], 2), 2, null);
                                                                                $callerEditor = \LaravelVitals\Support\EditorUrl::for($callerFile, $callerLine ? (int) $callerLine : null);
                                                                            @endphp
                                                                            @if ($callerEditor)
                                                                                <a href="{{ $callerEditor }}" class="inline-flex items-center gap-1 text-[11px] font-medium text-accent-600 dark:text-accent-400 hover:text-accent-700 dark:hover:text-accent-300 underline decoration-accent-500/40 hover:decoration-accent-500 decoration-1 underline-offset-2 transition-colors" title="{{ __('vitals::vitals.actions.open_in_editor') }}"><code>{{ $pattern['caller'] }}</code><flux:icon name="arrow-top-right-on-square" class="size-3" /></a>
                                                                            @else
                                                                                <code class="text-[11px] text-ink-500">{{ $pattern['caller'] }}</code>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                                {{-- Code references in user's app --}}
                                                @if (! empty($reco->code_references))
                                                    <div class="mt-4">
                                                        <div class="flex items-center gap-1.5 text-xs font-medium text-ink-500 dark:text-ink-400 mb-2">
                                                            <flux:icon name="code-bracket" class="size-3.5" />
                                                            {{ __('vitals::vitals.audit_detail.found_in_app') }}
                                                        </div>
                                                        <div class="space-y-2">
                                                            @foreach ($reco->code_references as $ref)
                                                                <x-vitals::code-reference :ref="$ref" />
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Panel A: Page details --}}
    @if ($audit->details)
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="document-magnifying-glass" class="size-5 text-violet-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.page_details_title') }}</h2>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-ink-500 mb-1">{{ __('vitals::vitals.audit_detail.page_weight') }}</div>
                    <div class="text-2xl font-bold">
                        @if (! empty($audit->details['page_weight_bytes']))
                            {{ number_format($audit->details['page_weight_bytes'] / 1024, 0) }}
                            <span class="text-sm text-ink-400">KB</span>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-ink-500 mb-1">{{ __('vitals::vitals.audit_detail.http_requests') }}</div>
                    <div class="text-2xl font-bold">{{ $audit->details['request_count'] ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-ink-500 mb-1">{{ __('vitals::vitals.audit_detail.dom_elements') }}</div>
                    @php $domSize = $audit->details['dom_size'] ?? null; @endphp
                    <div @class([
                        'text-2xl font-bold',
                        'text-amber-600 dark:text-amber-400' => $domSize !== null && $domSize > 1500,
                    ])>
                        {{ $domSize !== null ? number_format($domSize) : '—' }}
                        @if ($domSize !== null && $domSize > 1500)
                            <flux:icon.exclamation-triangle class="inline size-4 text-amber-500" />
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-ink-500 mb-1">{{ __('vitals::vitals.audit_detail.render_blocking') }}</div>
                    @php $rbt = $audit->details['render_blocking_time_ms'] ?? null; @endphp
                    <div @class([
                        'text-2xl font-bold',
                        'text-accent-600 dark:text-accent-400' => $rbt !== null && $rbt > 300,
                    ])>
                        {{ $rbt !== null ? (int) round($rbt) . 'ms' : '—' }}
                    </div>
                </div>
            </div>

            @if (! empty($audit->details['lcp_element']['selector']))
                <div class="mt-6 pt-4 border-t border-ink-200 dark:border-ink-800">
                    <div class="text-xs text-ink-500 mb-2 flex items-center gap-1.5">
                        <flux:icon.heart class="size-3.5 text-accent-500" /> {{ __('vitals::vitals.audit_detail.lcp_element') }}
                    </div>
                    <code class="block text-xs bg-ink-50 dark:bg-ink-900 p-2 rounded border border-ink-200 dark:border-ink-800 overflow-x-auto">{{ $audit->details['lcp_element']['selector'] }}</code>
                    @if (! empty($audit->details['lcp_element']['snippet']))
                        <code class="block text-xs bg-ink-50 dark:bg-ink-900 p-2 rounded border border-ink-200 dark:border-ink-800 mt-1.5 overflow-x-auto">{{ $audit->details['lcp_element']['snippet'] }}</code>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Panel B: Resource breakdown --}}
    @if (! empty($audit->details['resource_summary']))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.archive-box class="size-5 text-sky-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.resource_breakdown') }}</h2>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                @php
                    $resourceColors = [
                        'script' => '#f43f5e', 'image' => '#0ea5e9', 'stylesheet' => '#10b981',
                        'font' => '#a855f7', 'document' => '#71717a', 'media' => '#f59e0b',
                        'other' => '#737373', 'third-party' => '#ec4899',
                    ];
                    $resourceData = collect($audit->details['resource_summary'])
                        ->map(fn ($r) => ['type' => $r['type'], 'bytes' => $r['bytes']])
                        ->filter(fn ($r) => $r['bytes'] > 0)
                        ->values();
                    $chartLabels = $resourceData->pluck('type')->all();
                    $chartValues = $resourceData->pluck('bytes')->all();
                    $chartColors = $resourceData->pluck('type')->map(fn ($t) => $resourceColors[$t] ?? '#737373')->all();
                @endphp

                <div id="resource-pie-chart"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        new ApexCharts(document.querySelector('#resource-pie-chart'), {
                            chart: { type: 'donut', height: 240, animations: { enabled: false } },
                            series: @json($chartValues),
                            labels: @json($chartLabels),
                            colors: @json($chartColors),
                            legend: { position: 'right', fontSize: '12px' },
                            plotOptions: { pie: { donut: { size: '60%' } } },
                            tooltip: { y: { formatter: (v) => (v / 1024).toFixed(0) + ' KB' } },
                            dataLabels: { enabled: false },
                        }).render();
                    });
                </script>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('vitals::vitals.tables.type') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('vitals::vitals.tables.count') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('vitals::vitals.tables.size') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($audit->details['resource_summary'] as $row)
                            <flux:table.row>
                                <flux:table.cell variant="strong" class="capitalize">{{ $row['type'] }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $row['count'] }}</flux:table.cell>
                                <flux:table.cell align="end">{{ number_format($row['bytes'] / 1024, 0) }} KB</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    @endif

    {{-- Panel C: Third-party impact --}}
    @if (! empty($audit->details['third_parties']))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.globe-alt class="size-5 text-pink-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.third_party_title') }}</h2>
                <flux:badge color="pink" size="sm">{{ count($audit->details['third_parties']) }}</flux:badge>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('vitals::vitals.tables.entity') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('vitals::vitals.tables.transfer') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('vitals::vitals.tables.blocking') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('vitals::vitals.tables.main_thread') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($audit->details['third_parties'] as $tp)
                        @php $blockingHigh = ($tp['blocking_ms'] ?? 0) > 250; @endphp
                        <flux:table.row>
                            <flux:table.cell variant="strong">{{ $tp['entity'] }}</flux:table.cell>
                            <flux:table.cell align="end">{{ number_format(($tp['transfer_bytes'] ?? 0) / 1024, 0) }} KB</flux:table.cell>
                            <flux:table.cell align="end">
                                @if ($blockingHigh)
                                    <flux:badge color="rose" size="sm">{{ (int) round($tp['blocking_ms']) }}ms</flux:badge>
                                @else
                                    <span>{{ (int) round($tp['blocking_ms']) }}ms</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">{{ (int) round($tp['main_thread_ms']) }}ms</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endif

    {{-- Panel D: Main thread breakdown --}}
    @if (! empty($audit->details['main_thread']))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.cpu-chip class="size-5 text-violet-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.main_thread_title') }}</h2>
            </div>
            @php
                $mtCategories = collect($audit->details['main_thread'])->pluck('category')->all();
                $mtDurations = collect($audit->details['main_thread'])->pluck('duration_ms')->map(fn ($v) => (int) round((float) $v))->all();
            @endphp
            <div id="mainthread-chart"></div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new ApexCharts(document.querySelector('#mainthread-chart'), {
                        chart: { type: 'bar', height: 240, toolbar: { show: false }, animations: { enabled: false } },
                        series: [{ name: 'Duration', data: @json($mtDurations) }],
                        xaxis: { categories: @json($mtCategories), labels: { style: { fontSize: '11px' } } },
                        yaxis: { labels: { formatter: (v) => v + ' ms' } },
                        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
                        colors: ['#a855f7'],
                        dataLabels: { enabled: false },
                        grid: { borderColor: 'rgba(161,161,170,0.15)', strokeDashArray: 3 },
                    }).render();
                });
            </script>
        </div>
    @endif

    {{-- Panel E: Slow requests (top 10) --}}
    @if (! empty($audit->details['slow_requests']))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.clock class="size-5 text-amber-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.slowest_requests_title') }}</h2>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('vitals::vitals.tables.url') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('vitals::vitals.tables.type') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('vitals::vitals.tables.size') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('vitals::vitals.tables.duration') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach (array_slice($audit->details['slow_requests'], 0, 10) as $req)
                        @php
                            $color = match (true) {
                                ($req['duration_ms'] ?? 0) > 800 => 'rose',
                                ($req['duration_ms'] ?? 0) > 400 => 'amber',
                                default => 'zinc',
                            };
                        @endphp
                        <flux:table.row>
                            <flux:table.cell class="max-w-xs truncate">
                                <code class="text-xs">{{ basename(parse_url($req['url'] ?? '', PHP_URL_PATH) ?: $req['url'] ?? '?') }}</code>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:badge color="zinc" size="sm">{{ $req['resource_type'] ?? '?' }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">{{ number_format(($req['transfer_bytes'] ?? 0) / 1024, 0) }} KB</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:badge color="{{ $color }}" size="sm">{{ (int) round($req['duration_ms'] ?? 0) }}ms</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endif

    {{-- Panel F: Cache policy --}}
    @if (! empty($audit->details['cache_policy']))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="archive-box" class="size-5 text-amber-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.cache_policy_title') }}</h2>
                <flux:badge color="amber" size="sm">{{ count($audit->details['cache_policy']) }}</flux:badge>
            </div>
            <p class="text-sm text-ink-500 mb-3">{{ __('vitals::vitals.audit_detail.cache_policy_body') }}</p>
            <ul class="space-y-1.5">
                @foreach (array_slice($audit->details['cache_policy'], 0, 8) as $row)
                    @php $ttl = (int) ($row['ttl_seconds'] ?? 0); @endphp
                    <li class="flex items-center justify-between text-xs">
                        <code class="truncate flex-1 text-ink-700 dark:text-ink-300">{{ $row['url'] }}</code>
                        <flux:badge color="amber" size="sm">
                            @if ($ttl === 0)
                                {{ __('vitals::vitals.audit_detail.no_cache') }}
                            @elseif ($ttl < 3600)
                                {{ $ttl }}s
                            @elseif ($ttl < 86400)
                                {{ (int) round($ttl / 3600) }}h
                            @else
                                {{ (int) round($ttl / 86400) }}d
                            @endif
                        </flux:badge>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Panel G: Diagnostics summary --}}
    @if ($audit->details && (! empty($audit->details['critical_chain_depth']) || ! empty($audit->details['bootup_time'])))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="beaker" class="size-5 text-sky-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.audit_detail.diagnostics_title') }}</h2>
            </div>
            <dl class="space-y-3 text-sm">
                @if (! empty($audit->details['critical_chain_depth']))
                    @php $depth = (int) $audit->details['critical_chain_depth']; @endphp
                    <div class="flex items-center justify-between">
                        <dt class="text-ink-500 dark:text-ink-400">{{ __('vitals::vitals.audit_detail.critical_chain_depth') }}</dt>
                        <dd>
                            <flux:badge color="{{ $depth > 3 ? 'rose' : ($depth > 2 ? 'amber' : 'emerald') }}" size="sm">{{ $depth }} {{ __('vitals::vitals.audit_detail.levels') }}</flux:badge>
                        </dd>
                    </div>
                @endif

                @if (! empty($audit->details['bootup_time']))
                    <div>
                        <dt class="text-ink-500 dark:text-ink-400 mb-1.5">{{ __('vitals::vitals.audit_detail.top_js_costs') }}</dt>
                        <dd>
                            <ul class="space-y-1">
                                @foreach (array_slice($audit->details['bootup_time'], 0, 5) as $b)
                                    <li class="flex items-center justify-between text-xs">
                                        <code class="truncate flex-1 text-ink-700 dark:text-ink-300">{{ basename(parse_url($b['url'] ?? '', PHP_URL_PATH) ?: $b['url'] ?? '?') }}</code>
                                        <span class="text-ink-500">{{ (int) round($b['total_ms'] ?? 0) }}ms</span>
                                    </li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    @endif

    {{-- Request trace waterfall --}}
    @if ($audit->telemetry && ! empty($audit->telemetry->events_log))
        @php
            $events = $audit->telemetry->events_log;
            $totalDuration = $audit->telemetry->duration_ms > 0 ? (float) $audit->telemetry->duration_ms : 1000.0;
        @endphp
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="bars-3-bottom-left" class="size-5 text-violet-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.trace.title') }}</h2>
                <flux:badge color="zinc" size="sm">{{ count($events) }} {{ __('vitals::vitals.trace.events') }}</flux:badge>
            </div>
            {{-- Legend --}}
            <div class="flex flex-wrap gap-3 mb-4 text-xs text-ink-500">
                @foreach ([
                    'query' => ['label' => __('vitals::vitals.trace.query'), 'dot' => 'bg-accent-400'],
                    'view'  => ['label' => __('vitals::vitals.trace.view'),  'dot' => 'bg-violet-400'],
                    'cache' => ['label' => __('vitals::vitals.trace.cache'), 'dot' => 'bg-emerald-400'],
                    'job'   => ['label' => __('vitals::vitals.trace.job'),   'dot' => 'bg-amber-400'],
                ] as $type => $legendMeta)
                    <span class="flex items-center gap-1">
                        <span @class(['inline-block size-2.5 rounded-sm', $legendMeta['dot']])></span>
                        {{ $legendMeta['label'] }}
                    </span>
                @endforeach
            </div>
            {{-- SVG Waterfall --}}
            @php
                $rowH = 22;
                $labelW = 160;
                $trackW = 500;
                $svgH = max(60, count($events) * $rowH + 30);
            @endphp
            <div class="overflow-x-auto rounded-lg border border-ink-100 dark:border-ink-800">
                <svg width="{{ $labelW + $trackW + 60 }}" height="{{ $svgH }}" xmlns="http://www.w3.org/2000/svg" aria-label="{{ __('vitals::vitals.trace.waterfall_label') }}">
                    {{-- Time axis tick marks --}}
                    @foreach ([0, 25, 50, 75, 100] as $pct)
                        @php $x = $labelW + (int) ($trackW * $pct / 100); @endphp
                        <line x1="{{ $x }}" y1="0" x2="{{ $x }}" y2="{{ $svgH }}" stroke="currentColor" stroke-opacity="0.08" stroke-width="1" />
                        <text x="{{ $x }}" y="{{ $svgH - 4 }}" text-anchor="middle" font-size="9" fill="currentColor" opacity="0.4">{{ number_format($totalDuration * $pct / 100, 0) }}ms</text>
                    @endforeach

                    @foreach ($events as $i => $ev)
                        @php
                            $y = $i * $rowH + 4;
                            $startPct = min(100, ($ev['start_ms'] ?? 0) / $totalDuration * 100);
                            $durPct   = max(0.5, min(100 - $startPct, ($ev['duration_ms'] ?? 1) / $totalDuration * 100));
                            $barX     = $labelW + (int) ($trackW * $startPct / 100);
                            $barW     = max(2, (int) ($trackW * $durPct / 100));
                            $type     = $ev['type'] ?? 'query';
                            $colorMap = ['query' => '#f87171', 'view' => '#a78bfa', 'cache' => '#34d399', 'job' => '#fbbf24'];
                            $color    = $colorMap[$type] ?? '#94a3b8';
                            $label    = mb_strimwidth($ev['label'] ?? $type, 0, 28, '…');
                        @endphp
                        <text x="{{ $labelW - 6 }}" y="{{ $y + 12 }}" text-anchor="end" font-size="10" fill="currentColor" opacity="0.8" font-family="monospace">{{ $label }}</text>
                        <rect x="{{ $barX }}" y="{{ $y + 2 }}" width="{{ $barW }}" height="{{ $rowH - 8 }}" rx="2" fill="{{ $color }}" opacity="0.85">
                            <title>{{ $ev['label'] ?? '' }} — {{ $ev['duration_ms'] ?? 0 }}ms @ {{ $ev['start_ms'] ?? 0 }}ms</title>
                        </rect>
                        @if ($barW > 28)
                            <text x="{{ $barX + 4 }}" y="{{ $y + 12 }}" font-size="9" fill="white" font-family="monospace">{{ $ev['duration_ms'] ?? 0 }}ms</text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </div>
    @endif
</div>
