<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('vitals::vitals.queries.title') }}</h1>
            <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.queries.subtitle') }}</p>
        </div>
        <div class="flex gap-1">
            @foreach (\LaravelVitals\Enums\Period::availableFor((int) config('vitals.retention.days', 90)) as $case)
                <button
                    wire:click="setPeriod('{{ $case->value }}')"
                    @class([
                        'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                        'bg-accent-500 text-white' => $period === $case,
                        'bg-paper dark:bg-ink-900 border border-ink-200 dark:border-ink-800 text-ink-500 hover:text-ink-700 dark:hover:text-ink-300' => $period !== $case,
                    ])
                >{{ $case->buttonLabel() }}</button>
            @endforeach
        </div>
    </div>

    @if (empty($routes))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.circle-stack class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.queries.empty.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.queries.empty.body') }}</p>
        </div>
    @else

    {{-- Query baseline table --}}
    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
        <h3 class="text-base font-semibold mb-1">{{ __('vitals::vitals.queries.baseline_title') }}</h3>
        <p class="text-sm text-ink-500 mb-4">{{ __('vitals::vitals.queries.baseline_subtitle') }}</p>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('vitals::vitals.queries.col_route') }}</flux:table.column>
                <flux:table.column align="end">{{ __('vitals::vitals.queries.col_samples') }}</flux:table.column>
                <flux:table.column align="end">avg queries</flux:table.column>
                <flux:table.column align="end">p75 queries</flux:table.column>
                <flux:table.column align="end">p95 queries</flux:table.column>
                <flux:table.column align="end">p75 time</flux:table.column>
                <flux:table.column align="end">p95 time</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($routes as $row)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <code class="text-xs text-ink-600 dark:text-ink-400">{{ $row['route'] }}</code>
                                @if ($row['regression'])
                                    <flux:badge color="rose" size="sm" title="{{ $row['regression_label'] }}">
                                        ↑ {{ __('vitals::vitals.queries.regression') }}
                                    </flux:badge>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <span class="tabular-nums text-sm">{{ number_format($row['count']) }}</span>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <span class="tabular-nums text-sm text-ink-600 dark:text-ink-400">
                                {{ $row['queries_avg'] !== null ? number_format($row['queries_avg'], 1) : '—' }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @php
                                $p75 = $row['queries_p75'];
                                $color = $p75 === null ? 'zinc' : ($p75 >= 50 ? 'rose' : ($p75 >= 20 ? 'yellow' : 'zinc'));
                            @endphp
                            @if ($p75 !== null)
                                <flux:badge color="{{ $color }}" size="sm">{{ number_format($p75, 0) }}</flux:badge>
                            @else
                                <span class="text-ink-300">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @php
                                $p95 = $row['queries_p95'];
                                $color = $p95 === null ? 'zinc' : ($p95 >= 100 ? 'rose' : ($p95 >= 50 ? 'yellow' : 'zinc'));
                            @endphp
                            @if ($p95 !== null)
                                <flux:badge color="{{ $color }}" size="sm">{{ number_format($p95, 0) }}</flux:badge>
                            @else
                                <span class="text-ink-300">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <span class="tabular-nums text-sm text-ink-600 dark:text-ink-400">
                                {{ $row['time_p75'] !== null ? number_format($row['time_p75'], 1) . 'ms' : '—' }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <span class="tabular-nums text-sm text-ink-600 dark:text-ink-400">
                                {{ $row['time_p95'] !== null ? number_format($row['time_p95'], 1) . 'ms' : '—' }}
                            </span>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Memory hogs panel --}}
    @if (count($memoryHogs) > 0)
    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
        <h3 class="text-base font-semibold mb-1">{{ __('vitals::vitals.queries.memory_hogs_title') }}</h3>
        <p class="text-sm text-ink-500 mb-4">{{ __('vitals::vitals.queries.memory_hogs_subtitle') }}</p>
        <div class="space-y-3">
            @php $maxMb = $memoryHogs[0]['memory_p75_mb'] ?? 1; @endphp
            @foreach ($memoryHogs as $i => $hog)
                <div class="flex items-center gap-3">
                    <span class="w-5 text-xs text-ink-400 tabular-nums">{{ $i + 1 }}</span>
                    <code class="text-xs font-mono text-ink-600 dark:text-ink-400 flex-1 min-w-0 truncate">{{ $hog['route'] }}</code>
                    <div class="flex-1 max-w-xs bg-ink-100 dark:bg-ink-800 rounded-full h-2 overflow-hidden">
                        <div class="h-full bg-accent-500 rounded-full" style="width:{{ round($hog['memory_p75_mb'] / $maxMb * 100) }}%"></div>
                    </div>
                    <flux:badge color="{{ $hog['memory_p75_mb'] >= 100 ? 'rose' : ($hog['memory_p75_mb'] >= 50 ? 'yellow' : 'zinc') }}" size="sm">
                        {{ $hog['memory_p75_mb'] }} MB
                    </flux:badge>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif
</div>
