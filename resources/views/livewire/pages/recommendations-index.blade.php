<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Recommendations</h1>
            <p class="text-sm text-ink-500 mt-1">Aggregated issues across all audits</p>
        </div>
        @if ($rows->isNotEmpty())
            <flux:badge color="amber">{{ $rows->sum('occurrences') }} total</flux:badge>
        @endif
    </div>

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
                <flux:columns>
                    <flux:column>Recommendation</flux:column>
                    <flux:column>Category</flux:column>
                    <flux:column>Severity</flux:column>
                    <flux:column align="end">Occurrences</flux:column>
                    <flux:column align="end" class="w-14"></flux:column>
                </flux:columns>
                <flux:rows>
                    @foreach ($rows as $r)
                        @php
                            $sevColor = match ($r->severity) {
                                'critical' => 'rose',
                                'warning'  => 'amber',
                                default    => 'sky',
                            };
                        @endphp
                        <flux:row :key="$r->audit_key">
                            <flux:cell variant="strong">{{ __($r->title_key) }}</flux:cell>
                            <flux:cell>
                                <flux:badge color="zinc" size="sm">{{ str_replace('_', ' ', $r->category) }}</flux:badge>
                            </flux:cell>
                            <flux:cell>
                                <flux:badge color="{{ $sevColor }}" size="sm">{{ $r->severity }}</flux:badge>
                            </flux:cell>
                            <flux:cell align="end">
                                <span class="font-semibold tabular-nums">{{ $r->occurrences }}</span>
                            </flux:cell>
                            <flux:cell align="end">
                                <flux:button href="{{ route('vitals.learn') . '#' . $r->audit_key }}" variant="ghost" size="sm" icon="book-open" />
                            </flux:cell>
                        </flux:row>
                    @endforeach
                </flux:rows>
            </flux:table>
        </div>
    @endif
</div>
