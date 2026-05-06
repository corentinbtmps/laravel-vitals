<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-[-0.02em] text-ink-900 dark:text-ink-100">URLs</h1>
            <p class="mt-1 text-sm text-ink-500">Monitored pages and their latest scores</p>
        </div>
        <span class="text-xs text-ink-400 tabular-nums">{{ $urls->count() }} configured</span>
    </div>

    @if ($urls->isEmpty())
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-8 text-center">
            <flux:icon.link class="size-10 text-ink-300 dark:text-ink-700 mx-auto mb-3" />
            <p class="text-sm text-ink-500 mb-3">No URLs configured yet.</p>
            <p class="text-xs text-ink-400">
                Add entries to <code class="px-1.5 py-0.5 rounded bg-ink-100 dark:bg-ink-800 text-ink-600 dark:text-ink-400">config('vitals.urls')</code>
            </p>
        </div>
    @else
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-ink-200 dark:border-ink-800">
                        <th class="py-3 pl-5 pr-3 text-left label-caps text-ink-400">URL</th>
                        <th class="py-3 px-2 label-caps text-ink-400 text-center">Perf</th>
                        <th class="py-3 px-2 label-caps text-ink-400 text-center">A11y</th>
                        <th class="py-3 px-2 label-caps text-ink-400 text-center">BP</th>
                        <th class="py-3 px-2 label-caps text-ink-400 text-center">SEO</th>
                        <th class="py-3 px-2 label-caps text-ink-400 text-center">Trend</th>
                        <th class="py-3 px-2 label-caps text-ink-400 text-right">Last</th>
                        <th class="py-3 pr-5 label-caps text-ink-400 text-right">Audits</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($urls as $u)
                    @php $last = $lastAudits[$u->id] ?? null; @endphp
                    <tr class="border-b border-ink-100 dark:border-ink-800/50 last:border-0 hover:bg-ink-50 dark:hover:bg-ink-800/30 transition-colors duration-150 group">
                        {{-- URL column --}}
                        <td class="py-3 pl-5 pr-3">
                            <a href="{{ route('vitals.url', $u->id) }}" class="block">
                                <div class="font-medium text-accent-600 dark:text-accent-500 group-hover:underline">{{ $u->label }}</div>
                                <code class="text-[11px] text-ink-400 font-mono">{{ $u->path }}</code>
                            </a>
                        </td>

                        {{-- Score cells --}}
                        @foreach (['score_performance', 'score_accessibility', 'score_best_practices', 'score_seo'] as $col)
                            @php
                                $score = $last?->{$col};
                                $color = \LaravelVitals\Support\Health::colorForScore($score);
                                $scoreColorClass = match($color) {
                                    'emerald' => 'text-emerald-600 dark:text-emerald-400',
                                    'amber'   => 'text-amber-600 dark:text-amber-400',
                                    default   => 'text-accent-600 dark:text-accent-500',
                                };
                            @endphp
                            <td class="py-3 px-2 text-center">
                                @if ($score !== null)
                                    <span class="font-semibold tabular-nums text-sm {{ $scoreColorClass }}">{{ $score }}</span>
                                @else
                                    <span class="text-ink-300 dark:text-ink-700 text-sm">—</span>
                                @endif
                            </td>
                        @endforeach

                        {{-- Trend — delta indicator instead of sparkline --}}
                        <td class="py-3 px-2 text-center">
                            @php
                                $points = $sparklines[$u->id] ?? [];
                                $trendDelta = null;
                                $trendDir = null;
                                if (count($points) >= 2) {
                                    $latest = end($points);
                                    $prior = $points[count($points) - 2];
                                    $trendDelta = $latest - $prior;
                                    $trendDir = $trendDelta > 0 ? 'up' : ($trendDelta < 0 ? 'down' : 'flat');
                                }
                            @endphp
                            @if ($trendDelta !== null && $trendDir !== 'flat')
                                <span class="text-xs font-medium tabular-nums {{ $trendDir === 'up' ? 'text-emerald-600 dark:text-emerald-400' : 'text-accent-500' }}">
                                    {{ $trendDir === 'up' ? '▲' : '▼' }} {{ abs((int) $trendDelta) }}
                                </span>
                            @elseif ($trendDir === 'flat')
                                <span class="text-xs text-ink-400">→</span>
                            @else
                                <span class="text-ink-300 dark:text-ink-700 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Last audit time --}}
                        <td class="py-3 px-2 text-right text-xs text-ink-400">
                            @if ($last !== null && $last->completed_at !== null)
                                <a href="{{ route('vitals.audit', $last->id) }}" class="hover:text-accent-500 transition-colors duration-150" title="{{ $last->completed_at->toDayDateTimeString() }}">
                                    {{ $last->completed_at->diffForHumans(short: true) }}
                                </a>
                            @else
                                —
                            @endif
                        </td>

                        {{-- Audit count --}}
                        <td class="py-3 pr-5 text-right text-ink-500 dark:text-ink-400 tabular-nums text-sm">
                            <a href="{{ route('vitals.url', $u->id) }}" class="hover:text-accent-500 transition-colors duration-150">
                                {{ $u->audits_count }}
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
