<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Insights</h1>
        <p class="text-sm text-ink-500 mt-1">Cross-URL trends and opportunities aggregated from the last 7 days</p>
    </div>

    @if ($quickWins->isEmpty() && empty($worsening) && empty($improving))
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.sparkles class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.empty.insights_no_history.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.empty.insights_no_history.body') }}</p>
        </div>
    @else

    {{-- Quick wins --}}
    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold">Quick wins</h3>
                <p class="text-sm text-ink-500 mt-1">Most impactful issues to fix first</p>
            </div>
        </div>
        @if ($quickWins->isEmpty())
            <p class="text-sm text-ink-500">No prioritized issues found in the last 7 days.</p>
        @else
            <div class="space-y-2">
                @foreach ($quickWins as $w)
                    @php
                        $variant = match ($w->severity) {
                            'critical' => 'danger',
                            'warning'  => 'warning',
                            default    => 'secondary',
                        };
                        $icon = match ($w->severity) {
                            'critical' => 'exclamation-circle',
                            'warning'  => 'exclamation-triangle',
                            default    => 'information-circle',
                        };
                    @endphp
                    <flux:callout variant="{{ $variant }}" icon="{{ $icon }}">
                        <flux:callout.heading>{{ __($w->title_key) }}</flux:callout.heading>
                        <flux:callout.text>
                            {{ $w->occurrences }} {{ Str::plural('occurrence', $w->occurrences) }} across {{ $w->audit_count }} {{ Str::plural('audit', $w->audit_count) }}
                        </flux:callout.text>
                    </flux:callout>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Worsening / Improving --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Worsening URLs</h3>
                    <p class="text-sm text-ink-500 mt-1">Regressions detected</p>
                </div>
            </div>
            @if (empty($worsening))
                <p class="text-sm text-ink-500">No regressions detected.</p>
            @else
                <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($worsening as $w)
                        <li class="py-2 flex items-center gap-3">
                            <a href="{{ route('vitals.url', $w['url']->id) }}" class="flex-1 min-w-0 hover:text-accent-500">
                                <div class="font-medium text-sm">{{ $w['url']->label }}</div>
                                <div class="text-xs text-ink-500 tabular-nums">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
                            </a>
                            <flux:badge color="rose" size="sm">{{ $w['delta'] }}</flux:badge>
                            <flux:button href="{{ route('vitals.url', $w['url']->id) }}" variant="ghost" size="sm" icon="arrow-right" tooltip="View URL" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Improving URLs</h3>
                    <p class="text-sm text-ink-500 mt-1">Positive score changes</p>
                </div>
            </div>
            @if (empty($improving))
                <p class="text-sm text-ink-500">No improvements yet.</p>
            @else
                <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($improving as $w)
                        <li class="py-2 flex items-center gap-3">
                            <a href="{{ route('vitals.url', $w['url']->id) }}" class="flex-1 min-w-0 hover:text-accent-500">
                                <div class="font-medium text-sm">{{ $w['url']->label }}</div>
                                <div class="text-xs text-ink-500 tabular-nums">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
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
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Top third-party costs</h3>
                    <p class="text-sm text-ink-500 mt-1">Scripts and resources from external domains</p>
                </div>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Entity</flux:table.column>
                    <flux:table.column align="end">Audits with</flux:table.column>
                    <flux:table.column align="end">Total blocking</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($topThirdParties as $tp)
                        <flux:table.row>
                            <flux:table.cell variant="strong">{{ $tp['name'] }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <span class="tabular-nums">{{ $tp['occurrences'] }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:badge color="pink" size="sm">{{ (int) round($tp['total_blocking_ms']) }}ms</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endif
    @endif
</div>
