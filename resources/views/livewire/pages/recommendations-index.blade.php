<div>
    <h1 class="text-2xl font-bold mb-6">Laravel Vitals — Recommendations</h1>

    @if ($rows->isEmpty())
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-zinc-600 dark:text-zinc-400">No recommendations yet.</p>
        </div>
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
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
                        <td><span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">{{ $r->severity }}</span></td>
                        <td class="font-semibold">{{ $r->occurrences }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
