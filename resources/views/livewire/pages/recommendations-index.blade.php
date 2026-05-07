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
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-ink-200 dark:border-ink-800">
                        <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide">Recommendation</th>
                        <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide">Category</th>
                        <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide">Severity</th>
                        <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Occurrences</th>
                        <th class="py-3 pl-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $r)
                    @php
                        $sevColor = match ($r->severity) {
                            'critical' => 'rose',
                            'warning'  => 'amber',
                            default    => 'sky',
                        };
                    @endphp
                    <tr class="border-b border-ink-100 dark:border-ink-800/50 hover:bg-ink-50 dark:hover:bg-ink-900/40 transition-colors">
                        <td class="py-3 pr-4 font-medium">{{ __($r->title_key) }}</td>
                        <td class="py-3 pr-4">
                            <flux:badge color="zinc" size="sm">{{ str_replace('_', ' ', $r->category) }}</flux:badge>
                        </td>
                        <td class="py-3 pr-4">
                            <flux:badge color="{{ $sevColor }}" size="sm">{{ $r->severity }}</flux:badge>
                        </td>
                        <td class="py-3 pr-4 text-right font-semibold tabular-nums">
                            {{ $r->occurrences }}
                        </td>
                        <td class="py-3 pl-2 text-right">
                            <flux:button href="{{ route('vitals.learn') . '#' . $r->audit_key }}" variant="ghost" size="sm" icon="book-open" />
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
