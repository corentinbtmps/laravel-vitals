<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
            <flux:icon.link class="size-7 text-rose-500" />
            URLs
        </h1>
        <flux:badge color="zinc">{{ $urls->count() }} configured</flux:badge>
    </div>

    @if ($urls->isEmpty())
        <flux:card>
            <div class="text-center py-8">
                <flux:icon.link class="size-12 text-zinc-300 dark:text-zinc-700 mx-auto mb-3" />
                <p class="text-sm text-zinc-500 mb-4">No URLs configured yet.</p>
                <p class="text-xs text-zinc-400">
                    Add entries to <code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800">config('vitals.urls')</code>
                </p>
            </div>
        </flux:card>
    @else
        <flux:card>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Label</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Path</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Device</th>
                        <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Audits</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($urls as $u)
                    <tr class="border-b border-zinc-100 dark:border-zinc-800/50 hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors">
                        <td class="py-3 pr-4">
                            <a href="{{ route('vitals.url', $u->id) }}" class="font-medium text-rose-600 hover:underline">{{ $u->label }}</a>
                        </td>
                        <td class="py-3 pr-4">
                            <code class="text-xs text-zinc-600 dark:text-zinc-400">{{ $u->path }}</code>
                        </td>
                        <td class="py-3 pr-4">
                            <flux:badge color="zinc" size="sm">{{ $u->device }}</flux:badge>
                        </td>
                        <td class="py-3 pr-4 text-right text-zinc-700 dark:text-zinc-300">{{ $u->audits_count }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif
</div>
