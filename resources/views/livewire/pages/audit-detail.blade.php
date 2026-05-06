@php
    $overallScore = (int) round((($audit->score_performance ?? 0) + ($audit->score_accessibility ?? 0) + ($audit->score_best_practices ?? 0) + ($audit->score_seo ?? 0)) / 4);
    $overallGrade = \LaravelVitals\Support\Health::grade($overallScore);
    $overallColor = \LaravelVitals\Support\Health::colorForScore($overallScore);
@endphp

<div class="space-y-6">
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
</div>
