<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Performance budgets</h1>
            <p class="text-sm text-ink-500 mt-1">Thresholds that trigger alerts when exceeded</p>
        </div>
        <flux:badge color="zinc">{{ count($budgets) }} metrics</flux:badge>
    </div>

    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b border-ink-200 dark:border-ink-800">
                    <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide">Metric</th>
                    <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Warning</th>
                    <th class="py-3 pr-4 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Critical</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($budgets as $metric => $thresholds)
                <tr class="border-b border-ink-100 dark:border-ink-800/50">
                    <td class="py-3 pr-4">
                        <code class="text-xs text-ink-700 dark:text-ink-300">{{ $metric }}</code>
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
    </div>

    @if (! empty($perUrl))
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold">Per-URL overrides</h3>
                </div>
            </div>
            <pre class="text-xs bg-ink-50 dark:bg-ink-950 p-4 rounded-2xl overflow-x-auto border border-ink-200/60 dark:border-ink-800/60"><code>{{ json_encode($perUrl, JSON_PRETTY_PRINT) }}</code></pre>
        </div>
    @endif

    <p class="text-xs text-ink-500 text-center">
        Edit <code class="px-1.5 py-0.5 rounded bg-ink-100 dark:bg-ink-800">config/vitals.php</code> to change budgets.
    </p>
</div>
