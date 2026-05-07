<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Performance budgets</h1>
            <p class="text-sm text-ink-500 mt-1">Thresholds that trigger alerts when exceeded</p>
        </div>
        <flux:badge color="zinc">{{ count($budgets) }} metrics</flux:badge>
    </div>

    @if (empty($budgets))
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.chart-bar class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.empty.budgets_no_budgets.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.empty.budgets_no_budgets.body') }}</p>
            <div class="mt-6 rounded-xl bg-ink-50 dark:bg-ink-950 border border-ink-200/60 dark:border-ink-800/60 p-4 text-left max-w-sm mx-auto">
                <pre class="text-xs text-ink-600 dark:text-ink-400 font-mono overflow-x-auto">'budgets' => [
    'lcp_ms' => [
        'warning'  => 2500,
        'critical' => 4000,
    ],
    'score_performance' => [
        'warning'  => 80,
        'critical' => 50,
    ],
],</pre>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <flux:table>
                <flux:columns>
                    <flux:column>Metric</flux:column>
                    <flux:column align="end">Warning</flux:column>
                    <flux:column align="end">Critical</flux:column>
                </flux:columns>
                <flux:rows>
                    @foreach ($budgets as $metric => $thresholds)
                        <flux:row>
                            <flux:cell variant="strong">
                                <code class="text-xs">{{ $metric }}</code>
                            </flux:cell>
                            <flux:cell align="end">
                                <flux:badge color="amber" size="sm">{{ $thresholds['warning'] ?? '—' }}</flux:badge>
                            </flux:cell>
                            <flux:cell align="end">
                                <flux:badge color="rose" size="sm">{{ $thresholds['critical'] ?? '—' }}</flux:badge>
                            </flux:cell>
                        </flux:row>
                    @endforeach
                </flux:rows>
            </flux:table>
        </div>
    @endif

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
