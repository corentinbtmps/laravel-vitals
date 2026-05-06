<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Insights</h1>
        <p class="text-sm text-zinc-500 mt-1">Cross-URL trends and opportunities aggregated from the last 7 days</p>
    </div>

    {{-- Quick wins --}}
    <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold">Quick wins</h3>
                <p class="text-sm text-zinc-500 mt-1">Most impactful issues to fix first</p>
            </div>
        </div>
        @if ($quickWins->isEmpty())
            <p class="text-sm text-zinc-500">No prioritized issues found in the last 7 days.</p>
        @else
            <ul class="space-y-3">
                @foreach ($quickWins as $w)
                    @php
                        $sevColor = match ($w->severity) {
                            'critical' => 'rose',
                            'warning'  => 'amber',
                            default    => 'sky',
                        };
                    @endphp
                    <li class="flex items-start gap-3 p-3 rounded-2xl border border-{{ $sevColor }}-200/60 dark:border-{{ $sevColor }}-900/40 bg-{{ $sevColor }}-50/30 dark:bg-{{ $sevColor }}-900/5">
                        <flux:badge color="{{ $sevColor }}" size="sm">{{ $w->severity }}</flux:badge>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm">{{ __($w->title_key) }}</div>
                            <div class="text-xs text-zinc-500 mt-0.5">{{ $w->occurrences }} occurrence(s) across {{ $w->audit_count }} audit(s)</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Worsening / Improving --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Worsening URLs</h3>
                    <p class="text-sm text-zinc-500 mt-1">Regressions detected</p>
                </div>
            </div>
            @if (empty($worsening))
                <p class="text-sm text-zinc-500">No regressions detected.</p>
            @else
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($worsening as $w)
                        <li class="py-2 flex items-center gap-3">
                            <a href="{{ route('vitals.url', $w['url']->id) }}" class="flex-1 min-w-0 hover:text-rose-500">
                                <div class="font-medium text-sm">{{ $w['url']->label }}</div>
                                <div class="text-xs text-zinc-500 tabular-nums">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
                            </a>
                            <flux:badge color="rose" size="sm">{{ $w['delta'] }}</flux:badge>
                            <flux:button href="{{ route('vitals.url', $w['url']->id) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View URL" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Improving URLs</h3>
                    <p class="text-sm text-zinc-500 mt-1">Positive score changes</p>
                </div>
            </div>
            @if (empty($improving))
                <p class="text-sm text-zinc-500">No improvements yet.</p>
            @else
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($improving as $w)
                        <li class="py-2 flex items-center gap-3">
                            <a href="{{ route('vitals.url', $w['url']->id) }}" class="flex-1 min-w-0 hover:text-rose-500">
                                <div class="font-medium text-sm">{{ $w['url']->label }}</div>
                                <div class="text-xs text-zinc-500 tabular-nums">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
                            </a>
                            <flux:badge color="emerald" size="sm">+{{ $w['delta'] }}</flux:badge>
                            <flux:button href="{{ route('vitals.url', $w['url']->id) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View URL" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Top third parties --}}
    @if (! empty($topThirdParties))
        <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Top third-party costs</h3>
                    <p class="text-sm text-zinc-500 mt-1">Scripts and resources from external domains</p>
                </div>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                        <th class="py-2 text-xs uppercase tracking-wide text-zinc-500">Entity</th>
                        <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Audits with</th>
                        <th class="py-2 text-right text-xs uppercase tracking-wide text-zinc-500">Total blocking</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($topThirdParties as $tp)
                    <tr class="border-b border-zinc-100 dark:border-zinc-800/50">
                        <td class="py-2 font-medium">{{ $tp['name'] }}</td>
                        <td class="py-2 text-right tabular-nums">{{ $tp['occurrences'] }}</td>
                        <td class="py-2 text-right">
                            <flux:badge color="pink" size="sm">{{ (int) round($tp['total_blocking_ms']) }}ms</flux:badge>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
