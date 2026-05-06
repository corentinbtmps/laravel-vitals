<div class="space-y-6">
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('vitals.urls') }}">URLs</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $urlModel->label }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <flux:card class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 via-rose-500/5 to-transparent pointer-events-none"></div>
        <div class="relative">
            <div class="flex items-center gap-2 text-sm text-zinc-500 mb-2">
                <flux:icon.link class="size-4" />
                <code class="text-zinc-700 dark:text-zinc-300">{{ $urlModel->path }}</code>
            </div>
            <h1 class="text-3xl font-bold tracking-tight">{{ $urlModel->label }}</h1>
            <div class="mt-2 text-sm text-zinc-500">
                <flux:badge color="zinc" size="sm">{{ $urlModel->device }}</flux:badge>
            </div>
        </div>
    </flux:card>

    @if ($history->isEmpty())
        <flux:card>
            <div class="text-center py-8">
                <flux:icon name="clock" class="size-12 text-zinc-300 dark:text-zinc-700 mx-auto mb-3" />
                <p class="text-sm text-zinc-500">No completed audits yet for this URL.</p>
            </div>
        </flux:card>
    @else
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.chart-bar class="size-5 text-rose-500" />
                <h2 class="font-semibold">Performance trend</h2>
            </div>
            @php
                $reversed = $history->reverse()->values();
                $perfData = $reversed->pluck('score_performance')->map(fn ($v) => $v !== null ? (int) $v : null)->all();
                $lcpData  = $reversed->pluck('lcp_ms')->map(fn ($v) => $v !== null ? (int) round((float) $v) : null)->all();
                $labels   = $reversed->pluck('completed_at')->map(fn ($d) => $d?->format('M j H:i'))->all();
            @endphp
            <div id="url-trend-chart" class="-mx-2"></div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new ApexCharts(document.querySelector('#url-trend-chart'), {
                        chart: { type: 'line', height: 280, toolbar: { show: false }, animations: { enabled: false } },
                        series: [
                            { name: 'Performance score', data: @json($perfData), yAxisIndex: 0 },
                            { name: 'LCP (ms)', data: @json($lcpData), yAxisIndex: 1 },
                        ],
                        xaxis: { categories: @json($labels), labels: { style: { fontSize: '11px' } } },
                        yaxis: [
                            { seriesName: 'Performance score', max: 100, min: 0, title: { text: 'Score' } },
                            { seriesName: 'LCP (ms)', opposite: true, title: { text: 'LCP (ms)' } },
                        ],
                        stroke: { curve: 'smooth', width: 2 },
                        colors: ['#f43f5e', '#0ea5e9'],
                        grid: { borderColor: '#e4e4e7', strokeDashArray: 3 },
                        legend: { position: 'top', horizontalAlign: 'right' },
                    }).render();
                });
            </script>
        </flux:card>

        @if ($thirtyDayCount > 0)
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <flux:icon name="chart-bar" class="size-5 text-rose-500" />
                        <h2 class="font-semibold">30-day averages</h2>
                    </div>
                    <flux:badge color="zinc" size="sm">{{ $thirtyDayCount }} audits</flux:badge>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ([
                        'performance'    => 'Performance',
                        'accessibility'  => 'Accessibility',
                        'best_practices' => 'Best Practices',
                        'seo'            => 'SEO',
                    ] as $key => $label)
                        @php
                            $val = $avgScores[$key];
                            $color = \LaravelVitals\Support\Health::colorForScore($val);
                        @endphp
                        <div class="rounded-lg border border-{{ $color }}-200 dark:border-{{ $color }}-900/40 bg-{{ $color }}-50/40 dark:bg-{{ $color }}-900/10 p-4">
                            <div class="text-xs text-zinc-500 mb-1">{{ $label }}</div>
                            <div class="text-3xl font-bold text-{{ $color }}-700 dark:text-{{ $color }}-300">{{ $val ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endif

        @if ($frequentRecos->isNotEmpty())
            <flux:card>
                <div class="flex items-center gap-2 mb-4">
                    <flux:icon.light-bulb class="size-5 text-amber-500" />
                    <h2 class="font-semibold">Most frequent issues on this URL</h2>
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
            </flux:card>
        @endif

        @if ($failedAudits->isNotEmpty())
            <flux:card>
                <div class="flex items-center gap-2 mb-4">
                    <flux:icon.exclamation-circle class="size-5 text-rose-500" />
                    <h2 class="font-semibold">Recent failed audits</h2>
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
            </flux:card>
        @endif

        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.clock class="size-5 text-sky-500" />
                <h2 class="font-semibold">Audit history ({{ $history->count() }})</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Date</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Device</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Score</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">LCP</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">CLS</th>
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
                            <span class="inline-flex items-center gap-1.5 text-{{ $color }}-700 dark:text-{{ $color }}-300 font-semibold">
                                {{ $score ?? '—' }}
                                <span class="size-5 rounded bg-{{ $color }}-100 dark:bg-{{ $color }}-900/40 text-xs flex items-center justify-center font-bold">{{ $grade }}</span>
                            </span>
                        </td>
                        <td class="py-3 pr-4 text-right text-zinc-700 dark:text-zinc-300">
                            {{ $a->lcp_ms !== null ? (int) round((float) $a->lcp_ms) . 'ms' : '—' }}
                        </td>
                        <td class="py-3 pr-4 text-right text-zinc-700 dark:text-zinc-300">
                            {{ $a->cls !== null ? number_format((float) $a->cls, 2) : '—' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif
</div>
