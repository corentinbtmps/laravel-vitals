<div class="space-y-6">
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('vitals.urls') }}">URLs</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $urlModel->label }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- URL hero card --}}
    <div class="rounded-3xl border border-zinc-200/60 dark:border-zinc-800/60 bg-gradient-to-br from-white to-zinc-50 dark:from-zinc-900 dark:to-zinc-950 p-8">
        <div class="flex items-start justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 text-sm text-zinc-500 mb-2">
                    <flux:icon.link class="size-4" />
                    <code class="text-zinc-700 dark:text-zinc-300">{{ $urlModel->path }}</code>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">{{ $urlModel->label }}</h1>
                <div class="mt-2">
                    <flux:badge color="zinc" size="sm">{{ $urlModel->device }}</flux:badge>
                </div>
            </div>
            {{-- Period control --}}
            <div class="flex items-center gap-1 rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-1 shrink-0">
                @foreach (['24h' => '24h', '7d' => '7d', '30d' => '30d', '90d' => '90d', '1y' => '1y', 'all' => 'All'] as $val => $lbl)
                    <button
                        wire:click="setPeriod('{{ $val }}')"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                            {{ $period === $val
                                ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}"
                    >{{ $lbl }}</button>
                @endforeach
            </div>
        </div>
    </div>

    @if ($history->isEmpty())
        <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
            <div class="text-center py-8">
                <flux:icon name="clock" class="size-12 text-zinc-300 dark:text-zinc-700 mx-auto mb-3" />
                <p class="text-sm text-zinc-500">No completed audits yet for this URL.</p>
            </div>
        </div>
    @else
        {{-- Hero area chart --}}
        <div class="rounded-3xl border border-zinc-200/60 dark:border-zinc-800/60 bg-gradient-to-br from-white to-zinc-50 dark:from-zinc-900 dark:to-zinc-950 p-8">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h3 class="text-base font-semibold">Performance over time</h3>
                    <p class="text-sm text-zinc-500 mt-1">{{ $periodLabel }}</p>
                </div>
                {{-- Metric toggle --}}
                <div class="flex items-center gap-1 rounded-xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-1">
                    @foreach ([
                        'performance' => 'Score',
                        'lcp'         => 'LCP',
                        'inp'         => 'INP',
                        'cls'         => 'CLS',
                        'ttfb'        => 'TTFB',
                    ] as $val => $lbl)
                        <button
                            wire:click="setMetric('{{ $val }}')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                {{ $metric === $val
                                    ? 'bg-rose-500 text-white'
                                    : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}"
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
                            this.chart = new ApexCharts(this.$el, {
                                chart: { type: 'area', height: 280, toolbar: { show: false }, sparkline: { enabled: false }, animations: { enabled: false } },
                                series: [{ name: '{{ match($metric) { 'performance' => 'Score', 'lcp' => 'LCP (ms)', 'inp' => 'INP (ms)', 'cls' => 'CLS', 'ttfb' => 'TTFB (ms)', default => 'Value' } }}', data: this.series }],
                                stroke: { curve: 'smooth', width: 2.5 },
                                fill: {
                                    type: 'gradient',
                                    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0, stops: [0, 100] }
                                },
                                colors: ['#f43f5e'],
                                grid: { show: true, borderColor: 'rgba(161,161,170,0.1)', strokeDashArray: 4 },
                                yaxis: { show: false },
                                xaxis: {
                                    type: 'datetime',
                                    labels: { style: { colors: '#71717a' } },
                                    axisBorder: { show: false },
                                    axisTicks: { show: false }
                                },
                                tooltip: { theme: 'dark', x: { format: 'MMM d, h:mm tt' } },
                            });
                            this.chart.render();
                        }
                    }"
                    x-init="init()"
                ></div>
            </div>
        </div>

        @if ($periodCount > 0)
            <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-base font-semibold">Average scores</h3>
                        <p class="text-sm text-zinc-500 mt-1">{{ $periodLabel }} — {{ $periodCount }} audits</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ([
                        'performance'    => ['label' => 'Performance',    'color' => 'rose'],
                        'accessibility'  => ['label' => 'Accessibility',  'color' => 'emerald'],
                        'best_practices' => ['label' => 'Best Practices', 'color' => 'violet'],
                        'seo'            => ['label' => 'SEO',            'color' => 'sky'],
                    ] as $key => $meta)
                        @php
                            $val = $avgScores[$key];
                        @endphp
                        <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="h-2 w-2 rounded-full bg-{{ $meta['color'] }}-500"></span>
                                <span class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ $meta['label'] }}</span>
                            </div>
                            <div class="text-3xl font-semibold tabular-nums">
                                {{ $val ?? '—' }}<span class="text-base font-normal text-zinc-500">/100</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($frequentRecos->isNotEmpty())
            <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-base font-semibold">Most frequent issues</h3>
                        <p class="text-sm text-zinc-500 mt-1">Recurring findings on this URL</p>
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
                            <span class="text-xs text-zinc-500">{{ $r->occurrences }}×</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($failedAudits->isNotEmpty())
            <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <h3 class="text-base font-semibold">Recent failed audits</h3>
                    <flux:badge color="rose" size="sm">{{ $failedAudits->count() }}</flux:badge>
                </div>
                <ul class="space-y-2">
                    @foreach ($failedAudits as $f)
                        <li class="text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-zinc-700 dark:text-zinc-300">{{ $f->driver }} / {{ $f->device }}</span>
                                <span class="text-xs text-zinc-500">{{ $f->created_at?->diffForHumans() }}</span>
                            </div>
                            @if ($f->error)
                                <code class="block text-xs text-rose-600 dark:text-rose-400 mt-1 truncate">{{ $f->error }}</code>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Audit history</h3>
                    <p class="text-sm text-zinc-500 mt-1">{{ $history->count() }} audits — {{ $periodLabel }}</p>
                </div>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Date</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Device</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Score</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">LCP</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">CLS</th>
                        <th class="py-3 pl-2 text-right font-semibold text-zinc-500 text-xs uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($history as $a)
                    @php
                        $score = $a->score_performance;
                        $color = \LaravelVitals\Support\Health::colorForScore($score);
                        $grade = \LaravelVitals\Support\Health::grade($score);
                    @endphp
                    <tr class="border-b border-zinc-100 dark:border-zinc-800/50 hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors">
                        <td class="py-3 pr-4">
                            <a href="{{ route('vitals.audit', $a) }}" class="text-zinc-700 dark:text-zinc-300 hover:text-rose-600 hover:underline">
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
                        <td class="py-3 pr-4 text-right text-zinc-700 dark:text-zinc-300 tabular-nums">
                            {{ $a->lcp_ms !== null ? (int) round((float) $a->lcp_ms) : '—' }}<span class="text-xs text-zinc-500">{{ $a->lcp_ms !== null ? 'ms' : '' }}</span>
                        </td>
                        <td class="py-3 pr-4 text-right text-zinc-700 dark:text-zinc-300 tabular-nums">
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
