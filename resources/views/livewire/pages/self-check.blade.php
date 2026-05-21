<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('vitals::vitals.self_check.title') }}</h1>
            <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.self_check.subtitle') }}</p>
        </div>
        <flux:badge color="zinc" size="sm">{{ __('vitals::vitals.self_check.checked_at', ['time' => $checkedAt->format('H:i:s')]) }}</flux:badge>
    </div>

    {{-- Table sizes --}}
    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon name="circle-stack" class="size-5 text-violet-500" />
            <h2 class="text-base font-semibold">{{ __('vitals::vitals.self_check.table_sizes') }}</h2>
        </div>
        <div class="divide-y divide-ink-100 dark:divide-ink-800">
            @foreach ($tableSizes as $table => $count)
                @php $warnThreshold = str_contains($table, 'rum') ? 500_000 : 50_000; @endphp
                <div class="flex items-center justify-between py-2.5">
                    <code class="text-xs text-ink-600 dark:text-ink-400">{{ $table }}</code>
                    <span @class([
                        'font-semibold tabular-nums text-sm',
                        'text-ink-400'                       => $count < 0,
                        'text-amber-600 dark:text-amber-400' => $count > $warnThreshold,
                        'text-ink-900 dark:text-ink-100'     => $count >= 0 && $count <= $warnThreshold,
                    ])>
                        {{ $count < 0 ? '—' : number_format($count) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Slowest telemetry requests --}}
    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon name="clock" class="size-5 text-amber-500" />
            <h2 class="text-base font-semibold">{{ __('vitals::vitals.self_check.slowest_requests') }}</h2>
        </div>
        @if ($slowTelemetry->isEmpty())
            <p class="text-sm text-ink-500">{{ __('vitals::vitals.self_check.no_data') }}</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Route</flux:table.column>
                    <flux:table.column align="end">Duration</flux:table.column>
                    <flux:table.column align="end">Queries</flux:table.column>
                    <flux:table.column align="end">Recorded</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($slowTelemetry as $t)
                        <flux:table.row :key="$t->id">
                            <flux:table.cell>
                                <code class="text-xs">{{ $t->route_name ?? '—' }}</code>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <span @class([
                    'tabular-nums font-semibold',
                    'text-accent-600 dark:text-accent-400' => $t->duration_ms > 1000,
                ])>
                                    {{ number_format((float) $t->duration_ms, 0) }}ms
                                </span>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <span class="tabular-nums">{{ $t->queries_count }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <span class="text-xs text-ink-500">{{ $t->created_at?->diffForHumans() }}</span>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>

    {{-- Recent audits --}}
    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
        <div class="flex items-center gap-2 mb-4">
            <flux:icon name="bolt" class="size-5 text-accent-500" />
            <h2 class="text-base font-semibold">{{ __('vitals::vitals.self_check.recent_audits') }}</h2>
        </div>
        @if ($recentRuns->isEmpty())
            <p class="text-sm text-ink-500">{{ __('vitals::vitals.self_check.no_data') }}</p>
        @else
            <ul class="space-y-2">
                @foreach ($recentRuns as $run)
                    <li class="flex items-center gap-3 text-sm">
                        <span @class([
                            'inline-block size-2 rounded-full shrink-0',
                            \LaravelVitals\Support\ScoreColorClasses::dot($run->score_performance),
                        ])></span>
                        <flux:link href="{{ route('vitals.audit', $run->id) }}" variant="subtle" class="flex-1">
                            {{ $run->url?->label ?? $run->id }}
                        </flux:link>
                        <span class="text-ink-500 text-xs">{{ $run->device }} · {{ $run->driver }}</span>
                        <span class="text-ink-400 text-xs">{{ $run->completed_at?->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
