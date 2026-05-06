<div class="space-y-8">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('vitals.urls') }}">URLs</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $urlModel->label }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- URL header --}}
    <div class="flex items-start justify-between gap-6">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 text-sm text-ink-400 mb-1">
                <flux:icon.link class="size-3.5 shrink-0" />
                <code class="font-mono text-ink-500 dark:text-ink-400 truncate">{{ $urlModel->path }}</code>
            </div>
            <h1 class="text-3xl font-semibold tracking-[-0.02em] text-ink-900 dark:text-ink-100">{{ $urlModel->label }}</h1>
            <div class="mt-2">
                <span class="text-xs text-ink-400 label-caps">{{ $urlModel->device }}</span>
            </div>
        </div>
        {{-- Period control --}}
        <div class="flex items-center gap-0.5 rounded-lg border border-ink-200 dark:border-ink-800 bg-canvas dark:bg-ink-900 p-0.5 shrink-0">
            @foreach (['24h' => '24h', '7d' => '7d', '30d' => '30d', '90d' => '90d', '1y' => '1y', 'all' => 'All'] as $val => $lbl)
                <button
                    wire:click="setPeriod('{{ $val }}')"
                    class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors duration-150
                        {{ $period === $val
                            ? 'bg-ink-900 text-ink-50 dark:bg-ink-100 dark:text-ink-950'
                            : 'text-ink-500 hover:text-ink-900 dark:hover:text-ink-100 hover:bg-ink-100 dark:hover:bg-ink-800' }}"
                >{{ $lbl }}</button>
            @endforeach
        </div>
    </div>

    @if ($history->isEmpty())
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-8">
            <div class="text-center py-4">
                <flux:icon name="clock" class="size-10 text-ink-300 dark:text-ink-700 mx-auto mb-3" />
                <p class="text-sm text-ink-500">No completed audits yet for this URL.</p>
            </div>
        </div>
    @else
        {{-- Area chart --}}
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="label-caps text-ink-400">Performance over time</p>
                    <p class="text-xs text-ink-400 mt-0.5">{{ $periodLabel }}</p>
                </div>
                {{-- Metric toggle --}}
                <div class="flex items-center gap-0.5 rounded-lg border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-950 p-0.5">
                    @foreach ([
                        'performance' => 'Score',
                        'lcp'         => 'LCP',
                        'inp'         => 'INP',
                        'cls'         => 'CLS',
                        'ttfb'        => 'TTFB',
                    ] as $val => $lbl)
                        <button
                            wire:click="setMetric('{{ $val }}')"
                            class="px-2.5 py-1 rounded-md text-xs font-medium transition-colors duration-150
                                {{ $metric === $val
                                    ? 'bg-accent-500 text-white'
                                    : 'text-ink-500 hover:text-ink-900 dark:hover:text-ink-100 hover:bg-ink-100 dark:hover:bg-ink-800' }}"
                        >{{ $lbl }}</button>
                    @endforeach
                </div>
            </div>
            <div wire:ignore>
                <div
                    id="url-area-chart"
                    x-data="{
                        chart: null,
                        series: @js($chartSeries),
                        init() {
                            const isDark = document.documentElement.classList.contains('dark');
                            const gridColor = isDark ? 'oklch(22% 0.010 17)' : 'oklch(90% 0.007 17)';
                            const labelColor = isDark ? 'oklch(52% 0.012 17)' : 'oklch(52% 0.012 17)';
                            this.chart = new ApexCharts(this.$el, {
                                chart: { type: 'area', height: 260, toolbar: { show: false }, sparkline: { enabled: false }, animations: { enabled: false }, fontFamily: 'Geist Variable, system-ui, sans-serif' },
                                series: [{ name: '{{ match($metric) { 'performance' => 'Score', 'lcp' => 'LCP (ms)', 'inp' => 'INP (ms)', 'cls' => 'CLS', 'ttfb' => 'TTFB (ms)', default => 'Value' } }}', data: this.series }],
                                stroke: { curve: 'smooth', width: 2 },
                                fill: {
                                    type: 'gradient',
                                    gradient: { shadeIntensity: 1, opacityFrom: 0.15, opacityTo: 0, stops: [0, 100] }
                                },
                                colors: ['oklch(64% 0.220 12)'],
                                grid: { show: true, borderColor: gridColor, strokeDashArray: 4, padding: { left: 0, right: 0 } },
                                yaxis: { show: false },
                                xaxis: {
                                    type: 'datetime',
                                    labels: { style: { colors: labelColor, fontFamily: 'Geist Variable, system-ui, sans-serif', fontSize: '11px' } },
                                    axisBorder: { show: false },
                                    axisTicks: { show: false }
                                },
                                tooltip: { x: { format: 'MMM d, h:mm tt' } },
                            });
                            this.chart.render();
                        }
                    }"
                    x-init="init()"
                ></div>
            </div>
        </div>

        @if ($periodCount > 0)
            {{-- Average scores — tabular list, no card grid --}}
            <div>
                <p class="label-caps text-ink-400 mb-3">Average scores · {{ $periodLabel }} · {{ $periodCount }} audits</p>
                <div class="border-t border-ink-200 dark:border-ink-800">
                    @foreach ([
                        'performance'    => 'Performance',
                        'accessibility'  => 'Accessibility',
                        'best_practices' => 'Best Practices',
                        'seo'            => 'SEO',
                    ] as $key => $label)
                        @php
                            $val = $avgScores[$key];
                            $color = \LaravelVitals\Support\Health::colorForScore($val);
                            $scoreColorClass = match($color) {
                                'emerald' => 'text-emerald-600 dark:text-emerald-400',
                                'amber'   => 'text-amber-600 dark:text-amber-400',
                                default   => 'text-accent-600 dark:text-accent-500',
                            };
                        @endphp
                        <div class="flex items-center justify-between py-2.5 border-b border-ink-100 dark:border-ink-800/60 last:border-0">
                            <span class="text-sm text-ink-600 dark:text-ink-400">{{ $label }}</span>
                            <span class="text-sm font-semibold tabular-nums {{ $scoreColorClass }}">{{ $val ?? '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($frequentRecos->isNotEmpty())
            <div>
                <p class="label-caps text-ink-400 mb-3">Most frequent issues</p>
                <div class="border-t border-ink-200 dark:border-ink-800">
                    @foreach ($frequentRecos as $r)
                        @php
                            $dot = match ($r->severity) {
                                'critical' => 'bg-accent-500',
                                'warning'  => 'bg-amber-500',
                                default    => 'bg-emerald-500',
                            };
                        @endphp
                        <div class="flex items-baseline gap-3 py-2.5 border-b border-ink-100 dark:border-ink-800/60 last:border-0">
                            <span class="mt-1.5 shrink-0 size-1.5 rounded-full {{ $dot }}"></span>
                            <span class="flex-1 text-sm text-ink-700 dark:text-ink-300">{{ __($r->title_key) }}</span>
                            <span class="shrink-0 text-xs text-ink-400 tabular-nums">{{ $r->occurrences }}×</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($failedAudits->isNotEmpty())
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <p class="label-caps text-ink-400">Failed audits</p>
                    <span class="inline-flex items-center justify-center rounded-full bg-accent-100 dark:bg-accent-700/30 text-accent-600 dark:text-accent-400 text-[10px] font-semibold h-4 min-w-4 px-1.5">{{ $failedAudits->count() }}</span>
                </div>
                <div class="border-t border-ink-200 dark:border-ink-800">
                    @foreach ($failedAudits as $f)
                        <div class="py-2.5 border-b border-ink-100 dark:border-ink-800/60 last:border-0">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm text-ink-700 dark:text-ink-300">{{ $f->driver }} / {{ $f->device }}</span>
                                <span class="text-xs text-ink-400">{{ $f->created_at?->diffForHumans() }}</span>
                            </div>
                            @if ($f->error)
                                <code class="block text-xs text-accent-600 dark:text-accent-500 mt-1 truncate font-mono">{{ $f->error }}</code>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Audit history table --}}
        <div>
            <p class="label-caps text-ink-400 mb-3">Audit history · {{ $history->count() }} {{ Str::plural('audit', $history->count()) }}</p>
            <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-200 dark:border-ink-800">
                            <th class="py-3 pl-5 pr-4 text-left label-caps text-ink-400">Date</th>
                            <th class="py-3 pr-4 label-caps text-ink-400 text-left">Device</th>
                            <th class="py-3 pr-4 label-caps text-ink-400 text-right">Score</th>
                            <th class="py-3 pr-4 label-caps text-ink-400 text-right">LCP</th>
                            <th class="py-3 pr-4 label-caps text-ink-400 text-right">CLS</th>
                            <th class="py-3 pr-5 label-caps text-ink-400 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($history as $a)
                        @php
                            $score = $a->score_performance;
                            $color = \LaravelVitals\Support\Health::colorForScore($score);
                            $grade = \LaravelVitals\Support\Health::grade($score);
                            $scoreColorClass = match($color) {
                                'emerald' => 'text-emerald-600 dark:text-emerald-400',
                                'amber'   => 'text-amber-600 dark:text-amber-400',
                                default   => 'text-accent-600 dark:text-accent-500',
                            };
                        @endphp
                        <tr class="border-b border-ink-100 dark:border-ink-800/50 last:border-0 hover:bg-ink-50 dark:hover:bg-ink-800/30 transition-colors duration-150">
                            <td class="py-3 pl-5 pr-4">
                                <a href="{{ route('vitals.audit', $a) }}" class="text-ink-700 dark:text-ink-300 hover:text-accent-600 dark:hover:text-accent-500 transition-colors duration-150">
                                    {{ $a->completed_at?->format('M j, H:i') }}
                                </a>
                            </td>
                            <td class="py-3 pr-4 text-xs text-ink-500 label-caps">{{ $a->device }}</td>
                            <td class="py-3 pr-4 text-right">
                                <span class="font-semibold tabular-nums {{ $scoreColorClass }}">{{ $score ?? '—' }}</span>
                            </td>
                            <td class="py-3 pr-4 text-right text-ink-600 dark:text-ink-400 tabular-nums">
                                {{ $a->lcp_ms !== null ? (int) round((float) $a->lcp_ms) : '—' }}<span class="text-xs text-ink-400">{{ $a->lcp_ms !== null ? 'ms' : '' }}</span>
                            </td>
                            <td class="py-3 pr-4 text-right text-ink-600 dark:text-ink-400 tabular-nums">
                                {{ $a->cls !== null ? number_format((float) $a->cls, 2) : '—' }}
                            </td>
                            <td class="py-3 pr-5 text-right">
                                <flux:button href="{{ route('vitals.audit', $a) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View audit" />
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
