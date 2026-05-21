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

    {{-- Route baseline table --}}
    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
        <h3 class="text-base font-semibold mb-1">{{ __('vitals::vitals.queries.baseline_title') }}</h3>
        <p class="text-sm text-ink-500 mb-4">{{ __('vitals::vitals.queries.baseline_subtitle') }}</p>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('vitals::vitals.queries.col_route') }}</flux:table.column>
                <flux:table.column align="end">{{ __('vitals::vitals.queries.col_samples') }}</flux:table.column>
                <flux:table.column align="end">{{ __('vitals::vitals.queries.col_typical_queries') }}</flux:table.column>
                <flux:table.column align="end">{{ __('vitals::vitals.queries.col_worst_queries') }}</flux:table.column>
                <flux:table.column align="end">{{ __('vitals::vitals.queries.col_typical_time') }}</flux:table.column>
                <flux:table.column align="end">{{ __('vitals::vitals.queries.col_worst_time') }}</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($routes as $row)
                    @php
                        $isSelected = $selectedRoute === $row['route'];
                        $p75 = $row['queries_p75'];
                        $p95 = $row['queries_p95'];
                        $p75Color = $p75 === null ? 'zinc' : ($p75 >= 50 ? 'rose' : ($p75 >= 20 ? 'amber' : 'emerald'));
                        $p95Color = $p95 === null ? 'zinc' : ($p95 >= 100 ? 'rose' : ($p95 >= 50 ? 'amber' : 'emerald'));
                    @endphp
                    <flux:table.row
                        wire:click="selectRoute('{{ $row['route'] }}')"
                        @class([
                            'cursor-pointer transition-colors',
                            'bg-accent-50/40 dark:bg-accent-900/10' => $isSelected,
                        ])
                    >
                        <flux:table.cell>
                            <div class="flex items-center gap-2 flex-wrap">
                                <code class="text-xs text-ink-700 dark:text-ink-300 font-medium">{{ $row['route'] }}</code>
                                @if ($row['has_n_plus_one'])
                                    <flux:badge color="rose" size="sm">{{ __('vitals::vitals.queries.n_plus_one_badge') }}</flux:badge>
                                @endif
                                @if ($row['regression'])
                                    <flux:badge color="amber" size="sm" title="{{ $row['regression_label'] }}">
                                        ↑ {{ __('vitals::vitals.queries.regression') }}
                                    </flux:badge>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <span class="tabular-nums text-sm">{{ number_format($row['count']) }}</span>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @if ($p75 !== null)
                                <flux:badge color="{{ $p75Color }}" size="sm">{{ number_format($p75, 0) }}</flux:badge>
                            @else
                                <span class="text-ink-300">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @if ($p95 !== null)
                                <flux:badge color="{{ $p95Color }}" size="sm">{{ number_format($p95, 0) }}</flux:badge>
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
        <p class="mt-3 text-xs text-ink-400">{{ __('vitals::vitals.queries.legend') }}</p>
    </div>

    {{-- Per-route detail panel --}}
    @if ($routeDetail !== null)
        <div class="rounded-2xl border border-accent-200 dark:border-accent-900/60 bg-accent-50/30 dark:bg-accent-900/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-accent-200/60 dark:border-accent-900/40 flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wider text-accent-700 dark:text-accent-400">{{ __('vitals::vitals.queries.detail_heading') }}</p>
                    <code class="text-sm font-semibold text-ink-900 dark:text-ink-100">{{ $routeDetail['route'] }}</code>
                </div>
                <flux:button
                    wire:click="selectRoute('{{ $routeDetail['route'] }}')"
                    variant="ghost"
                    size="xs"
                    icon="x-mark"
                >{{ __('vitals::vitals.actions.close') }}</flux:button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-px bg-ink-100 dark:bg-ink-800">
                {{-- Affected URLs --}}
                <div class="bg-paper dark:bg-ink-900 p-5">
                    <h4 class="text-sm font-semibold mb-3">{{ __('vitals::vitals.queries.affected_urls') }}</h4>
                    @if (!empty($routeDetail['urls']))
                        <ul class="space-y-1.5">
                            @foreach ($routeDetail['urls'] as $u)
                                <li class="flex items-center justify-between gap-3 text-sm">
                                    <a href="{{ route('vitals.url', $u['id']) }}"
                                       class="font-medium text-accent-600 hover:text-accent-700 dark:text-accent-400 dark:hover:text-accent-300 transition-colors truncate">
                                        {{ $u['label'] }}
                                    </a>
                                    <span class="text-xs text-ink-400 tabular-nums">{{ $u['audit_count'] }} {{ __('vitals::vitals.queries.audits_short') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-ink-400">—</p>
                    @endif
                </div>

                {{-- Recent audits --}}
                <div class="bg-paper dark:bg-ink-900 p-5">
                    <h4 class="text-sm font-semibold mb-3">{{ __('vitals::vitals.queries.recent_audits') }}</h4>
                    @if (!empty($routeDetail['recent']))
                        <ul class="space-y-1.5">
                            @foreach ($routeDetail['recent'] as $r)
                                <li class="flex items-center justify-between gap-3 text-sm">
                                    <a href="{{ route('vitals.audit', $r['audit_id']) }}"
                                       class="text-xs text-accent-600 hover:text-accent-700 dark:text-accent-400 dark:hover:text-accent-300 transition-colors truncate">
                                        {{ $r['url_label'] ?? '—' }} · {{ $r['completed_at']?->format('M j, H:i') ?? '—' }}
                                    </a>
                                    <span class="text-xs text-ink-500 tabular-nums whitespace-nowrap">
                                        {{ $r['queries_count'] !== null ? $r['queries_count'] . ' q' : '—' }} ·
                                        {{ $r['queries_time_ms'] !== null ? number_format($r['queries_time_ms'], 0) . 'ms' : '—' }}
                                        @if ($r['n_plus_one'])
                                            <flux:badge color="rose" size="sm" class="ml-1">N+1</flux:badge>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-ink-400">—</p>
                    @endif
                </div>
            </div>

            {{-- Repeated SQL patterns from N+1 findings --}}
            @if (!empty($routeDetail['patterns']))
                <div class="bg-paper dark:bg-ink-900 p-5 border-t border-accent-200/60 dark:border-accent-900/40">
                    <h4 class="text-sm font-semibold mb-3">{{ __('vitals::vitals.queries.top_patterns') }}</h4>
                    <ul class="space-y-2">
                        @foreach ($routeDetail['patterns'] as $p)
                            <li class="rounded-lg border border-ink-200 dark:border-ink-800 bg-canvas dark:bg-ink-950 p-3 text-xs font-mono">
                                <div class="flex items-start justify-between gap-3 mb-1">
                                    <code class="text-ink-700 dark:text-ink-300 flex-1 break-all">{{ $p['sql'] }}</code>
                                    <flux:badge color="rose" size="sm" class="shrink-0">× {{ $p['occurrences'] }}</flux:badge>
                                </div>
                                @if ($p['caller'])
                                    @php
                                        [$callerFile, $callerLine] = array_pad(explode(':', $p['caller'], 2), 2, null);
                                        $editor = \LaravelVitals\Support\EditorUrl::for($callerFile, $callerLine !== null ? (int) $callerLine : null);
                                    @endphp
                                    @if ($editor)
                                        <a href="{{ $editor }}" class="text-accent-600 dark:text-accent-400 hover:underline">{{ $p['caller'] }}</a>
                                    @else
                                        <span class="text-ink-500">{{ $p['caller'] }}</span>
                                    @endif
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

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
                    <flux:badge color="{{ $hog['memory_p75_mb'] >= 100 ? 'rose' : ($hog['memory_p75_mb'] >= 50 ? 'amber' : 'zinc') }}" size="sm">
                        {{ $hog['memory_p75_mb'] }} MB
                    </flux:badge>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif
</div>
