<div>
    <h1 class="text-2xl font-bold mb-6">Laravel Vitals — Performance budgets</h1>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b dark:border-zinc-800">
                    <th class="py-2">Metric</th>
                    <th>Warning</th>
                    <th>Critical</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($budgets as $metric => $thresholds)
                <tr class="border-b dark:border-zinc-800/50">
                    <td class="py-2 font-mono">{{ $metric }}</td>
                    <td>{{ $thresholds['warning'] ?? '—' }}</td>
                    <td>{{ $thresholds['critical'] ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-xs text-zinc-500 mt-4">Edit <code>config/vitals.php</code> to change budgets.</p>
</div>
