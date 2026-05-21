<div class="space-y-6">
    @if ($quickWins->isEmpty() && empty($worsening) && empty($improving))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.sparkles class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.empty.insights_no_history.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.empty.insights_no_history.body') }}</p>
        </div>
    @else

    {{-- Quick wins --}}
    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold">{{ __('vitals::vitals.insights.quick_wins') }}</h3>
                <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.insights.quick_wins_subtitle') }}</p>
            </div>
        </div>
        @if ($quickWins->isEmpty())
            <p class="text-sm text-ink-500">{{ __('vitals::vitals.insights.no_quick_wins') }}</p>
        @else
            <div class="space-y-2">
                @foreach ($quickWins as $w)
                    <flux:callout
                        variant="{{ $w->severity->fluxCalloutVariant() }}"
                        icon="{{ $w->severity->fluxCalloutIcon() }}"
                    >
                        <flux:callout.heading>{{ __($w->title_key) }}</flux:callout.heading>
                        <flux:callout.text>
                            {{ $w->occurrences }} {{ __('vitals::vitals.insights.occurrences') }} — {{ $w->audit_count }} {{ __('vitals::vitals.insights.audit_count') }}
                        </flux:callout.text>
                    </flux:callout>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Worsening / Improving --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">{{ __('vitals::vitals.insights.worsening_urls') }}</h3>
                    <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.insights.regressions_detected') }}</p>
                </div>
            </div>
            @if (empty($worsening))
                <p class="text-sm text-ink-500">{{ __('vitals::vitals.insights.no_regressions') }}</p>
            @else
                <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($worsening as $w)
                        <li class="py-2 flex items-center gap-3">
                            <flux:link href="{{ route('vitals.url', $w['url']->id) }}" variant="subtle" class="flex-1 min-w-0 block">
                                <div class="font-medium text-sm">{{ $w['url']->label }}</div>
                                <div class="text-xs text-ink-500 tabular-nums">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
                            </flux:link>
                            <flux:badge color="rose" size="sm">{{ $w['delta'] }}</flux:badge>
                            <flux:button href="{{ route('vitals.url', $w['url']->id) }}" variant="ghost" size="sm" icon="arrow-right" :tooltip="__('vitals::vitals.actions.view_url')" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">{{ __('vitals::vitals.insights.improving_urls') }}</h3>
                    <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.insights.positive_score_changes') }}</p>
                </div>
            </div>
            @if (empty($improving))
                <p class="text-sm text-ink-500">{{ __('vitals::vitals.insights.no_improvements') }}</p>
            @else
                <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($improving as $w)
                        <li class="py-2 flex items-center gap-3">
                            <flux:link href="{{ route('vitals.url', $w['url']->id) }}" variant="subtle" class="flex-1 min-w-0 block">
                                <div class="font-medium text-sm">{{ $w['url']->label }}</div>
                                <div class="text-xs text-ink-500 tabular-nums">{{ $w['prior'] }} → {{ $w['latest'] }}</div>
                            </flux:link>
                            <flux:badge color="emerald" size="sm">+{{ $w['delta'] }}</flux:badge>
                            <flux:button href="{{ route('vitals.url', $w['url']->id) }}" variant="ghost" size="sm" icon="arrow-right" :tooltip="__('vitals::vitals.actions.view_url')" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Top third parties --}}
    @if (! empty($topThirdParties))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">{{ __('vitals::vitals.insights.top_third_parties') }}</h3>
                    <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.insights.third_party_subtitle') }}</p>
                </div>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('vitals::vitals.tables.entity') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('vitals::vitals.rum.col_samples') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('vitals::vitals.tables.blocking') }}</flux:table.column>
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
