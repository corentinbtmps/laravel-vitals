<div>
    <h1 class="text-2xl font-bold mb-6">Laravel Vitals — Recommendations</h1>

    @if ($rows->isEmpty())
        <flux:card>
            <p class="text-zinc-600 dark:text-zinc-400">No recommendations yet.</p>
        </flux:card>
    @else
        <flux:card>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b dark:border-zinc-800">
                        <th class="py-2">Recommendation</th>
                        <th>Category</th>
                        <th>Severity</th>
                        <th>Occurrences</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $r)
                    <tr class="border-b dark:border-zinc-800/50">
                        <td class="py-2">{{ __($r->title_key) }}</td>
                        <td>{{ $r->category }}</td>
                        <td><flux:badge>{{ $r->severity }}</flux:badge></td>
                        <td class="font-semibold">{{ $r->occurrences }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif
</div>
