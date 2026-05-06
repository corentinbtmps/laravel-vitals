<div>
    <h1 class="text-2xl font-bold mb-6">Laravel Vitals — URLs</h1>

    @if ($urls->isEmpty())
        <flux:card>
            <p class="text-zinc-600 dark:text-zinc-400">No URLs configured. Add entries to <code>config('vitals.urls')</code>.</p>
        </flux:card>
    @else
        <flux:card>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b dark:border-zinc-800">
                        <th class="py-2">Label</th>
                        <th>Path</th>
                        <th>Device</th>
                        <th>Audits</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($urls as $u)
                    <tr class="border-b dark:border-zinc-800/50">
                        <td class="py-2"><a href="#" class="hover:underline font-medium">{{ $u->label }}</a></td>
                        <td><code>{{ $u->path }}</code></td>
                        <td>{{ $u->device }}</td>
                        <td>{{ $u->audits_count }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </flux:card>
    @endif
</div>
