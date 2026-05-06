<div class="space-y-10">
    {{-- Page header --}}
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-3xl font-semibold tracking-[-0.02em] text-ink-900 dark:text-ink-100">Vitals</h1>
            <p class="mt-1 text-sm text-ink-500">Performance health for {{ $urlsCount }} {{ Str::plural('URL', $urlsCount) }}</p>
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

    {{-- Main content: Health panel (60%) + Activity feed (40%) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

        {{-- Health panel — 3 cols of 5 --}}
        <div class="lg:col-span-3 border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-6">
            <p class="label-caps text-ink-400 mb-5">Health</p>

            {{-- Rings + score side by side --}}
            <div class="flex items-start gap-6">
                <x-vitals::activity-rings :scores="$averages">
                    <div class="text-2xl font-semibold tabular-nums leading-none text-ink-900 dark:text-ink-100">{{ $overall ?? '—' }}</div>
                </x-vitals::activity-rings>

                {{-- Vertical score list --}}
                <div class="flex-1 divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ([
                        ['key' => 'performance',    'label' => 'Performance'],
                        ['key' => 'accessibility',  'label' => 'Accessibility'],
                        ['key' => 'best_practices', 'label' => 'Best Practices'],
                        ['key' => 'seo',            'label' => 'SEO'],
                    ] as $stat)
                        @php
                            $val = $averages[$stat['key']] ?? null;
                            $color = \LaravelVitals\Support\Health::colorForScore($val);
                            $colorClass = match($color) {
                                'emerald' => 'text-emerald-600 dark:text-emerald-400',
                                'amber'   => 'text-amber-600 dark:text-amber-400',
                                default   => 'text-accent-600 dark:text-accent-500',
                            };
                        @endphp
                        <div class="flex items-center justify-between py-2.5 first:pt-0 last:pb-0">
                            <span class="text-sm text-ink-600 dark:text-ink-400">{{ $stat['label'] }}</span>
                            <span class="text-sm font-semibold tabular-nums {{ $colorClass }}">{{ $val ?? '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Activity feed — 2 cols of 5 --}}
        <div class="lg:col-span-2 border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-6">
            <p class="label-caps text-ink-400 mb-5">Recent audits</p>

            @if ($recent->isEmpty())
                <div class="py-6 text-center">
                    <flux:icon.signal class="size-8 text-ink-300 dark:text-ink-700 mx-auto mb-2" />
                    <p class="text-sm text-ink-500 mb-3">No audits in this period.</p>
                    <code class="text-xs bg-ink-100 dark:bg-ink-800 px-2 py-1 rounded text-ink-600 dark:text-ink-400">php artisan vitals:audit</code>
                </div>
            @else
                <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($recent->take(7) as $audit)
                        @php
                            $color = \LaravelVitals\Support\Health::colorForScore($audit->score_performance);
                            $grade = \LaravelVitals\Support\Health::grade($audit->score_performance);
                            $scoreColorClass = match($color) {
                                'emerald' => 'text-emerald-600 dark:text-emerald-400',
                                'amber'   => 'text-amber-600 dark:text-amber-400',
                                default   => 'text-accent-600 dark:text-accent-500',
                            };
                        @endphp
                        <li class="py-2 flex items-center gap-3 first:pt-0 last:pb-0">
                            <a href="{{ route('vitals.audit', $audit) }}" class="flex-1 min-w-0 group">
                                <div class="text-sm font-medium text-ink-800 dark:text-ink-200 truncate group-hover:text-accent-600 dark:group-hover:text-accent-500 transition-colors duration-150">{{ $audit->url?->label }}</div>
                                <div class="text-xs text-ink-400">{{ $audit->device }} · {{ $audit->completed_at?->diffForHumans() }}</div>
                            </a>
                            <span class="text-sm font-semibold tabular-nums shrink-0 {{ $scoreColorClass }}">{{ $audit->score_performance ?? '—' }}</span>
                        </li>
                    @endforeach
                </ul>
                @if ($recent->count() > 7)
                    <div class="mt-4 pt-3 border-t border-ink-100 dark:border-ink-800">
                        <a href="{{ route('vitals.urls') }}" class="text-xs text-ink-500 hover:text-accent-600 dark:hover:text-accent-500 transition-colors duration-150">View all URLs →</a>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Active alerts — flat section, no card --}}
    @if (count($activeAlerts) > 0)
        <div>
            <div class="flex items-center gap-2 mb-3">
                <p class="label-caps text-ink-400">Active alerts</p>
                <span class="inline-flex items-center justify-center rounded-full bg-accent-100 dark:bg-accent-700/30 text-accent-600 dark:text-accent-400 text-[10px] font-semibold h-4 min-w-4 px-1.5">{{ count($activeAlerts) }}</span>
            </div>
            <div class="border-t border-ink-200 dark:border-ink-800">
                @foreach ($activeAlerts as $alert)
                    <div class="flex items-start gap-3 py-3 border-b border-ink-100 dark:border-ink-800/60 last:border-0">
                        <span class="mt-0.5 shrink-0 {{ $alert['severity'] === 'danger' ? 'text-accent-500' : 'text-amber-500' }}">
                            @if ($alert['severity'] === 'danger')
                                <flux:icon.exclamation-circle class="size-4" />
                            @else
                                <flux:icon.exclamation-triangle class="size-4" />
                            @endif
                        </span>
                        <div class="flex-1 min-w-0">
                            <span class="text-sm text-ink-800 dark:text-ink-200">{{ $alert['title'] }}</span>
                            <span class="text-sm text-ink-400 ml-2">{{ $alert['when']->diffForHumans() }}</span>
                        </div>
                        @if ($alert['link'])
                            <a href="{{ $alert['link'] }}" class="shrink-0 text-xs text-ink-400 hover:text-accent-500 transition-colors duration-150">View →</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Top recommendations — flat section --}}
    <div>
        <p class="label-caps text-ink-400 mb-3">Top recommendations</p>
        <div class="border-t border-ink-200 dark:border-ink-800">
            @if ($topRecommendations->isEmpty())
                <p class="py-4 text-sm text-ink-500">No recommendations yet. Run an audit to see suggestions.</p>
            @else
                @foreach ($topRecommendations as $reco)
                    @php
                        $sevColorClass = match ($reco->severity) {
                            'critical' => 'text-accent-500',
                            'warning'  => 'text-amber-500',
                            default    => 'text-emerald-500',
                        };
                    @endphp
                    <div class="flex items-baseline gap-3 py-2.5 border-b border-ink-100 dark:border-ink-800/60 last:border-0">
                        <span class="shrink-0 mt-1 size-1.5 rounded-full {{ $reco->severity === 'critical' ? 'bg-accent-500' : ($reco->severity === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                        <span class="flex-1 text-sm text-ink-700 dark:text-ink-300">{{ __($reco->title_key) }}</span>
                        <span class="shrink-0 text-xs text-ink-400 tabular-nums">{{ $reco->occurrences }} {{ Str::plural('audit', $reco->occurrences) }}</span>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
