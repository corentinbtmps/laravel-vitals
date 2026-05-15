<div class="space-y-6">
    <livewire:vitals::components.onboarding-banner />

    {{-- Page header + period control --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('vitals::vitals.pages.overview.title') }}</h1>
            <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.pages.overview.subtitle') }}</p>
        </div>
        <div class="overflow-x-auto -mx-2 md:mx-0">
            <div class="inline-flex items-center gap-1 rounded-xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-1 whitespace-nowrap mx-2 md:mx-0">
                @foreach (\LaravelVitals\Enums\Period::cases() as $case)
                    <button
                        wire:click="setPeriod('{{ $case->value }}')"
                        @class([
                            'px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
                            'bg-ink-900 text-white dark:bg-ink-100 dark:text-ink-900' => $period === $case,
                            'text-ink-500 hover:text-ink-900 dark:hover:text-ink-100' => $period !== $case,
                        ])
                    >{{ $case->buttonLabel() }}</button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Empty state: no URLs configured --}}
    @if ($urlsCount === 0)
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.link class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.empty.overview_no_urls.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.empty.overview_no_urls.body') }}</p>
            <div class="mt-5 flex items-center justify-center gap-2">
                <flux:button href="{{ route('vitals.urls') }}" variant="filled" color="accent" icon="link" size="sm">{{ __('vitals::vitals.empty.overview_no_urls.cta') }}</flux:button>
            </div>
            <code class="mt-6 inline-block rounded-md bg-ink-100 dark:bg-ink-800 px-3 py-1.5 text-xs text-ink-600 dark:text-ink-400 font-mono">php artisan vitals:demo</code>
        </div>
    @elseif ($recent->isEmpty())
        {{-- Empty state: URLs configured but no audits yet --}}
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.signal class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.empty.overview_no_audits.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.empty.overview_no_audits.body') }}</p>
            <div class="mt-5 flex items-center justify-center gap-2">
                <flux:button href="{{ route('vitals.urls') }}" variant="filled" color="accent" icon="link" size="sm">{{ __('vitals::vitals.empty.overview_no_audits.cta') }}</flux:button>
            </div>
            <code class="mt-6 inline-block rounded-md bg-ink-100 dark:bg-ink-800 px-3 py-1.5 text-xs text-ink-600 dark:text-ink-400 font-mono">php artisan vitals:audit</code>
        </div>
    @else

    {{-- Lens cards: 4 metrics with sparklines --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach ([
            ['key' => 'performance',    'label' => 'Performance',    'color' => 'accent',  'hex' => '#f43f5e'],
            ['key' => 'accessibility',  'label' => 'Accessibility',  'color' => 'emerald', 'hex' => '#10b981'],
            ['key' => 'best_practices', 'label' => 'Best Practices', 'color' => 'violet',  'hex' => '#8b5cf6'],
            ['key' => 'seo',            'label' => 'SEO',            'color' => 'sky',     'hex' => '#0ea5e9'],
        ] as $metric)
            @php
                $score  = $averages[$metric['key']] ?? null;
                $delta  = $metricDeltas[$metric['key']] ?? null;
                $series = $metricTrends[$metric['key']] ?? [];
            @endphp
            <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-4 lg:p-5">
                {{-- Top row: label + delta --}}
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.08em] text-ink-500">{{ $metric['label'] }}</span>
                    @if ($delta !== null)
                        @if ($delta > 0)
                            <span class="inline-flex items-center gap-0.5 text-xs font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">
                                <flux:icon.arrow-trending-up class="size-3" />+{{ $delta }}
                            </span>
                        @elseif ($delta < 0)
                            <span class="inline-flex items-center gap-0.5 text-xs font-semibold tabular-nums text-accent-600 dark:text-accent-400">
                                <flux:icon.arrow-trending-down class="size-3" />{{ $delta }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-0.5 text-xs font-medium text-ink-400">→</span>
                        @endif
                    @endif
                </div>

                {{-- Score --}}
                <div class="mt-2 text-3xl font-semibold tabular-nums text-ink-900 dark:text-ink-100 leading-none">
                    {{ $score ?? '—' }}<span class="text-base font-normal text-ink-400">/100</span>
                </div>

                {{-- Sparkline --}}
                <div class="mt-4 -mx-1"
                    x-data="{
                        chart: null,
                        series: {{ Js::from($series) }},
                        hex: '{{ $metric['hex'] }}',
                        label: '{{ $metric['label'] }}',
                        render() {
                            if (this.series.length < 2 || this.chart) return;
                            this.chart = new ApexCharts(this.$refs.spark, {
                                chart: { type: 'area', height: 48, sparkline: { enabled: true }, animations: { enabled: false } },
                                series: [{ name: this.label, data: this.series }],
                                stroke: { curve: 'smooth', width: 2 },
                                fill: {
                                    type: 'gradient',
                                    gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0, stops: [0, 100] }
                                },
                                colors: [this.hex],
                                tooltip: { enabled: false },
                                yaxis: { min: 0, max: 100 },
                            });
                            this.chart.render();
                        },
                        update(newSeries) {
                            this.series = newSeries;
                            if (this.series.length < 2) {
                                if (this.chart) {
                                    this.chart.destroy();
                                    this.chart = null;
                                }
                                return;
                            }
                            if (this.chart) {
                                this.chart.updateSeries([{ data: this.series }]);
                            } else {
                                this.render();
                            }
                        },
                        init() {
                            this.render();
                            this.$wire.on('sparklineUpdated', (data) => {
                                this.update(data.trends['{{ $metric['key'] }}'] ?? []);
                            });
                        }
                    }"
                >
                    <div x-ref="spark" class="h-12"></div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Daily summary card --}}
    @if ($dailySummary['audits'] > 0)
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 px-5 py-4">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                <span class="font-semibold text-ink-700 dark:text-ink-300">{{ __('vitals::vitals.overview.yesterday') }}</span>
                <span class="text-ink-500">{{ __('vitals::vitals.overview.daily_audits', ['count' => $dailySummary['audits']]) }}</span>
                @if ($dailySummary['regressions'] > 0)
                    <span class="text-accent-600 dark:text-accent-400">· {{ __('vitals::vitals.overview.daily_regressions', ['count' => $dailySummary['regressions']]) }}</span>
                @endif
                @if ($dailySummary['fixed'] > 0)
                    <span class="text-emerald-600 dark:text-emerald-400">· {{ __('vitals::vitals.overview.daily_fixed', ['count' => $dailySummary['fixed']]) }}</span>
                @endif
                @if ($dailySummary['lcp_improvement_pct'] !== null && $dailySummary['lcp_improvement_pct'] > 0)
                    <span class="text-violet-600 dark:text-violet-400">· {{ __('vitals::vitals.overview.daily_lcp', ['pct' => $dailySummary['lcp_improvement_pct']]) }}</span>
                @endif
            </div>
        </div>
    @endif

    {{-- API usage panel (PageSpeed users only) --}}
    @if ($apiUsage['calls'] > 0)
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <flux:icon name="cloud" class="size-4 text-sky-500" />
                    <span class="text-sm font-semibold">{{ __('vitals::vitals.overview.api_usage') }}</span>
                </div>
                <span class="text-xs text-ink-500 tabular-nums">{{ number_format($apiUsage['calls']) }} / {{ number_format($apiUsage['limit']) }}</span>
            </div>
            @php $pct = min(100, $apiUsage['calls'] / $apiUsage['limit'] * 100); @endphp
            <div class="w-full rounded-full h-1.5 bg-ink-100 dark:bg-ink-800">
                <div @class([
                    'h-1.5 rounded-full',
                    'bg-accent-400' => $pct > 80,
                    'bg-sky-400'    => $pct <= 80,
                ]) style="width: {{ $pct }}%"></div>
            </div>
        </div>
    @endif

    {{-- Active alerts --}}
    @if (count($activeAlerts) > 0)
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <flux:icon.bell class="size-5 text-accent-500" />
                    <h2 class="text-base font-semibold">{{ __('vitals::vitals.overview_page.active_alerts') }}</h2>
                </div>
                <flux:badge color="rose">{{ count($activeAlerts) }}</flux:badge>
            </div>
            <div class="space-y-2">
                @foreach ($activeAlerts as $alert)
                    <flux:callout
                        variant="{{ $alert['severity'] === 'danger' ? 'danger' : 'warning' }}"
                        icon="{{ $alert['severity'] === 'danger' ? 'exclamation-circle' : 'exclamation-triangle' }}"
                    >
                        <flux:callout.heading>{{ $alert['title'] }}</flux:callout.heading>
                        <flux:callout.text>
                            {{ $alert['when']->diffForHumans() }} —
                            @if ($alert['link'])
                                <a href="{{ $alert['link'] }}" class="underline">{{ __('vitals::vitals.actions.view_audit') }}</a>
                            @endif
                        </flux:callout.text>
                    </flux:callout>
                @endforeach
            </div>
            @if (count($activeAlerts) >= 3)
                <div class="mt-3 text-right">
                    <a href="{{ route('vitals.issues', ['tab' => 'top']) }}" class="text-xs text-ink-500 hover:text-accent-600 transition-colors">
                        {{ __('vitals::vitals.overview.view_all_alerts') }} →
                    </a>
                </div>
            @endif
        </div>
    @endif

    {{-- Two-column: top recos + activity feed --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">{{ __('vitals::vitals.overview_page.top_issues') }}</h3>
                    <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.overview_page.top_issues_subtitle') }}</p>
                </div>
            </div>
            @if ($topRecommendations->isEmpty())
                <p class="text-sm text-ink-500">{{ __('vitals::vitals.overview_page.no_recommendations') }}</p>
            @else
                <ul class="space-y-3">
                    @foreach ($topRecommendations as $reco)
                        <li class="flex items-start gap-3">
                            <flux:badge color="{{ $reco->severity->fluxBadgeColor() }}" size="sm">
                                {{ $reco->severity->label() }}
                            </flux:badge>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium">{{ __($reco->title_key) }}</div>
                                <div class="text-xs text-ink-500">{{ $reco->occurrences }} {{ __('vitals::vitals.overview_page.occurrence') }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-3 text-right">
                    <a href="{{ route('vitals.issues', ['tab' => 'all']) }}" class="text-xs text-ink-500 hover:text-accent-600 transition-colors">
                        {{ __('vitals::vitals.overview.view_all_recommendations') }} →
                    </a>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">{{ __('vitals::vitals.overview_page.recent_audits') }}</h3>
                    <p class="text-sm text-ink-500 mt-1">{{ $periodLabel }}</p>
                </div>
            </div>
            <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                @foreach ($recent->take(8) as $audit)
                    @php
                        $color = \LaravelVitals\Support\Health::colorForScore($audit->score_performance);
                        $grade = \LaravelVitals\Support\Health::grade($audit->score_performance);
                    @endphp
                    <li class="py-2.5 flex items-center gap-3">
                        <span @class([
                            'size-9 rounded-full flex items-center justify-center font-bold text-sm',
                            ...\LaravelVitals\Support\ScoreColorClasses::avatar($audit->score_performance),
                        ])>{{ $grade }}</span>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('vitals.audit', $audit) }}" class="text-sm font-medium hover:underline truncate block">{{ $audit->url?->label }}</a>
                            <div class="text-xs text-ink-500">{{ $audit->device }} · {{ $audit->completed_at?->diffForHumans() }}</div>
                        </div>
                        <flux:button href="{{ route('vitals.audit', $audit) }}" variant="ghost" size="sm" icon="arrow-right" />
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>
