<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('vitals::vitals.rum.title') }}</h1>
            <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.rum.subtitle') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Device filter --}}
            <div class="flex gap-1">
                @foreach (['all', 'mobile', 'desktop'] as $d)
                    <button
                        wire:click="setDevice('{{ $d }}')"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $device === $d ? 'bg-accent-500 text-white' : 'bg-paper dark:bg-ink-900 border border-ink-200/60 dark:border-ink-800/60 text-ink-500 hover:text-ink-700 dark:hover:text-ink-300' }}"
                    >{{ ucfirst($d) }}</button>
                @endforeach
            </div>
            {{-- Period filter --}}
            <div class="flex gap-1">
                @foreach (['24h', '7d', '30d', '90d'] as $p)
                    <button
                        wire:click="setPeriod('{{ $p }}')"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $period === $p ? 'bg-accent-500 text-white' : 'bg-paper dark:bg-ink-900 border border-ink-200/60 dark:border-ink-800/60 text-ink-500 hover:text-ink-700 dark:hover:text-ink-300' }}"
                    >{{ $p }}</button>
                @endforeach
            </div>
        </div>
    </div>

    @if ($totalEvents === 0)
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.signal class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.rum.empty.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.rum.empty.body') }}</p>
            <div class="mt-4">
                <code class="inline-block bg-ink-100 dark:bg-ink-800 text-ink-700 dark:text-ink-300 text-xs font-mono rounded-lg px-4 py-2">@vitalsRum</code>
            </div>
        </div>
    @else

    {{-- Metric cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $metricThresholds = [
                'LCP'  => ['good' => 2500, 'poor' => 4000, 'unit' => 'ms', 'divisor' => 1],
                'INP'  => ['good' => 200,  'poor' => 500,  'unit' => 'ms', 'divisor' => 1],
                'CLS'  => ['good' => 0.1,  'poor' => 0.25, 'unit' => '',   'divisor' => 1],
                'TTFB' => ['good' => 800,  'poor' => 1800, 'unit' => 'ms', 'divisor' => 1],
                'FCP'  => ['good' => 1800, 'poor' => 3000, 'unit' => 'ms', 'divisor' => 1],
            ];
        @endphp
        @foreach ($metricCards as $name => $card)
            @php
                $t = $metricThresholds[$name] ?? ['good' => 0, 'poor' => 999999, 'unit' => '', 'divisor' => 1];
                $p75 = $card['p75'];
                $ratingColor = 'text-ink-400';
                if ($p75 !== null) {
                    if ($p75 <= $t['good']) $ratingColor = 'text-emerald-500';
                    elseif ($p75 <= $t['poor']) $ratingColor = 'text-amber-500';
                    else $ratingColor = 'text-rose-500';
                }
                $total = $card['good'] + $card['ni'] + $card['poor'];
                $goodPct = $total > 0 ? round($card['good'] / $total * 100) : 0;
                $niPct   = $total > 0 ? round($card['ni']   / $total * 100) : 0;
                $poorPct = $total > 0 ? 100 - $goodPct - $niPct : 0;
            @endphp
            <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-mono font-semibold text-ink-500 uppercase tracking-wider">{{ $name }}</span>
                    @if ($card['count'] > 0)
                        <span class="text-xs text-ink-400">{{ number_format($card['count']) }}</span>
                    @endif
                </div>
                @if ($p75 !== null)
                    <div class="text-2xl font-bold tabular-nums {{ $ratingColor }}">
                        @if ($name === 'CLS')
                            {{ number_format($p75, 3) }}
                        @else
                            {{ number_format($p75, 0) }}ms
                        @endif
                    </div>
                    <div class="text-xs text-ink-400 mb-2">p75</div>
                    {{-- Distribution bar --}}
                    <div class="flex h-1.5 rounded-full overflow-hidden gap-px">
                        <div class="bg-emerald-400 dark:bg-emerald-500 rounded-l-full" style="width:{{ $goodPct }}%"></div>
                        <div class="bg-amber-400 dark:bg-amber-500" style="width:{{ $niPct }}%"></div>
                        <div class="bg-rose-400 dark:bg-rose-500 rounded-r-full" style="width:{{ $poorPct }}%"></div>
                    </div>
                    <div class="flex justify-between mt-1 text-xs text-ink-400 tabular-nums">
                        <span class="text-emerald-500">{{ $goodPct }}%</span>
                        <span class="text-amber-500">{{ $niPct }}%</span>
                        <span class="text-rose-500">{{ $poorPct }}%</span>
                    </div>
                @else
                    <div class="text-sm text-ink-400 mt-2">{{ __('vitals::vitals.rum.no_data') }}</div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Per-URL breakdown --}}
    @if (count($urlStats) > 0)
    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
        <h3 class="text-base font-semibold mb-4">{{ __('vitals::vitals.rum.url_breakdown') }}</h3>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('vitals::vitals.rum.col_url') }}</flux:table.column>
                <flux:table.column align="end">{{ __('vitals::vitals.rum.col_samples') }}</flux:table.column>
                <flux:table.column align="end">LCP p75</flux:table.column>
                <flux:table.column align="end">INP p75</flux:table.column>
                <flux:table.column align="end">CLS p75</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($urlStats as $row)
                    <flux:table.row>
                        <flux:table.cell>
                            <span class="font-mono text-xs text-ink-600 dark:text-ink-400">{{ $row['url'] }}</span>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <span class="tabular-nums text-sm">{{ number_format($row['count']) }}</span>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @if ($row['lcp_p75'] !== null)
                                @php $color = $row['lcp_p75'] <= 2500 ? 'emerald' : ($row['lcp_p75'] <= 4000 ? 'yellow' : 'rose'); @endphp
                                <flux:badge color="{{ $color }}" size="sm">{{ number_format($row['lcp_p75'], 0) }}ms</flux:badge>
                            @else
                                <span class="text-ink-300">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @if ($row['inp_p75'] !== null)
                                @php $color = $row['inp_p75'] <= 200 ? 'emerald' : ($row['inp_p75'] <= 500 ? 'yellow' : 'rose'); @endphp
                                <flux:badge color="{{ $color }}" size="sm">{{ number_format($row['inp_p75'], 0) }}ms</flux:badge>
                            @else
                                <span class="text-ink-300">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @if ($row['cls_p75'] !== null)
                                @php $color = $row['cls_p75'] <= 0.1 ? 'emerald' : ($row['cls_p75'] <= 0.25 ? 'yellow' : 'rose'); @endphp
                                <flux:badge color="{{ $color }}" size="sm">{{ number_format($row['cls_p75'], 3) }}</flux:badge>
                            @else
                                <span class="text-ink-300">—</span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    @endif

    {{-- INP Attribution --}}
    @if (count($inpAttributions) > 0)
    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
        <h3 class="text-base font-semibold mb-1">{{ __('vitals::vitals.rum.inp_attribution_title') }}</h3>
        <p class="text-sm text-ink-500 mb-4">{{ __('vitals::vitals.rum.inp_attribution_subtitle') }}</p>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('vitals::vitals.rum.col_element') }}</flux:table.column>
                <flux:table.column>{{ __('vitals::vitals.rum.col_event_type') }}</flux:table.column>
                <flux:table.column align="end">{{ __('vitals::vitals.rum.col_samples') }}</flux:table.column>
                <flux:table.column align="end">INP p75</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($inpAttributions as $attr)
                    <flux:table.row>
                        <flux:table.cell>
                            <code class="text-xs bg-ink-100 dark:bg-ink-800 text-ink-700 dark:text-ink-300 rounded px-1.5 py-0.5">{{ $attr['selector'] }}</code>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="blue">{{ $attr['event_type'] }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <span class="tabular-nums text-sm">{{ number_format($attr['count']) }}</span>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @if ($attr['inp_p75'] !== null)
                                @php $color = $attr['inp_p75'] <= 200 ? 'emerald' : ($attr['inp_p75'] <= 500 ? 'yellow' : 'rose'); @endphp
                                <flux:badge color="{{ $color }}" size="sm">{{ number_format($attr['inp_p75'], 0) }}ms</flux:badge>
                            @else
                                <span class="text-ink-300">—</span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    @endif

    @endif
</div>
