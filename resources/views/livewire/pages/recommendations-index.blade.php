<div class="space-y-6">
    @if ($rows->isNotEmpty())
        <div class="flex justify-end">
            <flux:badge color="amber">{{ $rows->sum('occurrences') }} total</flux:badge>
        </div>
    @endif

    @if ($rows->isEmpty())
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.light-bulb class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.empty.recos_no_recos.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.empty.recos_no_recos.body') }}</p>
            <div class="mt-5 flex items-center justify-center gap-2">
                <flux:button href="{{ route('vitals.learn') }}" variant="filled" color="accent" icon="book-open" size="sm">{{ __('vitals::vitals.empty.recos_no_recos.cta') }}</flux:button>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($rows as $r)
                @php $sev = $r->severity; @endphp
                <flux:accent :color="$sev->fluxAccentColor()">
                    <div @class([
                        'rounded-lg border p-4',
                        ...$sev->containerClasses(),
                    ])>
                        <div class="flex items-start gap-3">
                            <flux:icon name="{{ $sev->fluxCalloutIcon() }}" @class([
                                'size-5 shrink-0 mt-0.5',
                                $sev->iconTextColor(),
                            ]) />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <flux:link href="{{ route('vitals.issue.detail', ['auditKey' => $r->audit_key]) }}" variant="ghost" class="font-semibold">
                                        {{ __($r->title_key) }}
                                    </flux:link>
                                    <flux:badge color="{{ $sev->fluxBadgeColor() }}" size="sm">{{ $sev->label() }}</flux:badge>
                                    <flux:badge color="zinc" size="sm">{{ str_replace('_', ' ', $r->category) }}</flux:badge>
                                    <flux:button
                                        href="{{ route('vitals.issue.detail', ['auditKey' => $r->audit_key]) }}"
                                        variant="ghost"
                                        size="xs"
                                        icon="map-pin"
                                        class="ml-auto"
                                    >{{ $r->occurrences }} {{ Str::plural(__('vitals::vitals.issue_detail.occurrences_label'), (int) $r->occurrences) }}</flux:button>
                                </div>
                            </div>
                        </div>
                    </div>
                </flux:accent>
            @endforeach
        </div>
    @endif
</div>
