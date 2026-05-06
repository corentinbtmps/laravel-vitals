<div>
    <h1 class="text-2xl font-bold mb-6">Laravel Vitals &mdash; Overview</h1>

    @if ($recent->isEmpty())
        <flux:card>
            <p class="text-zinc-600 dark:text-zinc-400">No audits in the last 7 days. Run <code>php artisan vitals:audit</code>.</p>
        </flux:card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            @foreach (['performance' => 'Performance', 'accessibility' => 'Accessibility', 'best_practices' => 'Best Practices', 'seo' => 'SEO'] as $key => $label)
                <flux:card>
                    <div class="text-sm text-zinc-500">{{ $label }}</div>
                    <div class="text-4xl font-bold">{{ $averages[$key] }}</div>
                </flux:card>
            @endforeach
        </div>

        <flux:card>
            <h2 class="text-lg font-semibold mb-4">Recent audits</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b dark:border-zinc-800">
                        <th class="py-2">URL</th>
                        <th>Device</th>
                        <th>Perf</th>
                        <th>A11y</th>
                        <th>BP</th>
                        <th>SEO</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($recent as $a)
                    <tr class="border-b dark:border-zinc-800/50">
                        <td class="py-2"><a href="{{ route('vitals.audit', $a) }}" class="hover:underline">{{ $a->url?->label }}</a></td>
                        <td>{{ $a->device }}</td>
                        <td>{{ $a->score_performance }}</td>
                        <td>{{ $a->score_accessibility }}</td>
                        <td>{{ $a->score_best_practices }}</td>
                        <td>{{ $a->score_seo }}</td>
                        <td class="text-zinc-500">{{ $a->completed_at?->diffForHumans() }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif
</div>
