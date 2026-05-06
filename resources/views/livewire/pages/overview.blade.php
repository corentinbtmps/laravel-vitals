<div class="space-y-6">
    {{-- Hero: overall health card --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <flux:card class="lg:col-span-1 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 via-rose-500/5 to-transparent pointer-events-none"></div>
            <div class="relative">
                <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <flux:icon.heart class="size-4 text-rose-500" />
                    Overall health
                </div>
                @if ($overall !== null)
                    <div class="mt-3 flex items-baseline gap-3">
                        <span class="text-7xl font-bold tracking-tight text-{{ $overallColor }}-500">{{ $overallGrade }}</span>
                        <span class="text-3xl font-semibold text-zinc-700 dark:text-zinc-300">{{ $overall }}</span>
                    </div>
                    @if (! empty($perfTrend))
                        @php
                            $sparkData = array_values($perfTrend);
                            $sparkLabels = array_keys($perfTrend);
                        @endphp
                        <div id="vitals-perf-sparkline" class="mt-4 -mx-2"></div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                new ApexCharts(document.querySelector('#vitals-perf-sparkline'), {
                                    chart: { type: 'area', height: 80, sparkline: { enabled: true }, animations: { enabled: false } },
                                    series: [{ name: 'Performance', data: @json($sparkData) }],
                                    xaxis: { categories: @json($sparkLabels) },
                                    stroke: { curve: 'smooth', width: 2 },
                                    colors: ['#f43f5e'],
                                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.0 } },
                                    tooltip: { x: { show: false }, marker: { show: false } },
                                }).render();
                            });
                        </script>
                    @endif
                    <p class="mt-2 text-xs text-zinc-500">based on {{ $recent->count() }} audits in the last 7 days</p>
                @else
                    <div class="mt-3 text-2xl font-semibold text-zinc-500">No data</div>
                    <p class="mt-2 text-xs text-zinc-500">Run <code class="text-rose-500">php artisan vitals:audit</code> to start</p>
                @endif
            </div>
        </flux:card>

        {{-- Score breakdown: 4 mini cards --}}
        <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach ([
                'performance'    => ['label' => 'Performance',    'icon' => 'bolt'],
                'accessibility'  => ['label' => 'Accessibility',  'icon' => 'eye'],
                'best_practices' => ['label' => 'Best Practices', 'icon' => 'shield-check'],
                'seo'            => ['label' => 'SEO',            'icon' => 'magnifying-glass'],
            ] as $key => $meta)
                @php
                    $score = $averages[$key];
                    $color = \LaravelVitals\Support\Health::colorForScore($score);
                @endphp
                <flux:card class="!p-4 relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-{{ $color }}-500"></div>
                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                        <flux:icon name="{{ $meta['icon'] }}" class="size-3.5" />
                        {{ $meta['label'] }}
                    </div>
                    <div class="mt-2 text-3xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $score ?? '—' }}</div>
                </flux:card>
            @endforeach
        </div>
    </div>

    {{-- Active alerts --}}
    @if (count($activeAlerts) > 0)
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <flux:icon.bell class="size-5 text-rose-500" />
                    <h2 class="font-semibold">Active alerts</h2>
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
                                <a href="{{ $alert['link'] }}" class="underline">View audit</a>
                            @endif
                        </flux:callout.text>
                    </flux:callout>
                @endforeach
            </div>
        </flux:card>
    @endif

    {{-- Two-column: top recos + activity feed --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.light-bulb class="size-5 text-amber-500" />
                <h2 class="font-semibold">Top issues to fix</h2>
            </div>
            @if ($topRecommendations->isEmpty())
                <p class="text-sm text-zinc-500">No recommendations yet. Run an audit to see suggestions.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($topRecommendations as $reco)
                        <li class="flex items-start gap-3">
                            <flux:badge color="{{ $reco->severity === 'critical' ? 'rose' : ($reco->severity === 'warning' ? 'amber' : 'sky') }}" size="sm">
                                {{ $reco->severity }}
                            </flux:badge>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium">{{ __($reco->title_key) }}</div>
                                <div class="text-xs text-zinc-500">{{ $reco->occurrences }} occurrence(s)</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.clock class="size-5 text-sky-500" />
                <h2 class="font-semibold">Recent audits</h2>
            </div>
            @if ($recent->isEmpty())
                <div class="text-center py-6">
                    <flux:icon.signal class="size-10 text-zinc-300 mx-auto mb-2" />
                    <p class="text-sm text-zinc-500 mb-3">No audits in the last 7 days.</p>
                    <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">php artisan vitals:audit</code>
                </div>
            @else
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($recent->take(8) as $audit)
                        @php
                            $color = \LaravelVitals\Support\Health::colorForScore($audit->score_performance);
                            $grade = \LaravelVitals\Support\Health::grade($audit->score_performance);
                        @endphp
                        <li class="py-2.5 flex items-center gap-3">
                            <span class="size-9 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-700 dark:text-{{ $color }}-300 flex items-center justify-center font-bold text-sm">{{ $grade }}</span>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('vitals.audit', $audit) }}" class="text-sm font-medium hover:underline truncate block">{{ $audit->url?->label }}</a>
                                <div class="text-xs text-zinc-500">{{ $audit->device }} · {{ $audit->completed_at?->diffForHumans() }}</div>
                            </div>
                            <flux:button href="{{ route('vitals.audit', $audit) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View audit" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </flux:card>
    </div>
</div>
