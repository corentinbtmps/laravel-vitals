<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
            <flux:icon.light-bulb class="size-7 text-amber-500" />
            Recommendations
        </h1>
        @if ($rows->isNotEmpty())
            <flux:badge color="amber">{{ $rows->sum('occurrences') }} total</flux:badge>
        @endif
    </div>

    @if ($rows->isEmpty())
        <flux:card>
            <div class="text-center py-8">
                <flux:icon.light-bulb class="size-12 text-zinc-300 dark:text-zinc-700 mx-auto mb-3" />
                <p class="text-sm text-zinc-500">No recommendations yet.</p>
                <p class="text-xs text-zinc-400 mt-2">Recommendations appear after audits are completed.</p>
            </div>
        </flux:card>
    @else
        <flux:card>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Recommendation</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Category</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Severity</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Occurrences</th>
                        <th class="py-3 pl-2 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Action</th>
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
                    <tr class="border-b border-zinc-100 dark:border-zinc-800/50 hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors">
                        <td class="py-3 pr-4 font-medium">{{ __($r->title_key) }}</td>
                        <td class="py-3 pr-4">
                            <flux:badge color="zinc" size="sm">{{ str_replace('_', ' ', $r->category) }}</flux:badge>
                        </td>
                        <td class="py-3 pr-4">
                            <flux:badge color="{{ $sevColor }}" size="sm">{{ $r->severity }}</flux:badge>
                        </td>
                        <td class="py-3 pr-4 text-right font-semibold">{{ $r->occurrences }}</td>
                        <td class="py-3 pl-2 text-right">
                            <flux:button href="{{ route('vitals.learn') . '#' . $r->audit_key }}" variant="ghost" size="sm" icon="book-open" tooltip="Learn more" />
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif
</div>
