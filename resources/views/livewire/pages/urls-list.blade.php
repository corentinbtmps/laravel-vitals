<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">URLs</h1>
            <p class="text-sm text-zinc-500 mt-1">Monitored pages and their latest scores</p>
        </div>
        <flux:badge color="zinc">{{ $urls->count() }} configured</flux:badge>
    </div>

    @if ($urls->isEmpty())
        <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
            <div class="text-center py-8">
                <flux:icon.link class="size-12 text-zinc-300 dark:text-zinc-700 mx-auto mb-3" />
                <p class="text-sm text-zinc-500 mb-4">No URLs configured yet.</p>
                <p class="text-xs text-zinc-400">
                    Add entries to <code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800">config('vitals.urls')</code>
                </p>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-zinc-200/60 dark:border-zinc-800/60 bg-white dark:bg-zinc-900 p-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-zinc-200 dark:border-zinc-800">
                        <th class="py-3 pr-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">URL</th>
                        <th class="py-3 px-2 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-center">Perf</th>
                        <th class="py-3 px-2 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-center">A11y</th>
                        <th class="py-3 px-2 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-center">BP</th>
                        <th class="py-3 px-2 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-center">SEO</th>
                        <th class="py-3 px-2 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-center">Trend</th>
                        <th class="py-3 px-2 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Last</th>
                        <th class="py-3 px-2 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Audits</th>
                        <th class="py-3 px-2 font-semibold text-zinc-500 text-xs uppercase tracking-wide text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($urls as $u)
                    @php $last = $lastAudits[$u->id] ?? null; @endphp
                    <tr class="border-b border-zinc-100 dark:border-zinc-800/50 hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors">
                        {{-- URL column --}}
                        <td class="py-3 pr-3">
                            <a href="{{ route('vitals.url', $u->id) }}" class="block hover:underline">
                                <div class="font-medium text-rose-600 dark:text-rose-400">{{ $u->label }}</div>
                                <code class="text-[11px] text-zinc-500">{{ $u->path }}</code>
                            </a>
                        </td>

                        {{-- Score cells --}}
                        @foreach (['score_performance', 'score_accessibility', 'score_best_practices', 'score_seo'] as $col)
                            @php
                                $score = $last?->{$col};
                                $color = \LaravelVitals\Support\Health::colorForScore($score);
                            @endphp
                            <td class="py-3 px-2 text-center">
                                @if ($score !== null)
                                    <span class="inline-flex items-center justify-center size-9 rounded-xl bg-{{ $color }}-50 dark:bg-{{ $color }}-900/30 text-{{ $color }}-700 dark:text-{{ $color }}-300 font-semibold text-sm tabular-nums">
                                        {{ $score }}
                                    </span>
                                @else
                                    <span class="text-zinc-300 dark:text-zinc-700 text-sm">—</span>
                                @endif
                            </td>
                        @endforeach

                        {{-- Sparkline --}}
                        <td class="py-3 px-2">
                            @php
                                $points = $sparklines[$u->id] ?? [];
                                $sparkId = 'spark-' . $u->id;
                            @endphp
                            @if (count($points) >= 2)
                                <div id="{{ $sparkId }}" class="w-24 mx-auto"></div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        new ApexCharts(document.querySelector('#{{ $sparkId }}'), {
                                            chart: { type: 'line', height: 28, sparkline: { enabled: true }, animations: { enabled: false } },
                                            series: [{ data: @json($points) }],
                                            stroke: { curve: 'smooth', width: 2 },
                                            colors: ['#f43f5e'],
                                            tooltip: { enabled: false },
                                        }).render();
                                    });
                                </script>
                            @else
                                <span class="text-zinc-300 dark:text-zinc-700 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Last audit time --}}
                        <td class="py-3 px-2 text-right text-xs text-zinc-500">
                            @if ($last !== null && $last->completed_at !== null)
                                <a href="{{ route('vitals.audit', $last->id) }}" class="hover:text-rose-500 hover:underline" title="{{ $last->completed_at->toDayDateTimeString() }}">
                                    {{ $last->completed_at->diffForHumans(short: true) }}
                                </a>
                            @else
                                —
                            @endif
                        </td>

                        {{-- Audit count --}}
                        <td class="py-3 px-2 text-right text-zinc-700 dark:text-zinc-300 tabular-nums">{{ $u->audits_count }}</td>

                        {{-- Action --}}
                        <td class="py-3 pl-2 text-right">
                            <flux:button href="{{ route('vitals.url', $u->id) }}" variant="ghost" size="sm" icon="arrow-right">View</flux:button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
