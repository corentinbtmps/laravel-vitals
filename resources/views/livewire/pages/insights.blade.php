<div class="space-y-6">
    <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
        <flux:icon.sparkles class="size-7 text-rose-500" />
        Insights
    </h1>

    <p class="text-sm text-zinc-500">Cross-URL trends and opportunities aggregated from the last 7 days.</p>

    {{-- Quick wins --}}
    <flux:card>
        <div class="flex items-center gap-2 mb-4">
            <flux:icon.bolt class="size-5 text-amber-500" />
            <h2 class="font-semibold">Quick wins</h2>
            <span class="text-xs text-zinc-500">— most impactful issues to fix first</span>
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
                    <li class="flex items-start gap-3 p-3 rounded-lg border border-{{ $sevColor }}-200 dark:border-{{ $sevColor }}-900/40 bg-{{ $sevColor }}-50/30 dark:bg-{{ $sevColor }}-900/5">
                        <flux:badge color="{{ $sevColor }}" size="sm">{{ $w->severity }}</flux:badge>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm">{{ __($w->title_key) }}</div>
                            <div class="text-xs text-zinc-500 mt-0.5">{{ $w->occurrences }} occurrence(s) across {{ $w->audit_count }} audit(s)</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </flux:card>

    {{-- Worsening / Improving --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.arrow-trending-down class="size-5 text-rose-500" />
                <h2 class="font-semibold">Worsening URLs</h2>
            </div>
            @if (empty($worsening))
                <p class="text-sm text-zinc-500">No regressions detected.</p>
            @else
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($worsening as $w)
                        <li class="py-2 flex items-center gap-3">
                            <a href="{{ route('vitals.url', $w['url']->id) }}" class="flex-1 min-w-0 hover:text-rose-500">
                                <div class="font-medium text-sm">{{ $w['url']->label }}</div>
                                <div class="text-xs text-zinc-500">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
                            </a>
                            <flux:badge color="rose" size="sm">{{ $w['delta'] }}</flux:badge>
                            <flux:button href="{{ route('vitals.url', $w['url']->id) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View URL" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.arrow-trending-up class="size-5 text-emerald-500" />
                <h2 class="font-semibold">Improving URLs</h2>
            </div>
            @if (empty($improving))
                <p class="text-sm text-zinc-500">No improvements yet.</p>
            @else
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($improving as $w)
                        <li class="py-2 flex items-center gap-3">
                            <a href="{{ route('vitals.url', $w['url']->id) }}" class="flex-1 min-w-0 hover:text-rose-500">
                                <div class="font-medium text-sm">{{ $w['url']->label }}</div>
                                <div class="text-xs text-zinc-500">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
                            </a>
                            <flux:badge color="emerald" size="sm">+{{ $w['delta'] }}</flux:badge>
                            <flux:button href="{{ route('vitals.url', $w['url']->id) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View URL" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </flux:card>
    </div>

    {{-- Top third parties --}}
    @if (! empty($topThirdParties))
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.globe-alt class="size-5 text-pink-500" />
                <h2 class="font-semibold">Top third-party costs</h2>
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
                        <td class="py-2 text-right">{{ $tp['occurrences'] }}</td>
                        <td class="py-2 text-right">
                            <flux:badge color="pink" size="sm">{{ (int) round($tp['total_blocking_ms']) }}ms</flux:badge>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif
</div>
