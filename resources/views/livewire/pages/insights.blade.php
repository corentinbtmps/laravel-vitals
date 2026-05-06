<div class="space-y-10">
    <div>
        <h1 class="text-3xl font-semibold tracking-[-0.02em] text-ink-900 dark:text-ink-100">Insights</h1>
        <p class="mt-1 text-sm text-ink-500">Cross-URL trends and opportunities from the last 7 days</p>
    </div>

    {{-- Quick wins — flat section --}}
    <div>
        <p class="label-caps text-ink-400 mb-3">Quick wins</p>
        <div class="border-t border-ink-200 dark:border-ink-800">
            @if ($quickWins->isEmpty())
                <p class="py-4 text-sm text-ink-500">No prioritized issues found in the last 7 days.</p>
            @else
                @foreach ($quickWins as $w)
                    @php
                        $dot = match ($w->severity) {
                            'critical' => 'bg-accent-500',
                            'warning'  => 'bg-amber-500',
                            default    => 'bg-emerald-500',
                        };
                        $sevTextClass = match ($w->severity) {
                            'critical' => 'text-accent-600 dark:text-accent-500',
                            'warning'  => 'text-amber-600 dark:text-amber-400',
                            default    => 'text-emerald-600 dark:text-emerald-400',
                        };
                    @endphp
                    <div class="flex items-baseline gap-3 py-2.5 border-b border-ink-100 dark:border-ink-800/60 last:border-0">
                        <span class="mt-1.5 shrink-0 size-1.5 rounded-full {{ $dot }}"></span>
                        <div class="flex-1 min-w-0">
                            <span class="text-sm text-ink-700 dark:text-ink-300">{{ __($w->title_key) }}</span>
                        </div>
                        <span class="shrink-0 text-xs text-ink-400 tabular-nums">{{ $w->occurrences }} occ. / {{ $w->audit_count }} audits</span>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Worsening / Improving --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
            <p class="label-caps text-ink-400 mb-3">Worsening URLs</p>
            <div class="border-t border-ink-200 dark:border-ink-800">
                @if (empty($worsening))
                    <p class="py-4 text-sm text-ink-500">No regressions detected.</p>
                @else
                    @foreach ($worsening as $w)
                        <div class="flex items-center gap-3 py-2.5 border-b border-ink-100 dark:border-ink-800/60 last:border-0">
                            <a href="{{ route('vitals.url', $w['url']->id) }}" class="flex-1 min-w-0 group">
                                <div class="text-sm font-medium text-ink-800 dark:text-ink-200 group-hover:text-accent-500 transition-colors duration-150">{{ $w['url']->label }}</div>
                                <div class="text-xs text-ink-400 tabular-nums">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
                            </a>
                            <span class="text-sm font-semibold tabular-nums text-accent-500">{{ $w['delta'] }}</span>
                            <flux:button href="{{ route('vitals.url', $w['url']->id) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View URL" />
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div>
            <p class="label-caps text-ink-400 mb-3">Improving URLs</p>
            <div class="border-t border-ink-200 dark:border-ink-800">
                @if (empty($improving))
                    <p class="py-4 text-sm text-ink-500">No improvements yet.</p>
                @else
                    @foreach ($improving as $w)
                        <div class="flex items-center gap-3 py-2.5 border-b border-ink-100 dark:border-ink-800/60 last:border-0">
                            <a href="{{ route('vitals.url', $w['url']->id) }}" class="flex-1 min-w-0 group">
                                <div class="text-sm font-medium text-ink-800 dark:text-ink-200 group-hover:text-accent-500 transition-colors duration-150">{{ $w['url']->label }}</div>
                                <div class="text-xs text-ink-400 tabular-nums">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
                            </a>
                            <span class="text-sm font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">+{{ $w['delta'] }}</span>
                            <flux:button href="{{ route('vitals.url', $w['url']->id) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View URL" />
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Top third parties --}}
    @if (! empty($topThirdParties))
        <div>
            <p class="label-caps text-ink-400 mb-3">Top third-party costs</p>
            <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-200 dark:border-ink-800">
                            <th class="py-3 pl-5 pr-4 text-left label-caps text-ink-400">Entity</th>
                            <th class="py-3 pr-4 label-caps text-ink-400 text-right">Audits</th>
                            <th class="py-3 pr-5 label-caps text-ink-400 text-right">Total blocking</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($topThirdParties as $tp)
                        <tr class="border-b border-ink-100 dark:border-ink-800/50 last:border-0">
                            <td class="py-2.5 pl-5 pr-4 font-medium text-ink-700 dark:text-ink-300">{{ $tp['name'] }}</td>
                            <td class="py-2.5 pr-4 text-right text-ink-500 tabular-nums">{{ $tp['occurrences'] }}</td>
                            <td class="py-2.5 pr-5 text-right font-semibold tabular-nums text-amber-600 dark:text-amber-400">{{ (int) round($tp['total_blocking_ms']) }}ms</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
