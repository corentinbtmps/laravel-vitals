<div class="space-y-6">
    {{-- Page header + period control --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Vitals</h1>
            <p class="text-sm text-ink-500 mt-1">Performance health across all monitored URLs</p>
        </div>
        <div class="flex items-center gap-1 rounded-xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-1">
            @foreach (['24h' => '24h', '7d' => '7d', '30d' => '30d', '90d' => '90d', '1y' => '1y', 'all' => 'All'] as $val => $lbl)
                <button
                    wire:click="setPeriod('{{ $val }}')"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                        {{ $period === $val
                            ? 'bg-ink-900 text-white dark:bg-ink-100 dark:text-ink-900'
                            : 'text-ink-500 hover:text-ink-900 dark:hover:text-ink-100' }}"
                >{{ $lbl }}</button>
            @endforeach
        </div>
    </div>

    {{-- Hero: activity rings + score chips --}}
    <div class="rounded-3xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-8">
        <div class="flex items-center gap-8">
            <x-vitals::activity-rings :scores="$averages">
                <div class="text-5xl font-semibold tabular-nums leading-none">{{ $overallGrade ?? '—' }}</div>
                <div class="text-sm text-ink-500 mt-1 tabular-nums">{{ $overall ?? '—' }}<span class="text-xs">/100</span></div>
            </x-vitals::activity-rings>

            <div class="flex-1">
                <div class="text-sm text-ink-500">{{ $periodLabel }}</div>
                <h2 class="text-2xl font-semibold mt-1">Health overview</h2>
                <p class="text-sm text-ink-500 mt-2 max-w-md">Performance, accessibility, and SEO scores aggregated across {{ $urlsCount }} {{ Str::plural('URL', $urlsCount) }}.</p>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ([
                ['key' => 'performance',    'label' => 'Performance',    'color' => 'accent'],
                ['key' => 'accessibility',  'label' => 'Accessibility',  'color' => 'emerald'],
                ['key' => 'best_practices', 'label' => 'Best Practices', 'color' => 'violet'],
                ['key' => 'seo',            'label' => 'SEO',            'color' => 'sky'],
            ] as $stat)
                <div class="rounded-2xl bg-paper dark:bg-ink-900 border border-ink-200/60 dark:border-ink-800/60 p-4">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-{{ $stat['color'] }}-500"></span>
                        <span class="text-xs font-medium text-ink-500 uppercase tracking-wide">{{ $stat['label'] }}</span>
                    </div>
                    <div class="mt-3 text-3xl font-semibold tabular-nums">
                        {{ $averages[$stat['key']] ?? '—' }}<span class="text-base font-normal text-ink-500">/100</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Active alerts --}}
    @if (count($activeAlerts) > 0)
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <flux:icon.bell class="size-5 text-accent-500" />
                    <h2 class="text-base font-semibold">Active alerts</h2>
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
        </div>
    @endif

    {{-- Two-column: top recos + activity feed --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Top issues to fix</h3>
                    <p class="text-sm text-ink-500 mt-1">Most impactful issues to fix first</p>
                </div>
            </div>
            @if ($topRecommendations->isEmpty())
                <p class="text-sm text-ink-500">No recommendations yet. Run an audit to see suggestions.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($topRecommendations as $reco)
                        <li class="flex items-start gap-3">
                            <flux:badge color="{{ $reco->severity === 'critical' ? 'rose' : ($reco->severity === 'warning' ? 'amber' : 'sky') }}" size="sm">
                                {{ $reco->severity }}
                            </flux:badge>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium">{{ __($reco->title_key) }}</div>
                                <div class="text-xs text-ink-500">{{ $reco->occurrences }} occurrence(s)</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Recent audits</h3>
                    <p class="text-sm text-ink-500 mt-1">{{ $periodLabel }}</p>
                </div>
            </div>
            @if ($recent->isEmpty())
                <div class="text-center py-6">
                    <flux:icon.signal class="size-10 text-ink-300 mx-auto mb-2" />
                    <p class="text-sm text-ink-500 mb-3">No audits in this period.</p>
                    <code class="text-xs bg-ink-100 dark:bg-ink-800 px-2 py-1 rounded">php artisan vitals:audit</code>
                </div>
            @else
                <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($recent->take(8) as $audit)
                        @php
                            $color = \LaravelVitals\Support\Health::colorForScore($audit->score_performance);
                            $grade = \LaravelVitals\Support\Health::grade($audit->score_performance);
                        @endphp
                        <li class="py-2.5 flex items-center gap-3">
                            <span class="size-9 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-700 dark:text-{{ $color }}-300 flex items-center justify-center font-bold text-sm">{{ $grade }}</span>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('vitals.audit', $audit) }}" class="text-sm font-medium hover:underline truncate block">{{ $audit->url?->label }}</a>
                                <div class="text-xs text-ink-500">{{ $audit->device }} · {{ $audit->completed_at?->diffForHumans() }}</div>
                            </div>
                            <flux:button href="{{ route('vitals.audit', $audit) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View audit" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
