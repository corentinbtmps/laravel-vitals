@php
    $overallScore = (int) round((($audit->score_performance ?? 0) + ($audit->score_accessibility ?? 0) + ($audit->score_best_practices ?? 0) + ($audit->score_seo ?? 0)) / 4);
    $overallGrade = \LaravelVitals\Support\Health::grade($overallScore);
    $overallColor = \LaravelVitals\Support\Health::colorForScore($overallScore);
    $overallColorClass = match($overallColor) {
        'emerald' => 'text-emerald-600 dark:text-emerald-400',
        'amber'   => 'text-amber-600 dark:text-amber-400',
        default   => 'text-accent-600 dark:text-accent-500',
    };

    $delta = function ($current, $prev) {
        if ($current === null || $prev === null) return null;
        $diff = (float) $current - (float) $prev;
        if ($diff == 0) return null;
        return ['diff' => $diff, 'is_better' => $diff > 0];
    };
@endphp

<div class="space-y-8">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('vitals.urls') }}">URLs</flux:breadcrumbs.item>
        @if ($audit->url)
            <flux:breadcrumbs.item href="{{ route('vitals.url', $audit->url->id) }}">{{ $audit->url->label }}</flux:breadcrumbs.item>
        @endif
        <flux:breadcrumbs.item>{{ $audit->completed_at?->format('M j, H:i') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Audit header --}}
    <div class="flex items-start justify-between gap-6">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 text-sm text-ink-400 mb-1">
                <flux:icon.link class="size-3.5 shrink-0" />
                <code class="font-mono text-ink-500 dark:text-ink-400 truncate">{{ $audit->url?->path }}</code>
            </div>
            <h1 class="text-3xl font-semibold tracking-[-0.02em] text-ink-900 dark:text-ink-100">{{ $audit->url?->label }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-ink-400">
                <span class="inline-flex items-center gap-1.5">
                    <flux:icon name="{{ $audit->device === 'mobile' ? 'device-phone-mobile' : 'computer-desktop' }}" class="size-3.5" />
                    {{ $audit->device }}
                </span>
                <span>·</span>
                <span class="inline-flex items-center gap-1.5">
                    <flux:icon.clock class="size-3.5" />
                    {{ $audit->completed_at?->toDayDateTimeString() }}
                </span>
                <span>·</span>
                <span class="text-xs label-caps">{{ $audit->driver }}</span>
            </div>
        </div>
        {{-- Overall score --}}
        <div class="text-right shrink-0">
            <div class="text-5xl font-semibold tabular-nums {{ $overallColorClass }} leading-none">{{ $overallScore }}</div>
            <div class="mt-1 text-xs label-caps text-ink-400">overall</div>
        </div>
    </div>

    {{-- Score breakdown — tabular, no radial charts --}}
    <div>
        <p class="label-caps text-ink-400 mb-3">Scores</p>
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-ink-200 dark:border-ink-800">
                        <th class="py-3 pl-5 pr-4 text-left label-caps text-ink-400">Category</th>
                        <th class="py-3 pr-4 label-caps text-ink-400 text-right">Score</th>
                        <th class="py-3 pr-5 label-caps text-ink-400 text-right">vs previous</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ([
                    'score_performance'    => ['label' => 'Performance',    'icon' => 'bolt'],
                    'score_accessibility'  => ['label' => 'Accessibility',  'icon' => 'eye'],
                    'score_best_practices' => ['label' => 'Best Practices', 'icon' => 'shield-check'],
                    'score_seo'            => ['label' => 'SEO',            'icon' => 'magnifying-glass'],
                ] as $col => $meta)
                    @php
                        $value = $audit->{$col};
                        $color = \LaravelVitals\Support\Health::colorForScore($value);
                        $scoreColorClass = match($color) {
                            'emerald' => 'text-emerald-600 dark:text-emerald-400',
                            'amber'   => 'text-amber-600 dark:text-amber-400',
                            default   => 'text-accent-600 dark:text-accent-500',
                        };
                        $prevValue = $previous?->{$col};
                        $scoreDelta = $value !== null && $prevValue !== null ? (int) $value - (int) $prevValue : null;
                    @endphp
                    <tr class="border-b border-ink-100 dark:border-ink-800/50 last:border-0">
                        <td class="py-3 pl-5 pr-4">
                            <div class="flex items-center gap-2">
                                <flux:icon name="{{ $meta['icon'] }}" class="size-3.5 text-ink-400 shrink-0" />
                                <span class="text-ink-700 dark:text-ink-300">{{ $meta['label'] }}</span>
                            </div>
                        </td>
                        <td class="py-3 pr-4 text-right">
                            @if ($value !== null)
                                <span class="text-lg font-semibold tabular-nums {{ $scoreColorClass }}">{{ $value }}</span>
                            @else
                                <span class="text-ink-300 dark:text-ink-700">—</span>
                            @endif
                        </td>
                        <td class="py-3 pr-5 text-right">
                            @if ($scoreDelta !== null && $scoreDelta !== 0)
                                <span class="text-sm font-medium tabular-nums {{ $scoreDelta > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-accent-500' }}">
                                    {{ $scoreDelta > 0 ? '▲' : '▼' }} {{ abs($scoreDelta) }}
                                </span>
                            @elseif ($prevValue !== null)
                                <span class="text-xs text-ink-400">→</span>
                            @else
                                <span class="text-xs text-ink-300 dark:text-ink-700">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Core Web Vitals --}}
    <div>
        <p class="label-caps text-ink-400 mb-3">Core Web Vitals</p>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach ([
                ['col' => 'lcp_ms',  'label' => 'LCP',  'unit' => 'ms', 'desc' => 'Largest Contentful Paint',
                 'tooltip' => 'Time until the largest visible content element is rendered. Good = under 2.5s.',
                 'doc' => 'https://web.dev/articles/lcp'],
                ['col' => 'cls',     'label' => 'CLS',  'unit' => '',   'desc' => 'Cumulative Layout Shift',
                 'tooltip' => 'How much visible content unexpectedly shifts during page load. Good = under 0.1.',
                 'doc' => 'https://web.dev/articles/cls'],
                ['col' => 'inp_ms',  'label' => 'INP',  'unit' => 'ms', 'desc' => 'Interaction to Next Paint',
                 'tooltip' => 'Latency between user input and the next paint. Good = under 200ms.',
                 'doc' => 'https://web.dev/articles/inp'],
                ['col' => 'ttfb_ms', 'label' => 'TTFB', 'unit' => 'ms', 'desc' => 'Time to First Byte',
                 'tooltip' => 'How long the server takes to respond with the first byte. Good = under 800ms.',
                 'doc' => 'https://web.dev/articles/ttfb'],
            ] as $cwv)
                @php
                    $val = $audit->{$cwv['col']};
                    $valNumeric = $val !== null ? (float) $val : null;
                    $status = \LaravelVitals\Support\Health::cwvStatus($cwv['col'], $valNumeric);
                    $color = \LaravelVitals\Support\Health::colorForStatus($status);
                    $icon  = \LaravelVitals\Support\Health::iconForStatus($status);
                    $borderClass = match($color) {
                        'emerald' => 'border-emerald-200 dark:border-emerald-900/40',
                        'amber'   => 'border-amber-200 dark:border-amber-900/40',
                        default   => 'border-ink-200 dark:border-ink-800',
                    };
                    $statusLabel = match($status) {
                        'good'    => 'Good',
                        'needs-improvement' => 'Needs work',
                        'poor'    => 'Poor',
                        default   => '—',
                    };
                    $statusTextClass = match($color) {
                        'emerald' => 'text-emerald-600 dark:text-emerald-400',
                        'amber'   => 'text-amber-600 dark:text-amber-400',
                        default   => 'text-accent-500',
                    };
                    $valueColorClass = match($color) {
                        'emerald' => 'text-emerald-700 dark:text-emerald-300',
                        'amber'   => 'text-amber-700 dark:text-amber-300',
                        default   => 'text-accent-600 dark:text-accent-500',
                    };
                @endphp
                <div class="border {{ $borderClass }} rounded-xl bg-canvas dark:bg-ink-900 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <flux:tooltip content="{{ $cwv['desc'] }} — {{ $cwv['tooltip'] ?? '' }}">
                            <span class="label-caps text-ink-500 cursor-help">{{ $cwv['label'] }}</span>
                        </flux:tooltip>
                        <span class="text-[11px] font-medium {{ $statusTextClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="text-2xl font-bold tabular-nums {{ $valueColorClass }}">
                        @if ($valNumeric !== null)
                            {{ $cwv['col'] === 'cls' ? number_format($valNumeric, 2) : (int) round($valNumeric) }}{{ $cwv['unit'] }}
                        @else
                            —
                        @endif
                    </div>
                    <div class="mt-1 text-[11px] text-ink-400">{{ $cwv['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Front-end ↔ Back-end correlation panel --}}
    @if ($audit->telemetry && $breakdown['lcp_ms'] !== null && $breakdown['ttfb_ms'] !== null)
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.signal class="size-4 text-ink-400" />
                <p class="label-caps text-ink-400">Front-end ↔ Back-end breakdown</p>
            </div>

            <div class="space-y-3">
                <div class="flex items-baseline justify-between">
                    <span class="text-sm text-ink-500">LCP composition</span>
                    <span class="text-sm font-semibold tabular-nums text-ink-700 dark:text-ink-300">{{ (int) round($breakdown['lcp_ms']) }}ms total</span>
                </div>
                <div class="h-7 w-full bg-ink-100 dark:bg-ink-800 rounded-md overflow-hidden flex">
                    <div class="h-full bg-ink-500 flex items-center justify-center text-xs text-ink-50 font-medium" style="width: {{ $breakdown['ttfb_share'] }}%">
                        @if ($breakdown['ttfb_share'] >= 15)
                            backend {{ (int) round($breakdown['ttfb_ms']) }}ms ({{ $breakdown['ttfb_share'] }}%)
                        @endif
                    </div>
                    <div class="h-full bg-accent-500 flex items-center justify-center text-xs text-white font-medium" style="width: {{ 100 - $breakdown['ttfb_share'] }}%">
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
        </div>
    @endif

    {{-- Backend telemetry --}}
    @if ($audit->telemetry)
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="server-stack" class="size-4 text-ink-400" />
                <p class="label-caps text-ink-400">Backend telemetry</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                @php
                    $hits = (int) $audit->telemetry->cache_hits;
                    $misses = (int) $audit->telemetry->cache_misses;
                    $total = $hits + $misses;
                    $rate = $total > 0 ? round(($hits / $total) * 100) : null;
                @endphp
                <div>
                    <div class="label-caps text-ink-400 mb-1">Queries</div>
                    <div class="text-xl font-bold tabular-nums text-ink-800 dark:text-ink-200">
                        {{ $audit->telemetry->queries_count }}
                        <span class="text-xs font-normal text-ink-400 ml-1">/ {{ $audit->telemetry->queries_unique }} unique</span>
                    </div>
                </div>
                <div>
                    <div class="label-caps text-ink-400 mb-1">Query time</div>
                    <div class="text-xl font-bold tabular-nums text-ink-800 dark:text-ink-200">{{ (int) round((float) $audit->telemetry->queries_time_ms) }}<span class="text-sm font-normal text-ink-400">ms</span></div>
                </div>
                <div>
                    <div class="label-caps text-ink-400 mb-1">Memory peak</div>
                    <div class="text-xl font-bold tabular-nums text-ink-800 dark:text-ink-200">{{ number_format($audit->telemetry->memory_peak_kb / 1024, 1) }}<span class="text-sm font-normal text-ink-400">MB</span></div>
                </div>
                <div>
                    <div class="label-caps text-ink-400 mb-1">Cache hit rate</div>
                    <div class="text-xl font-bold tabular-nums text-ink-800 dark:text-ink-200">{{ $rate !== null ? $rate . '%' : '—' }}</div>
                </div>
            </div>

            @if (! empty($audit->telemetry->slow_queries))
                <div class="mt-5 pt-4 border-t border-ink-100 dark:border-ink-800">
                    <p class="label-caps text-ink-400 mb-3">Slowest queries</p>
                    <div class="space-y-2">
                        @foreach (array_slice($audit->telemetry->slow_queries, 0, 5) as $q)
                            <div class="flex items-baseline justify-between gap-3 border border-ink-200 dark:border-ink-800 rounded-lg bg-paper dark:bg-ink-950 px-3 py-2">
                                <code class="text-xs text-ink-600 dark:text-ink-400 truncate flex-1 font-mono">{{ $q['sql'] ?? '' }}</code>
                                <span class="shrink-0 text-xs font-semibold tabular-nums text-accent-500">{{ (int) round((float) ($q['time_ms'] ?? 0)) }}ms</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Recommendations grouped by category --}}
    @if ($groupedRecos->isNotEmpty())
        <div>
            <div class="flex items-center justify-between mb-4">
                <p class="label-caps text-ink-400">Recommendations</p>
                <span class="text-xs text-ink-400 tabular-nums">{{ $audit->recommendations->count() }} total</span>
            </div>

            <div class="space-y-8">
                @foreach (['performance', 'accessibility', 'best_practices', 'seo'] as $category)
                    @if ($groupedRecos->has($category))
                        <div>
                            <p class="label-caps text-ink-400 mb-3">{{ str_replace('_', ' ', $category) }}</p>
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
                                        $sevBorderClass = match ($reco->severity) {
                                            'critical' => 'border-accent-200 dark:border-accent-700/30',
                                            'warning'  => 'border-amber-200 dark:border-amber-900/40',
                                            default    => 'border-ink-200 dark:border-ink-800',
                                        };
                                        $sevIconClass = match ($reco->severity) {
                                            'critical' => 'text-accent-500',
                                            'warning'  => 'text-amber-500',
                                            default    => 'text-ink-400',
                                        };
                                        $sevBadgeClass = match ($reco->severity) {
                                            'critical' => 'bg-accent-100 dark:bg-accent-700/30 text-accent-600 dark:text-accent-400',
                                            'warning'  => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                            default    => 'bg-ink-100 dark:bg-ink-800 text-ink-600 dark:text-ink-400',
                                        };
                                    @endphp
                                    <div class="border {{ $sevBorderClass }} rounded-xl bg-canvas dark:bg-ink-900 p-4">
                                        <div class="flex items-start gap-3">
                                            <flux:icon name="{{ $sevIcon }}" class="size-4 {{ $sevIconClass }} shrink-0 mt-0.5" />
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                    <h4 class="font-semibold text-ink-800 dark:text-ink-200">{{ __($reco->title_key, $reco->translation_params ?? []) }}</h4>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold label-caps {{ $sevBadgeClass }}">{{ $reco->severity }}</span>
                                                </div>
                                                <p class="text-sm text-ink-500 dark:text-ink-400">{{ __($reco->description_key, $reco->translation_params ?? []) }}</p>

                                                @php $docs = \LaravelVitals\Recommendations\RecommendationDocs::for($reco->audit_key); @endphp

                                                @if ($docs)
                                                    <div class="mt-3 text-sm text-ink-600 dark:text-ink-300">{{ $docs['why'] }}</div>

                                                    @if (! empty($docs['impact']))
                                                        <div class="mt-2 text-xs text-amber-600 dark:text-amber-400 font-medium">{{ $docs['impact'] }}</div>
                                                    @endif

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

                                                    @if (! empty($docs['good']) || ! empty($docs['bad']))
                                                        <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-3">
                                                            @if (! empty($docs['good']))
                                                                <div class="rounded-lg border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/40 dark:bg-emerald-900/10 overflow-hidden">
                                                                    <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 border-b border-emerald-200 dark:border-emerald-900/40">
                                                                        <flux:icon.check-circle class="size-3.5" />
                                                                        Recommended
                                                                    </div>
                                                                    <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-emerald-800 dark:text-emerald-200 font-mono">{{ $docs['good'] }}</code></pre>
                                                                </div>
                                                            @endif
                                                            @if (! empty($docs['bad']))
                                                                <div class="rounded-lg border border-accent-200 dark:border-accent-700/30 bg-accent-50/40 dark:bg-accent-700/5 overflow-hidden">
                                                                    <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-accent-700 dark:text-accent-400 border-b border-accent-200 dark:border-accent-700/30">
                                                                        <flux:icon.x-circle class="size-3.5" />
                                                                        Avoid
                                                                    </div>
                                                                    <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-accent-800 dark:text-accent-300 font-mono">{{ $docs['bad'] }}</code></pre>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif

                                                @if (! empty($reco->code_references))
                                                    <div class="mt-4">
                                                        <div class="label-caps text-ink-400 mb-2">Found in your application</div>
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

    {{-- Page details --}}
    @if ($audit->details)
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="document-magnifying-glass" class="size-4 text-ink-400" />
                <p class="label-caps text-ink-400">Page details</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                <div>
                    <div class="label-caps text-ink-400 mb-1">Page weight</div>
                    <div class="text-xl font-bold tabular-nums text-ink-800 dark:text-ink-200">
                        @if (! empty($audit->details['page_weight_bytes']))
                            {{ number_format($audit->details['page_weight_bytes'] / 1024, 0) }}<span class="text-sm font-normal text-ink-400">KB</span>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div>
                    <div class="label-caps text-ink-400 mb-1">HTTP requests</div>
                    <div class="text-xl font-bold tabular-nums text-ink-800 dark:text-ink-200">{{ $audit->details['request_count'] ?? '—' }}</div>
                </div>
                <div>
                    <div class="label-caps text-ink-400 mb-1">DOM elements</div>
                    @php $domSize = $audit->details['dom_size'] ?? null; @endphp
                    <div class="text-xl font-bold tabular-nums {{ $domSize !== null && $domSize > 1500 ? 'text-amber-600 dark:text-amber-400' : 'text-ink-800 dark:text-ink-200' }}">
                        {{ $domSize !== null ? number_format($domSize) : '—' }}
                        @if ($domSize !== null && $domSize > 1500)
                            <flux:icon.exclamation-triangle class="inline size-4 text-amber-500 ml-1" />
                        @endif
                    </div>
                </div>
                <div>
                    <div class="label-caps text-ink-400 mb-1">Render-blocking</div>
                    @php $rbt = $audit->details['render_blocking_time_ms'] ?? null; @endphp
                    <div class="text-xl font-bold tabular-nums {{ $rbt !== null && $rbt > 300 ? 'text-accent-600 dark:text-accent-500' : 'text-ink-800 dark:text-ink-200' }}">
                        {{ $rbt !== null ? (int) round($rbt) . 'ms' : '—' }}
                    </div>
                </div>
            </div>

            @if (! empty($audit->details['lcp_element']['selector']))
                <div class="mt-5 pt-4 border-t border-ink-100 dark:border-ink-800">
                    <div class="label-caps text-ink-400 mb-2">LCP element</div>
                    <code class="block text-xs font-mono bg-paper dark:bg-ink-950 text-ink-700 dark:text-ink-300 p-2 rounded-lg border border-ink-200 dark:border-ink-800 overflow-x-auto">{{ $audit->details['lcp_element']['selector'] }}</code>
                    @if (! empty($audit->details['lcp_element']['snippet']))
                        <code class="block text-xs font-mono bg-paper dark:bg-ink-950 text-ink-700 dark:text-ink-300 p-2 rounded-lg border border-ink-200 dark:border-ink-800 mt-1.5 overflow-x-auto">{{ $audit->details['lcp_element']['snippet'] }}</code>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Resource breakdown --}}
    @if (! empty($audit->details['resource_summary']))
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.archive-box class="size-4 text-ink-400" />
                <p class="label-caps text-ink-400">Resource breakdown</p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                @php
                    $resourceColors = [
                        'script' => 'oklch(64% 0.220 12)', 'image' => '#0ea5e9', 'stylesheet' => '#10b981',
                        'font' => '#a855f7', 'document' => 'oklch(52% 0.012 17)', 'media' => '#f59e0b',
                        'other' => 'oklch(65% 0.010 17)', 'third-party' => '#ec4899',
                    ];
                    $resourceData = collect($audit->details['resource_summary'])
                        ->map(fn ($r) => ['type' => $r['type'], 'bytes' => $r['bytes']])
                        ->filter(fn ($r) => $r['bytes'] > 0)
                        ->values();
                    $chartLabels = $resourceData->pluck('type')->all();
                    $chartValues = $resourceData->pluck('bytes')->all();
                    $chartColors = $resourceData->pluck('type')->map(fn ($t) => $resourceColors[$t] ?? 'oklch(65% 0.010 17)')->all();
                @endphp

                <div id="resource-pie-chart"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        new ApexCharts(document.querySelector('#resource-pie-chart'), {
                            chart: { type: 'donut', height: 220, animations: { enabled: false }, fontFamily: 'Geist Variable, system-ui, sans-serif' },
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
                        <tr class="border-b border-ink-200 dark:border-ink-800">
                            <th class="py-2 text-left label-caps text-ink-400">Type</th>
                            <th class="py-2 text-right label-caps text-ink-400">Count</th>
                            <th class="py-2 text-right label-caps text-ink-400">Size</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($audit->details['resource_summary'] as $row)
                        <tr class="border-b border-ink-100 dark:border-ink-800/50 last:border-0">
                            <td class="py-2 capitalize text-ink-700 dark:text-ink-300">{{ $row['type'] }}</td>
                            <td class="py-2 text-right text-ink-600 dark:text-ink-400 tabular-nums">{{ $row['count'] }}</td>
                            <td class="py-2 text-right text-ink-500 tabular-nums">{{ number_format($row['bytes'] / 1024, 0) }} KB</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Third-party impact --}}
    @if (! empty($audit->details['third_parties']))
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.globe-alt class="size-4 text-ink-400" />
                <p class="label-caps text-ink-400">Third-party impact</p>
                <span class="text-xs text-ink-400 tabular-nums ml-auto">{{ count($audit->details['third_parties']) }} entities</span>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-ink-200 dark:border-ink-800">
                        <th class="py-2 text-left label-caps text-ink-400">Entity</th>
                        <th class="py-2 text-right label-caps text-ink-400">Transfer</th>
                        <th class="py-2 text-right label-caps text-ink-400">Blocking</th>
                        <th class="py-2 text-right label-caps text-ink-400">Main thread</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($audit->details['third_parties'] as $tp)
                    @php $blockingHigh = ($tp['blocking_ms'] ?? 0) > 250; @endphp
                    <tr class="border-b border-ink-100 dark:border-ink-800/50 last:border-0">
                        <td class="py-2 font-medium text-ink-700 dark:text-ink-300">{{ $tp['entity'] }}</td>
                        <td class="py-2 text-right text-ink-500 tabular-nums">{{ number_format(($tp['transfer_bytes'] ?? 0) / 1024, 0) }} KB</td>
                        <td class="py-2 text-right tabular-nums">
                            @if ($blockingHigh)
                                <span class="text-accent-500 font-medium">{{ (int) round($tp['blocking_ms']) }}ms</span>
                            @else
                                <span class="text-ink-500">{{ (int) round($tp['blocking_ms']) }}ms</span>
                            @endif
                        </td>
                        <td class="py-2 text-right text-ink-500 tabular-nums">{{ (int) round($tp['main_thread_ms']) }}ms</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Main thread breakdown --}}
    @if (! empty($audit->details['main_thread']))
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.cpu-chip class="size-4 text-ink-400" />
                <p class="label-caps text-ink-400">Main thread breakdown</p>
            </div>
            @php
                $mtCategories = collect($audit->details['main_thread'])->pluck('category')->all();
                $mtDurations = collect($audit->details['main_thread'])->pluck('duration_ms')->map(fn ($v) => (int) round((float) $v))->all();
            @endphp
            <div id="mainthread-chart-{{ $audit->id }}"></div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new ApexCharts(document.querySelector('#mainthread-chart-{{ $audit->id }}'), {
                        chart: { type: 'bar', height: 220, toolbar: { show: false }, animations: { enabled: false }, fontFamily: 'Geist Variable, system-ui, sans-serif' },
                        series: [{ name: 'Duration', data: @json($mtDurations) }],
                        xaxis: { categories: @json($mtCategories), labels: { style: { fontSize: '11px', fontFamily: 'Geist Variable, system-ui, sans-serif' } } },
                        yaxis: { labels: { formatter: (v) => v + ' ms', style: { fontFamily: 'Geist Variable, system-ui, sans-serif' } } },
                        plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '60%' } },
                        colors: ['oklch(64% 0.220 12)'],
                        dataLabels: { enabled: false },
                        grid: { borderColor: 'oklch(90% 0.007 17)', strokeDashArray: 3 },
                    }).render();
                });
            </script>
        </div>
    @endif

    {{-- Slow requests --}}
    @if (! empty($audit->details['slow_requests']))
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.clock class="size-4 text-ink-400" />
                <p class="label-caps text-ink-400">Slowest requests</p>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-ink-200 dark:border-ink-800">
                        <th class="py-2 text-left label-caps text-ink-400">URL</th>
                        <th class="py-2 text-right label-caps text-ink-400">Type</th>
                        <th class="py-2 text-right label-caps text-ink-400">Size</th>
                        <th class="py-2 text-right label-caps text-ink-400">Duration</th>
                    </tr>
                </thead>
                <tbody>
                @foreach (array_slice($audit->details['slow_requests'], 0, 10) as $req)
                    @php
                        $dur = $req['duration_ms'] ?? 0;
                        $durClass = match (true) {
                            $dur > 800 => 'text-accent-500 font-medium',
                            $dur > 400 => 'text-amber-600 dark:text-amber-400',
                            default    => 'text-ink-500',
                        };
                    @endphp
                    <tr class="border-b border-ink-100 dark:border-ink-800/50 last:border-0">
                        <td class="py-2 max-w-xs truncate"><code class="text-xs font-mono text-ink-600 dark:text-ink-400">{{ basename(parse_url($req['url'] ?? '', PHP_URL_PATH) ?: $req['url'] ?? '?') }}</code></td>
                        <td class="py-2 text-right text-xs label-caps text-ink-400">{{ $req['resource_type'] ?? '?' }}</td>
                        <td class="py-2 text-right text-ink-500 tabular-nums text-xs">{{ number_format(($req['transfer_bytes'] ?? 0) / 1024, 0) }} KB</td>
                        <td class="py-2 text-right tabular-nums text-sm {{ $durClass }}">{{ (int) round($dur) }}ms</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Cache policy --}}
    @if (! empty($audit->details['cache_policy']))
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="archive-box" class="size-4 text-ink-400" />
                    <p class="label-caps text-ink-400">Cache policy issues</p>
                </div>
                <span class="text-xs text-ink-400 tabular-nums">{{ count($audit->details['cache_policy']) }} resource(s)</span>
            </div>
            <p class="text-sm text-ink-500 mb-3">Resources with cache TTL under 30 days. Long-term caching reduces repeat-visit load times.</p>
            <ul class="space-y-1.5">
                @foreach (array_slice($audit->details['cache_policy'], 0, 8) as $row)
                    @php $ttl = (int) ($row['ttl_seconds'] ?? 0); @endphp
                    <li class="flex items-center justify-between text-xs">
                        <code class="truncate flex-1 text-ink-600 dark:text-ink-400 font-mono">{{ $row['url'] }}</code>
                        <span class="shrink-0 ml-3 font-medium tabular-nums text-amber-600 dark:text-amber-400">
                            @if ($ttl === 0) no cache
                            @elseif ($ttl < 3600) {{ $ttl }}s
                            @elseif ($ttl < 86400) {{ (int) round($ttl / 3600) }}h
                            @else {{ (int) round($ttl / 86400) }}d
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Diagnostics --}}
    @if ($audit->details && (! empty($audit->details['critical_chain_depth']) || ! empty($audit->details['bootup_time'])))
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="beaker" class="size-4 text-ink-400" />
                <p class="label-caps text-ink-400">Diagnostics</p>
            </div>
            <dl class="space-y-4 text-sm">
                @if (! empty($audit->details['critical_chain_depth']))
                    @php $depth = (int) $audit->details['critical_chain_depth']; @endphp
                    <div class="flex items-center justify-between">
                        <dt class="text-ink-500 dark:text-ink-400">Critical request chain depth</dt>
                        <dd class="font-semibold tabular-nums {{ $depth > 3 ? 'text-accent-500' : ($depth > 2 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">{{ $depth }} levels</dd>
                    </div>
                @endif

                @if (! empty($audit->details['bootup_time']))
                    <div>
                        <dt class="text-ink-500 dark:text-ink-400 mb-2">Top JS execution costs</dt>
                        <dd>
                            <ul class="space-y-1">
                                @foreach (array_slice($audit->details['bootup_time'], 0, 5) as $b)
                                    <li class="flex items-center justify-between text-xs">
                                        <code class="truncate flex-1 text-ink-600 dark:text-ink-400 font-mono">{{ basename(parse_url($b['url'] ?? '', PHP_URL_PATH) ?: $b['url'] ?? '?') }}</code>
                                        <span class="shrink-0 ml-3 text-ink-500 tabular-nums">{{ (int) round($b['total_ms'] ?? 0) }}ms</span>
                                    </li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    @endif
</div>
