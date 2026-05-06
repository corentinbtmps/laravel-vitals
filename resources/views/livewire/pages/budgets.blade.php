<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-[-0.02em] text-ink-900 dark:text-ink-100">Performance budgets</h1>
            <p class="mt-1 text-sm text-ink-500">Thresholds that trigger alerts when exceeded</p>
        </div>
        <span class="text-xs text-ink-400 tabular-nums">{{ count($budgets) }} metrics</span>
    </div>

    <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-ink-200 dark:border-ink-800">
                    <th class="py-3 pl-5 pr-4 text-left label-caps text-ink-400">Metric</th>
                    <th class="py-3 pr-4 label-caps text-ink-400 text-right">Warning</th>
                    <th class="py-3 pr-5 label-caps text-ink-400 text-right">Critical</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($budgets as $metric => $thresholds)
                <tr class="border-b border-ink-100 dark:border-ink-800/50 last:border-0">
                    <td class="py-3 pl-5 pr-4">
                        <code class="text-xs font-mono text-ink-600 dark:text-ink-400">{{ $metric }}</code>
                    </td>
                    <td class="py-3 pr-4 text-right">
                        <span class="text-sm font-medium tabular-nums text-amber-600 dark:text-amber-400">{{ $thresholds['warning'] ?? '—' }}</span>
                    </td>
                    <td class="py-3 pr-5 text-right">
                        <span class="text-sm font-medium tabular-nums text-accent-600 dark:text-accent-500">{{ $thresholds['critical'] ?? '—' }}</span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if (! empty($perUrl))
        <div>
            <p class="label-caps text-ink-400 mb-3">Per-URL overrides</p>
            <pre class="text-xs bg-canvas dark:bg-ink-900 border border-ink-200 dark:border-ink-800 p-4 rounded-xl overflow-x-auto"><code class="font-mono text-ink-700 dark:text-ink-300">{{ json_encode($perUrl, JSON_PRETTY_PRINT) }}</code></pre>
        </div>
    @endif

    <p class="text-xs text-ink-400">
        Edit <code class="px-1.5 py-0.5 rounded bg-ink-100 dark:bg-ink-800 font-mono text-ink-600 dark:text-ink-400">config/vitals.php</code> to change budgets.
    </p>
</div>
