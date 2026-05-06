<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-[-0.02em] text-ink-900 dark:text-ink-100">Recommendations</h1>
            <p class="mt-1 text-sm text-ink-500">Aggregated issues across all audits</p>
        </div>
        @if ($rows->isNotEmpty())
            <span class="text-xs text-ink-400 tabular-nums">{{ $rows->sum('occurrences') }} total occurrences</span>
        @endif
    </div>

    @if ($rows->isEmpty())
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-8 text-center">
            <flux:icon.light-bulb class="size-10 text-ink-300 dark:text-ink-700 mx-auto mb-3" />
            <p class="text-sm text-ink-500">No recommendations yet.</p>
            <p class="text-xs text-ink-400 mt-2">Recommendations appear after audits are completed.</p>
        </div>
    @else
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-ink-200 dark:border-ink-800">
                        <th class="py-3 pl-5 pr-4 text-left label-caps text-ink-400">Recommendation</th>
                        <th class="py-3 pr-4 label-caps text-ink-400 text-left">Category</th>
                        <th class="py-3 pr-4 label-caps text-ink-400 text-left">Severity</th>
                        <th class="py-3 pr-4 label-caps text-ink-400 text-right">Occurrences</th>
                        <th class="py-3 pr-5 label-caps text-ink-400 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $r)
                    @php
                        $sevDotClass = match ($r->severity) {
                            'critical' => 'bg-accent-500',
                            'warning'  => 'bg-amber-500',
                            default    => 'bg-emerald-500',
                        };
                        $sevTextClass = match ($r->severity) {
                            'critical' => 'text-accent-600 dark:text-accent-500',
                            'warning'  => 'text-amber-600 dark:text-amber-400',
                            default    => 'text-emerald-600 dark:text-emerald-400',
                        };
                    @endphp
                    <tr class="border-b border-ink-100 dark:border-ink-800/50 last:border-0 hover:bg-ink-50 dark:hover:bg-ink-800/30 transition-colors duration-150">
                        <td class="py-3 pl-5 pr-4 font-medium text-ink-800 dark:text-ink-200">{{ __($r->title_key) }}</td>
                        <td class="py-3 pr-4 text-xs label-caps text-ink-400">{{ str_replace('_', ' ', $r->category) }}</td>
                        <td class="py-3 pr-4">
                            <span class="flex items-center gap-1.5 text-xs font-medium {{ $sevTextClass }}">
                                <span class="size-1.5 rounded-full {{ $sevDotClass }}"></span>
                                {{ $r->severity }}
                            </span>
                        </td>
                        <td class="py-3 pr-4 text-right font-semibold tabular-nums text-ink-700 dark:text-ink-300">{{ $r->occurrences }}</td>
                        <td class="py-3 pr-5 text-right">
                            <flux:button href="{{ route('vitals.learn') . '#' . $r->audit_key }}" variant="ghost" size="sm" icon="book-open" tooltip="Learn more" />
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
