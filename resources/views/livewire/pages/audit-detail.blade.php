<div>
    <h1 class="text-2xl font-bold mb-2">Laravel Vitals — {{ $auditModel->url?->label }}</h1>
    <p class="text-sm text-zinc-500 mb-6"><code>{{ $auditModel->url?->path }}</code> — {{ $auditModel->device }} — {{ $auditModel->completed_at?->toDateTimeString() }}</p>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        @foreach (['score_performance' => 'Performance', 'score_accessibility' => 'Accessibility', 'score_best_practices' => 'Best Practices', 'score_seo' => 'SEO'] as $col => $label)
            <flux:card>
                <div class="text-sm text-zinc-500">{{ $label }}</div>
                <div class="text-4xl font-bold">{{ $auditModel->{$col} ?? '—' }}</div>
            </flux:card>
        @endforeach
    </div>

    <flux:card class="mb-6">
        <h2 class="text-lg font-semibold mb-3">Core Web Vitals</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><div class="text-zinc-500">LCP</div><div class="text-xl font-semibold">{{ $auditModel->lcp_ms ? round((float) $auditModel->lcp_ms) . ' ms' : '—' }}</div></div>
            <div><div class="text-zinc-500">CLS</div><div class="text-xl font-semibold">{{ $auditModel->cls ?? '—' }}</div></div>
            <div><div class="text-zinc-500">INP</div><div class="text-xl font-semibold">{{ $auditModel->inp_ms ? round((float) $auditModel->inp_ms) . ' ms' : '—' }}</div></div>
            <div><div class="text-zinc-500">TTFB</div><div class="text-xl font-semibold">{{ $auditModel->ttfb_ms ? round((float) $auditModel->ttfb_ms) . ' ms' : '—' }}</div></div>
        </div>
    </flux:card>

    @if ($auditModel->telemetry)
        <flux:card class="mb-6">
            <h2 class="text-lg font-semibold mb-3">Backend telemetry</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><div class="text-zinc-500">Queries</div><div class="text-xl font-semibold">{{ $auditModel->telemetry->queries_count }}</div></div>
                <div><div class="text-zinc-500">Query time</div><div class="text-xl font-semibold">{{ round((float) $auditModel->telemetry->queries_time_ms) }} ms</div></div>
                <div><div class="text-zinc-500">Memory peak</div><div class="text-xl font-semibold">{{ round($auditModel->telemetry->memory_peak_kb / 1024, 1) }} MB</div></div>
                <div><div class="text-zinc-500">N+1?</div><div class="text-xl font-semibold">{{ $auditModel->telemetry->n_plus_one_suspect ? 'Yes' : 'No' }}</div></div>
            </div>
        </flux:card>
    @endif

    <h2 class="text-lg font-semibold mb-3">Recommendations</h2>
    @forelse ($auditModel->recommendations as $reco)
        <flux:card class="mb-3">
            <div class="flex items-center gap-2 mb-2">
                <flux:badge>{{ $reco->severity }}</flux:badge>
                <flux:badge variant="outline">{{ $reco->category }}</flux:badge>
                <h3 class="font-semibold">{{ __($reco->title_key) }}</h3>
            </div>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">{{ __($reco->description_key) }}</p>

            @if (! empty($reco->code_references))
                <div class="space-y-2">
                    @foreach ($reco->code_references as $ref)
                        <x-vitals::code-reference :ref="$ref" />
                    @endforeach
                </div>
            @endif
        </flux:card>
    @empty
        <p class="text-zinc-500">No recommendations.</p>
    @endforelse
</div>
