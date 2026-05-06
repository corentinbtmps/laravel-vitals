<div class="space-y-6">
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('vitals.urls') }}">URLs</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $urlModel->label }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- URL hero card --}}
    <div class="rounded-3xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-8">
        <div class="flex items-start justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 text-sm text-ink-500 mb-2">
                    <flux:icon.link class="size-4" />
                    <code class="text-ink-700 dark:text-ink-300">{{ $urlModel->path }}</code>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">{{ $urlModel->label }}</h1>
                <div class="mt-2">
                    <flux:badge color="zinc" size="sm">{{ $urlModel->device }}</flux:badge>
                </div>
            </div>
            {{-- Period control --}}
            <div class="flex items-center gap-1 rounded-xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-1 shrink-0">
                @foreach (['24h' => ['label' => '24h', 'desc' => 'Show audits from the last 24 hours'], '7d' => ['label' => '7d', 'desc' => 'Show audits from the last 7 days'], '30d' => ['label' => '30d', 'desc' => 'Show audits from the last 30 days'], '90d' => ['label' => '90d', 'desc' => 'Show audits from the last 90 days'], '1y' => ['label' => '1y', 'desc' => 'Show audits from the last year'], 'all' => ['label' => 'All', 'desc' => 'Show all audits']] as $val => $meta)
                    <flux:tooltip content="{{ $meta['desc'] }}">
                        <button
                            wire:click="setPeriod('{{ $val }}')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                {{ $period === $val
                                    ? 'bg-ink-900 text-white dark:bg-ink-100 dark:text-ink-900'
                                    : 'text-ink-500 hover:text-ink-900 dark:hover:text-ink-100' }}"
                        >{{ $meta['label'] }}</button>
                    </flux:tooltip>
                @endforeach
            </div>
        </div>
    </div>

    @if ($history->isEmpty())
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="text-center py-8">
                <flux:icon name="clock" class="size-12 text-ink-300 dark:text-ink-700 mx-auto mb-3" />
                <p class="text-sm text-ink-500">No completed audits yet for this URL.</p>
            </div>
        </div>
    @else
        {{-- Hero area chart --}}
        <div class="rounded-3xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-8">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h3 class="text-base font-semibold">Performance over time</h3>
                    <p class="text-sm text-ink-500 mt-1">{{ $periodLabel }}</p>
                </div>
                {{-- Metric toggle --}}
                <div class="flex items-center gap-1 rounded-xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-1">
                    @foreach ([
                        'performance' => ['label' => 'Score', 'desc' => 'Composite Lighthouse Performance score (0-100)'],
                        'lcp'         => ['label' => 'LCP',   'desc' => 'Largest Contentful Paint — time until the largest visible content renders. Good < 2.5s'],
                        'inp'         => ['label' => 'INP',   'desc' => 'Interaction to Next Paint — input latency. Good < 200ms'],
                        'cls'         => ['label' => 'CLS',   'desc' => 'Cumulative Layout Shift — visual stability. Good < 0.1'],
                        'ttfb'        => ['label' => 'TTFB',  'desc' => 'Time to First Byte — server response time. Good < 800ms'],
                    ] as $val => $meta)
                        <flux:tooltip content="{{ $meta['desc'] }}">
                            <button
                                wire:click="setMetric('{{ $val }}')"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                    {{ $metric === $val
                                        ? 'bg-accent-500 text-white'
                                        : 'text-ink-500 hover:text-ink-900 dark:hover:text-ink-100' }}"
                            >{{ $meta['label'] }}</button>
                        </flux:tooltip>
                    @endforeach
                </div>
            </div>
            <div
                x-data="{
                    chart: null,
                    metric: @js($metric),
                    series: @js($chartSeries),
                    getYaxis(metric) {
                        if (metric === 'cls') {
                            return {
                                show: true,
                                min: 0,
                                max: function(max) { return Math.max(max * 1.2, 0.5); },
                                decimalsInFloat: 3,
                                labels: { style: { colors: '#71717a' }, formatter: function(v) { return v.toFixed(3); } }
                            };
                        }
                        if (metric === 'performance') {
                            return {
                                show: true,
                                min: 0,
                                max: 100,
                                labels: { style: { colors: '#71717a' }, formatter: function(v) { return Math.round(v); } }
                            };
                        }
                        // lcp, inp, ttfb — milliseconds
                        return {
                            show: true,
                            min: 0,
                            labels: { style: { colors: '#71717a' }, formatter: function(v) { return Math.round(v) + 'ms'; } }
                        };
                    },
                    getSeriesName(metric) {
                        const names = { performance: 'Score', lcp: 'LCP (ms)', inp: 'INP (ms)', cls: 'CLS', ttfb: 'TTFB (ms)' };
                        return names[metric] || 'Value';
                    },
                    buildOptions() {
                        return {
                            chart: { type: 'area', height: 280, toolbar: { show: false }, sparkline: { enabled: false }, animations: { enabled: false } },
                            series: [{ name: this.getSeriesName(this.metric), data: this.series }],
                            stroke: { curve: 'smooth', width: 2.5 },
                            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0, stops: [0, 100] } },
                            colors: ['#f43f5e'],
                            grid: { show: true, borderColor: 'rgba(161,161,170,0.1)', strokeDashArray: 4 },
                            yaxis: this.getYaxis(this.metric),
                            xaxis: {
                                type: 'datetime',
                                labels: { style: { colors: '#71717a' } },
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            tooltip: { theme: 'dark', x: { format: 'MMM d, h:mm tt' } },
                            noData: { text: 'No data for this period', style: { color: '#71717a' } },
                        };
                    },
                    init() {
                        this.chart = new ApexCharts(this.$el, this.buildOptions());
                        this.chart.render();

                        // Listen for Livewire updates — re-build chart when metric or series changes
                        this.$wire.on('chartUpdated', (data) => {
                            this.metric = data.metric;
                            this.series = data.series;
                            this.chart.updateOptions(this.buildOptions(), true, false);
                        });
                    }
                }"
                x-init="init()"
            ></div>
        </div>

        @if ($periodCount > 0)
            <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-base font-semibold">Average scores</h3>
                        <p class="text-sm text-ink-500 mt-1">{{ $periodLabel }} — {{ $periodCount }} audits</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ([
                        'performance'    => ['label' => 'Performance',    'color' => 'accent'],
                        'accessibility'  => ['label' => 'Accessibility',  'color' => 'emerald'],
                        'best_practices' => ['label' => 'Best Practices', 'color' => 'violet'],
                        'seo'            => ['label' => 'SEO',            'color' => 'sky'],
                    ] as $key => $meta)
                        @php
                            $val = $avgScores[$key];
                        @endphp
                        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="h-2 w-2 rounded-full bg-{{ $meta['color'] }}-500"></span>
                                <span class="text-xs font-medium text-ink-500 uppercase tracking-wide">{{ $meta['label'] }}</span>
                            </div>
                            <div class="text-3xl font-semibold tabular-nums">
                                {{ $val ?? '—' }}<span class="text-base font-normal text-ink-500">/100</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($frequentRecos->isNotEmpty())
            <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-base font-semibold">Most frequent issues</h3>
                        <p class="text-sm text-ink-500 mt-1">Recurring findings on this URL</p>
                    </div>
                </div>
                <ul class="space-y-2">
                    @foreach ($frequentRecos as $r)
                        @php
                            $sevColor = match ($r->severity) {
                                'critical' => 'rose',
                                'warning'  => 'amber',
                                default    => 'sky',
                            };
                        @endphp
                        <li class="flex items-center gap-3">
                            <flux:badge color="{{ $sevColor }}" size="sm">{{ $r->severity }}</flux:badge>
                            <span class="flex-1 text-sm">{{ __($r->title_key) }}</span>
                            <span class="text-xs text-ink-500">{{ $r->occurrences }}×</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($failedAudits->isNotEmpty())
            <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <h3 class="text-base font-semibold">Recent failed audits</h3>
                    <flux:badge color="rose" size="sm">{{ $failedAudits->count() }}</flux:badge>
                </div>
                <ul class="space-y-2">
                    @foreach ($failedAudits as $f)
                        <li class="text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-ink-700 dark:text-ink-300">{{ $f->driver }} / {{ $f->device }}</span>
                                <span class="text-xs text-ink-500">{{ $f->created_at?->diffForHumans() }}</span>
                            </div>
                            @if ($f->error)
                                <code class="block text-xs text-accent-600 dark:text-accent-400 mt-1 truncate">{{ $f->error }}</code>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Audit history</h3>
                    <p class="text-sm text-ink-500 mt-1">{{ $history->count() }} audits — {{ $periodLabel }}</p>
                </div>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-ink-200 dark:border-ink-800">
                        <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide">Date</th>
                        <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide">Device</th>
                        <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Score</th>
                        <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">LCP</th>
                        <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">CLS</th>
                        <th class="py-3 pl-2 text-right font-semibold text-ink-500 text-xs uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($history as $a)
                    @php
                        $score = $a->score_performance;
                        $color = \LaravelVitals\Support\Health::colorForScore($score);
                        $grade = \LaravelVitals\Support\Health::grade($score);
                    @endphp
                    <tr class="border-b border-ink-100 dark:border-ink-800/50 hover:bg-ink-50 dark:hover:bg-ink-900/40 transition-colors">
                        <td class="py-3 pr-4">
                            <a href="{{ route('vitals.audit', $a) }}" class="text-ink-700 dark:text-ink-300 hover:text-accent-600 hover:underline">
                                {{ $a->completed_at?->format('M j, H:i') }}
                            </a>
                        </td>
                        <td class="py-3 pr-4">
                            <flux:badge color="zinc" size="sm">{{ $a->device }}</flux:badge>
                        </td>
                        <td class="py-3 pr-4 text-right">
                            <span class="inline-flex items-center gap-1.5 text-{{ $color }}-700 dark:text-{{ $color }}-300 font-semibold tabular-nums">
                                {{ $score ?? '—' }}
                                <span class="size-5 rounded bg-{{ $color }}-100 dark:bg-{{ $color }}-900/40 text-xs flex items-center justify-center font-bold">{{ $grade }}</span>
                            </span>
                        </td>
                        <td class="py-3 pr-4 text-right text-ink-700 dark:text-ink-300 tabular-nums">
                            {{ $a->lcp_ms !== null ? (int) round((float) $a->lcp_ms) : '—' }}<span class="text-xs text-ink-500">{{ $a->lcp_ms !== null ? 'ms' : '' }}</span>
                        </td>
                        <td class="py-3 pr-4 text-right text-ink-700 dark:text-ink-300 tabular-nums">
                            {{ $a->cls !== null ? number_format((float) $a->cls, 2) : '—' }}
                        </td>
                        <td class="py-3 pl-2 text-right">
                            <flux:button href="{{ route('vitals.audit', $a) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View audit" />
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
