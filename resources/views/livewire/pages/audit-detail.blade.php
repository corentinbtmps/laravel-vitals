@php
    $overallScore = (int) round((($audit->score_performance ?? 0) + ($audit->score_accessibility ?? 0) + ($audit->score_best_practices ?? 0) + ($audit->score_seo ?? 0)) / 4);
    $overallGrade = \LaravelVitals\Support\Health::grade($overallScore);
    $overallColor = \LaravelVitals\Support\Health::colorForScore($overallScore);

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
    <flux:card class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 via-rose-500/5 to-transparent pointer-events-none"></div>
        <div class="relative flex items-start justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 text-sm text-zinc-500">
                    <flux:icon.link class="size-4" />
                    <code class="text-zinc-700 dark:text-zinc-300">{{ $audit->url?->path }}</code>
                </div>
                <h1 class="mt-2 text-3xl font-bold tracking-tight">{{ $audit->url?->label }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-zinc-500">
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
            <div class="text-right shrink-0">
                <div class="text-7xl font-bold tracking-tight text-{{ $overallColor }}-500 leading-none">{{ $overallGrade }}</div>
                <div class="mt-1 text-2xl font-semibold text-zinc-600 dark:text-zinc-400">{{ $overallScore }}</div>
            </div>
        </div>
    </flux:card>

    {{-- Score breakdown: 4 cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            'score_performance'    => ['label' => 'Performance',    'icon' => 'bolt'],
            'score_accessibility'  => ['label' => 'Accessibility',  'icon' => 'eye'],
            'score_best_practices' => ['label' => 'Best Practices', 'icon' => 'shield-check'],
            'score_seo'            => ['label' => 'SEO',            'icon' => 'magnifying-glass'],
        ] as $col => $meta)
            @php
                $value = $audit->{$col};
                $color = \LaravelVitals\Support\Health::colorForScore($value);
            @endphp
            <flux:card class="!p-4 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-{{ $color }}-500"></div>
                <div class="flex items-center gap-2 text-xs text-zinc-500">
                    <flux:icon name="{{ $meta['icon'] }}" class="size-3.5" />
                    {{ $meta['label'] }}
                </div>
                @if ($value !== null)
                    @php $chartId = 'score-' . $col . '-' . uniqid(); @endphp
                    <div id="{{ $chartId }}" class="mt-1 -mx-1"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            new ApexCharts(document.querySelector('#{{ $chartId }}'), {
                                chart: { type: 'radialBar', height: 140, sparkline: { enabled: true }, animations: { enabled: false } },
                                series: [{{ (int) $value }}],
                                labels: [@json($meta['label'])],
                                colors: ['{{ $color === 'emerald' ? '#10b981' : ($color === 'amber' ? '#f59e0b' : ($color === 'rose' ? '#f43f5e' : '#71717a')) }}'],
                                plotOptions: {
                                    radialBar: {
                                        hollow: { size: '62%' },
                                        track: { background: '{{ $color === 'emerald' ? '#d1fae5' : ($color === 'amber' ? '#fef3c7' : ($color === 'rose' ? '#ffe4e6' : '#e4e4e7')) }}' },
                                        dataLabels: {
                                            name: { show: false },
                                            value: { show: true, fontSize: '24px', fontWeight: 700, offsetY: 8, color: '{{ $color === 'emerald' ? '#059669' : ($color === 'amber' ? '#d97706' : ($color === 'rose' ? '#e11d48' : '#52525b')) }}' },
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
                                <flux:icon.arrow-trending-down class="size-3 text-rose-500" />
                                <span class="text-rose-600 dark:text-rose-400 font-medium">{{ $scoreDelta }}</span>
                            @endif
                            <span class="text-zinc-400">vs prev</span>
                        </div>
                    @endif
                @else
                    <div class="mt-2 text-3xl font-bold text-zinc-400">—</div>
                @endif
            </flux:card>
        @endforeach
    </div>

    {{-- Core Web Vitals --}}
    <flux:card>
        <div class="flex items-center gap-2 mb-4">
            <flux:icon.heart class="size-5 text-rose-500" />
            <h2 class="font-semibold">Core Web Vitals</h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['col' => 'lcp_ms',  'label' => 'LCP',  'unit' => 'ms', 'desc' => 'Largest Contentful Paint'],
                ['col' => 'cls',     'label' => 'CLS',  'unit' => '',   'desc' => 'Cumulative Layout Shift'],
                ['col' => 'inp_ms',  'label' => 'INP',  'unit' => 'ms', 'desc' => 'Interaction to Next Paint'],
                ['col' => 'ttfb_ms', 'label' => 'TTFB', 'unit' => 'ms', 'desc' => 'Time to First Byte'],
            ] as $cwv)
                @php
                    $val = $audit->{$cwv['col']};
                    $valNumeric = $val !== null ? (float) $val : null;
                    $status = \LaravelVitals\Support\Health::cwvStatus($cwv['col'], $valNumeric);
                    $color = \LaravelVitals\Support\Health::colorForStatus($status);
                    $icon  = \LaravelVitals\Support\Health::iconForStatus($status);
                @endphp
                <div class="rounded-lg border border-{{ $color }}-200 dark:border-{{ $color }}-900/40 bg-{{ $color }}-50/40 dark:bg-{{ $color }}-900/10 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-zinc-500">{{ $cwv['label'] }}</span>
                        <flux:icon name="{{ $icon }}" class="size-4 text-{{ $color }}-500" />
                    </div>
                    <div class="text-2xl font-bold text-{{ $color }}-700 dark:text-{{ $color }}-300">
                        @if ($valNumeric !== null)
                            {{ $cwv['col'] === 'cls' ? number_format($valNumeric, 2) : (int) round($valNumeric) }}{{ $cwv['unit'] }}
                        @else
                            —
                        @endif
                    </div>
                    <div class="mt-1 text-[11px] text-zinc-500">{{ $cwv['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </flux:card>

    {{-- Front-end ↔ Back-end correlation panel --}}
    @if ($audit->telemetry && $breakdown['lcp_ms'] !== null && $breakdown['ttfb_ms'] !== null)
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.signal class="size-5 text-sky-500" />
                <h2 class="font-semibold">Front-end ↔ Back-end breakdown</h2>
            </div>

            {{-- Stacked horizontal bar --}}
            <div class="space-y-3">
                <div class="flex items-baseline justify-between">
                    <span class="text-sm text-zinc-500">LCP composition</span>
                    <span class="text-sm font-semibold">{{ (int) round($breakdown['lcp_ms']) }}ms total</span>
                </div>
                <div class="h-8 w-full bg-zinc-100 dark:bg-zinc-800 rounded-md overflow-hidden flex">
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
                        <flux:callout.heading>Backend is the bottleneck</flux:callout.heading>
                        <flux:callout.text>
                            {{ $breakdown['ttfb_share'] }}% of your LCP is server processing time. Frontend optimizations alone won't move the needle — focus on backend.
                        </flux:callout.text>
                    </flux:callout>
                @endif

                @if ($audit->telemetry->n_plus_one_suspect)
                    <flux:callout variant="danger" icon="circle-stack">
                        <flux:callout.heading>N+1 query pattern detected</flux:callout.heading>
                        <flux:callout.text>
                            {{ $audit->telemetry->queries_count }} queries executed in {{ (int) round((float) $audit->telemetry->queries_time_ms) }}ms ({{ $audit->telemetry->queries_unique }} unique patterns).
                            @if ($estimatedGain !== null)
                                Fixing this could shave <strong>~{{ $estimatedGain }}ms</strong> off your TTFB.
                            @endif
                        </flux:callout.text>
                    </flux:callout>
                @endif
            </div>
        </flux:card>
    @endif

    {{-- Backend telemetry stats --}}
    @if ($audit->telemetry)
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="server-stack" class="size-5 text-violet-500" />
                <h2 class="font-semibold">Backend telemetry</h2>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div>
                    <div class="text-xs text-zinc-500 mb-1">Queries</div>
                    <div class="text-xl font-bold flex items-baseline gap-1">
                        {{ $audit->telemetry->queries_count }}
                        <span class="text-xs text-zinc-400">({{ $audit->telemetry->queries_unique }} unique)</span>
                    </div>
                </div>
                <div>
                    <div class="text-xs text-zinc-500 mb-1">Query time</div>
                    <div class="text-xl font-bold">{{ (int) round((float) $audit->telemetry->queries_time_ms) }}ms</div>
                </div>
                <div>
                    <div class="text-xs text-zinc-500 mb-1">Memory peak</div>
                    <div class="text-xl font-bold">{{ number_format($audit->telemetry->memory_peak_kb / 1024, 1) }}MB</div>
                </div>
                <div>
                    <div class="text-xs text-zinc-500 mb-1">Cache hit rate</div>
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
                    <div class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Slowest queries</div>
                    <div class="space-y-2">
                        @foreach (array_slice($audit->telemetry->slow_queries, 0, 5) as $q)
                            <div class="rounded border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 p-3">
                                <div class="flex items-baseline justify-between gap-3 mb-1">
                                    <code class="text-xs text-zinc-700 dark:text-zinc-300 truncate flex-1">{{ $q['sql'] ?? '' }}</code>
                                    <flux:badge color="rose" size="sm">{{ (int) round((float) ($q['time_ms'] ?? 0)) }}ms</flux:badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </flux:card>
    @endif

    {{-- Recommendations grouped by category --}}
    @if ($groupedRecos->isNotEmpty())
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <flux:icon.light-bulb class="size-5 text-amber-500" />
                    <h2 class="font-semibold">Recommendations</h2>
                </div>
                <flux:badge color="amber">{{ $audit->recommendations->count() }}</flux:badge>
            </div>

            <div class="space-y-6">
                @foreach (['performance', 'accessibility', 'best_practices', 'seo'] as $category)
                    @if ($groupedRecos->has($category))
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 mb-3">{{ str_replace('_', ' ', $category) }}</h3>
                            <div class="space-y-3">
                                @foreach ($groupedRecos[$category] as $reco)
                                    @php
                                        $sevColor = match ($reco->severity) {
                                            'critical' => 'rose',
                                            'warning'  => 'amber',
                                            default    => 'sky',
                                        };
                                        $sevIcon = match ($reco->severity) {
                                            'critical' => 'exclamation-circle',
                                            'warning'  => 'exclamation-triangle',
                                            default    => 'information-circle',
                                        };
                                    @endphp
                                    <div class="rounded-lg border border-{{ $sevColor }}-200 dark:border-{{ $sevColor }}-900/40 bg-{{ $sevColor }}-50/30 dark:bg-{{ $sevColor }}-900/5 p-4">
                                        <div class="flex items-start gap-3">
                                            <flux:icon name="{{ $sevIcon }}" class="size-5 text-{{ $sevColor }}-500 shrink-0 mt-0.5" />
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-semibold">{{ __($reco->title_key, $reco->translation_params ?? []) }}</h4>
                                                    <flux:badge color="{{ $sevColor }}" size="sm">{{ $reco->severity }}</flux:badge>
                                                </div>
                                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __($reco->description_key, $reco->translation_params ?? []) }}</p>

                                                @if (! empty($reco->code_references))
                                                    <div class="mt-3 space-y-2">
                                                        @foreach ($reco->code_references as $ref)
                                                            <x-vitals::code-reference :ref="$ref" />
                                                        @endforeach
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
        </flux:card>
    @endif

    {{-- Panel A: Page details --}}
    @if ($audit->details)
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="document-magnifying-glass" class="size-5 text-violet-500" />
                <h2 class="font-semibold">Page details</h2>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-zinc-500 mb-1">Page weight</div>
                    <div class="text-2xl font-bold">
                        @if (! empty($audit->details['page_weight_bytes']))
                            {{ number_format($audit->details['page_weight_bytes'] / 1024, 0) }}
                            <span class="text-sm text-zinc-400">KB</span>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-zinc-500 mb-1">HTTP requests</div>
                    <div class="text-2xl font-bold">{{ $audit->details['request_count'] ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-zinc-500 mb-1">DOM elements</div>
                    @php $domSize = $audit->details['dom_size'] ?? null; @endphp
                    <div class="text-2xl font-bold {{ $domSize !== null && $domSize > 1500 ? 'text-amber-600 dark:text-amber-400' : '' }}">
                        {{ $domSize !== null ? number_format($domSize) : '—' }}
                        @if ($domSize !== null && $domSize > 1500)
                            <flux:icon.exclamation-triangle class="inline size-4 text-amber-500" />
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-zinc-500 mb-1">Render-blocking</div>
                    @php $rbt = $audit->details['render_blocking_time_ms'] ?? null; @endphp
                    <div class="text-2xl font-bold {{ $rbt !== null && $rbt > 300 ? 'text-rose-600 dark:text-rose-400' : '' }}">
                        {{ $rbt !== null ? (int) round($rbt) . 'ms' : '—' }}
                    </div>
                </div>
            </div>

            @if (! empty($audit->details['lcp_element']['selector']))
                <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <div class="text-xs text-zinc-500 mb-2 flex items-center gap-1.5">
                        <flux:icon.heart class="size-3.5 text-rose-500" /> LCP element
                    </div>
                    <code class="block text-xs bg-zinc-50 dark:bg-zinc-900 p-2 rounded border border-zinc-200 dark:border-zinc-800 overflow-x-auto">{{ $audit->details['lcp_element']['selector'] }}</code>
                    @if (! empty($audit->details['lcp_element']['snippet']))
                        <code class="block text-xs bg-zinc-50 dark:bg-zinc-900 p-2 rounded border border-zinc-200 dark:border-zinc-800 mt-1.5 overflow-x-auto">{{ $audit->details['lcp_element']['snippet'] }}</code>
                    @endif
                </div>
            @endif
        </flux:card>
    @endif

    {{-- Panel B: Resource breakdown --}}
    @if (! empty($audit->details['resource_summary']))
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.archive-box class="size-5 text-sky-500" />
                <h2 class="font-semibold">Resource breakdown</h2>
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

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                            <th class="py-2 text-xs uppercase tracking-wide text-zinc-500">Type</th>
                            <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Count</th>
                            <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Size</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($audit->details['resource_summary'] as $row)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800/50">
                            <td class="py-2 capitalize">{{ $row['type'] }}</td>
                            <td class="py-2 text-right">{{ $row['count'] }}</td>
                            <td class="py-2 text-right text-zinc-600 dark:text-zinc-400">{{ number_format($row['bytes'] / 1024, 0) }} KB</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </flux:card>
    @endif

    {{-- Panel C: Third-party impact --}}
    @if (! empty($audit->details['third_parties']))
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.globe-alt class="size-5 text-pink-500" />
                <h2 class="font-semibold">Third-party impact</h2>
                <flux:badge color="pink" size="sm">{{ count($audit->details['third_parties']) }}</flux:badge>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                        <th class="py-2 text-xs uppercase tracking-wide text-zinc-500">Entity</th>
                        <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Transfer</th>
                        <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Blocking</th>
                        <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Main thread</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($audit->details['third_parties'] as $tp)
                    @php $blockingHigh = ($tp['blocking_ms'] ?? 0) > 250; @endphp
                    <tr class="border-b border-zinc-100 dark:border-zinc-800/50">
                        <td class="py-2 font-medium">{{ $tp['entity'] }}</td>
                        <td class="py-2 text-right text-zinc-600 dark:text-zinc-400">{{ number_format(($tp['transfer_bytes'] ?? 0) / 1024, 0) }} KB</td>
                        <td class="py-2 text-right">
                            @if ($blockingHigh)
                                <flux:badge color="rose" size="sm">{{ (int) round($tp['blocking_ms']) }}ms</flux:badge>
                            @else
                                <span class="text-zinc-600 dark:text-zinc-400">{{ (int) round($tp['blocking_ms']) }}ms</span>
                            @endif
                        </td>
                        <td class="py-2 text-right text-zinc-600 dark:text-zinc-400">{{ (int) round($tp['main_thread_ms']) }}ms</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif

    {{-- Panel D: Main thread breakdown --}}
    @if (! empty($audit->details['main_thread']))
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.cpu-chip class="size-5 text-violet-500" />
                <h2 class="font-semibold">Main thread breakdown</h2>
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
                        grid: { borderColor: '#e4e4e7', strokeDashArray: 3 },
                    }).render();
                });
            </script>
        </flux:card>
    @endif

    {{-- Panel E: Slow requests (top 10) --}}
    @if (! empty($audit->details['slow_requests']))
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.clock class="size-5 text-amber-500" />
                <h2 class="font-semibold">Slowest requests</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                        <th class="py-2 text-xs uppercase tracking-wide text-zinc-500">URL</th>
                        <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Type</th>
                        <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Size</th>
                        <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Duration</th>
                    </tr>
                </thead>
                <tbody>
                @foreach (array_slice($audit->details['slow_requests'], 0, 10) as $req)
                    @php
                        $color = match (true) {
                            ($req['duration_ms'] ?? 0) > 800 => 'rose',
                            ($req['duration_ms'] ?? 0) > 400 => 'amber',
                            default => 'zinc',
                        };
                    @endphp
                    <tr class="border-b border-zinc-100 dark:border-zinc-800/50">
                        <td class="py-2 max-w-xs truncate"><code class="text-xs text-zinc-700 dark:text-zinc-300">{{ basename(parse_url($req['url'] ?? '', PHP_URL_PATH) ?: $req['url'] ?? '?') }}</code></td>
                        <td class="py-2 text-right">
                            <flux:badge color="zinc" size="sm">{{ $req['resource_type'] ?? '?' }}</flux:badge>
                        </td>
                        <td class="py-2 text-right text-zinc-600 dark:text-zinc-400">{{ number_format(($req['transfer_bytes'] ?? 0) / 1024, 0) }} KB</td>
                        <td class="py-2 text-right">
                            <flux:badge color="{{ $color }}" size="sm">{{ (int) round($req['duration_ms'] ?? 0) }}ms</flux:badge>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif

    {{-- Panel F: Cache policy --}}
    @if (! empty($audit->details['cache_policy']))
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="archive-box" class="size-5 text-amber-500" />
                <h2 class="font-semibold">Cache policy issues</h2>
                <flux:badge color="amber" size="sm">{{ count($audit->details['cache_policy']) }} resource(s)</flux:badge>
            </div>
            <p class="text-sm text-zinc-500 mb-3">Resources with cache TTL under 30 days. Long-term caching reduces repeat-visit load times.</p>
            <ul class="space-y-1.5">
                @foreach (array_slice($audit->details['cache_policy'], 0, 8) as $row)
                    @php $ttl = (int) ($row['ttl_seconds'] ?? 0); @endphp
                    <li class="flex items-center justify-between text-xs">
                        <code class="truncate flex-1 text-zinc-700 dark:text-zinc-300">{{ $row['url'] }}</code>
                        <flux:badge color="amber" size="sm">
                            @if ($ttl === 0)
                                no cache
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
        </flux:card>
    @endif

    {{-- Panel G: Diagnostics summary --}}
    @if ($audit->details && (! empty($audit->details['critical_chain_depth']) || ! empty($audit->details['bootup_time'])))
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="beaker" class="size-5 text-sky-500" />
                <h2 class="font-semibold">Diagnostics</h2>
            </div>
            <dl class="space-y-3 text-sm">
                @if (! empty($audit->details['critical_chain_depth']))
                    @php $depth = (int) $audit->details['critical_chain_depth']; @endphp
                    <div class="flex items-center justify-between">
                        <dt class="text-zinc-600 dark:text-zinc-400">Critical request chain depth</dt>
                        <dd>
                            <flux:badge color="{{ $depth > 3 ? 'rose' : ($depth > 2 ? 'amber' : 'emerald') }}" size="sm">{{ $depth }} levels</flux:badge>
                        </dd>
                    </div>
                @endif

                @if (! empty($audit->details['bootup_time']))
                    <div>
                        <dt class="text-zinc-600 dark:text-zinc-400 mb-1.5">Top JS execution costs</dt>
                        <dd>
                            <ul class="space-y-1">
                                @foreach (array_slice($audit->details['bootup_time'], 0, 5) as $b)
                                    <li class="flex items-center justify-between text-xs">
                                        <code class="truncate flex-1 text-zinc-700 dark:text-zinc-300">{{ basename(parse_url($b['url'] ?? '', PHP_URL_PATH) ?: $b['url'] ?? '?') }}</code>
                                        <span class="text-zinc-500">{{ (int) round($b['total_ms'] ?? 0) }}ms</span>
                                    </li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                @endif
            </dl>
        </flux:card>
    @endif
</div>
