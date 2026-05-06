<div>
    <h1 class="text-2xl font-bold mb-2">Laravel Vitals — {{ $urlModel->label }}</h1>
    <p class="text-sm text-zinc-500 mb-6"><code>{{ $urlModel->path }}</code></p>

    @if ($history->isEmpty())
        <flux:card><p class="text-zinc-600 dark:text-zinc-400">No completed audits yet.</p></flux:card>
    @else
        <flux:card>
            <h2 class="text-lg font-semibold mb-3">Recent audits</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b dark:border-zinc-800">
                        <th class="py-2">Date</th>
                        <th>Device</th>
                        <th>Performance</th>
                        <th>LCP</th>
                        <th>CLS</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($history as $a)
                    <tr class="border-b dark:border-zinc-800/50">
                        <td class="py-2">{{ $a->completed_at?->toDateTimeString() }}</td>
                        <td>{{ $a->device }}</td>
                        <td>{{ $a->score_performance }}</td>
                        <td>{{ $a->lcp_ms ? round((float) $a->lcp_ms) . ' ms' : '—' }}</td>
                        <td>{{ $a->cls ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif
</div>
