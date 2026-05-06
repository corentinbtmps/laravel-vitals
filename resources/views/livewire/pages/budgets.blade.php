<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
            <flux:icon.chart-bar class="size-7 text-rose-500" />
            Performance budgets
        </h1>
        <flux:badge color="zinc">{{ count($budgets) }} metrics</flux:badge>
    </div>

    <flux:card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                    <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide">Metric</th>
                    <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Warning</th>
                    <th class="py-3 pr-4 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Critical</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($budgets as $metric => $thresholds)
                <tr class="border-b border-zinc-100 dark:border-zinc-800/50">
                    <td class="py-3 pr-4">
                        <code class="text-xs text-zinc-700 dark:text-zinc-300">{{ $metric }}</code>
                    </td>
                    <td class="py-3 pr-4 text-right">
                        <flux:badge color="amber" size="sm">{{ $thresholds['warning'] ?? '—' }}</flux:badge>
                    </td>
                    <td class="py-3 pr-4 text-right">
                        <flux:badge color="rose" size="sm">{{ $thresholds['critical'] ?? '—' }}</flux:badge>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </flux:card>

    @if (! empty($perUrl))
        <flux:card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.link class="size-5 text-rose-500" />
                <h2 class="font-semibold">Per-URL overrides</h2>
            </div>
            <pre class="text-xs bg-zinc-100 dark:bg-zinc-900 p-4 rounded overflow-x-auto"><code>{{ json_encode($perUrl, JSON_PRETTY_PRINT) }}</code></pre>
        </flux:card>
    @endif

    <p class="text-xs text-zinc-500 text-center">
        Edit <code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800">config/vitals.php</code> to change budgets.
    </p>
</div>
