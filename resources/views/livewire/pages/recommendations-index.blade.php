<div class="space-y-6">
    @if ($rows->isNotEmpty())
        <div class="flex justify-end">
            <flux:badge color="amber">{{ $rows->sum('occurrences') }} total</flux:badge>
        </div>
    @endif

    @if ($rows->isEmpty())
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-12 text-center">
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
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('vitals::vitals.tables.recommendation') }}</flux:table.column>
                    <flux:table.column>{{ __('vitals::vitals.tables.category') }}</flux:table.column>
                    <flux:table.column>{{ __('vitals::vitals.tables.metric') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('vitals::vitals.tables.occurrences') }}</flux:table.column>
                    <flux:table.column align="end" class="w-14"></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($rows as $r)
                            <flux:table.row :key="$r->audit_key">
                            <flux:table.cell variant="strong">{{ __($r->title_key) }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm">{{ str_replace('_', ' ', $r->category) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ $r->severity->fluxBadgeColor() }}" size="sm">{{ $r->severity->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <span class="font-semibold tabular-nums">{{ $r->occurrences }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button href="{{ route('vitals.learn') . '#' . $r->audit_key }}" variant="ghost" size="sm" icon="book-open" />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endif
</div>
