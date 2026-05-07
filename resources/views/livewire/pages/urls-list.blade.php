<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">URLs</h1>
            <p class="text-sm text-ink-500 mt-1">Monitored pages and their latest scores</p>
        </div>
        <flux:badge color="zinc">{{ $urls->count() }} configured</flux:badge>
    </div>

    @if ($urls->isEmpty())
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.link class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.empty.urls_no_urls.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.empty.urls_no_urls.body') }}</p>
            <div class="mt-6 rounded-xl bg-ink-50 dark:bg-ink-950 border border-ink-200/60 dark:border-ink-800/60 p-4 text-left max-w-sm mx-auto">
                <pre class="text-xs text-ink-600 dark:text-ink-400 font-mono overflow-x-auto">'urls' => [
    ['label' => 'Home', 'path' => '/'],
    ['label' => 'About', 'path' => '/about'],
],</pre>
            </div>
            <div class="mt-4 flex items-center justify-center gap-2">
                <code class="inline-block rounded-md bg-ink-100 dark:bg-ink-800 px-3 py-1.5 text-xs text-ink-600 dark:text-ink-400 font-mono">php artisan vitals:demo</code>
            </div>
        </div>
    @else

        {{-- Pinned / Favorites section --}}
        @if ($pinnedUrls->isNotEmpty())
            <div>
                <div class="flex items-baseline gap-3 mb-3">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.08em] text-amber-600 dark:text-amber-500">Favorites</h2>
                    <span class="text-xs text-ink-500">{{ $pinnedUrls->count() }}</span>
                </div>
                <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
                    <div class="overflow-x-auto -mx-6 px-6 md:mx-0 md:px-0">
                    <table class="w-full text-sm min-w-[600px]">
                        <thead>
                            <tr class="text-left border-b border-ink-200 dark:border-ink-800">
                                <th class="py-3 pr-2 w-6"></th>
                                <th class="py-3 pr-3 font-semibold text-ink-500 text-xs uppercase tracking-wide">URL</th>
                                <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">Perf</th>
                                <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">A11y</th>
                                <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">BP</th>
                                <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">SEO</th>
                                <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">Trend</th>
                                <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Last</th>
                                <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Audits</th>
                                <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($pinnedUrls as $u)
                            @php $last = $lastAudits[$u->id] ?? null; @endphp
                            <tr class="border-b border-ink-100 dark:border-ink-800/50 hover:bg-ink-50 dark:hover:bg-ink-900/40 transition-colors">
                                {{-- Star / pin button --}}
                                <td class="py-3 pr-2">
                                    <flux:tooltip :content="$u->pinned_at ? __('vitals.tooltip.unpin') : __('vitals.tooltip.pin')">
                                        <button wire:click="togglePin({{ $u->id }})"
                                                type="button"
                                                class="text-amber-500 hover:text-amber-600 transition-colors duration-150">
                                            <flux:icon.star variant="solid" class="size-4" />
                                        </button>
                                    </flux:tooltip>
                                </td>

                                {{-- URL column --}}
                                <td class="py-3 pr-3">
                                    <a href="{{ route('vitals.url', $u->id) }}" class="block hover:underline">
                                        <div class="font-medium text-accent-600 dark:text-accent-400">{{ $u->label }}</div>
                                        <code class="text-[11px] text-ink-500">{{ $u->path }}</code>
                                    </a>
                                </td>

                                {{-- Score cells --}}
                                @foreach (['score_performance' => 'Performance', 'score_accessibility' => 'Accessibility', 'score_best_practices' => 'Best Practices', 'score_seo' => 'SEO'] as $col => $colLabel)
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
                                            <span class="text-ink-300 dark:text-ink-700 text-sm">—</span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Sparkline --}}
                                <td class="py-3 px-2">
                                    @php
                                        $points = $sparklines[$u->id] ?? [];
                                        $sparkId = 'spark-pin-' . $u->id;
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
                                        <span class="text-ink-300 dark:text-ink-700 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Last audit time --}}
                                <td class="py-3 px-2 text-right text-xs text-ink-500">
                                    @if ($last !== null && $last->completed_at !== null)
                                        <flux:tooltip :content="__('vitals.tooltip.last_audit_at', ['timestamp' => $last->completed_at->toDayDateTimeString()])">
                                            <a href="{{ route('vitals.audit', $last->id) }}" class="hover:text-accent-500 hover:underline">
                                                {{ $last->completed_at->diffForHumans(short: true) }}
                                            </a>
                                        </flux:tooltip>
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Audit count --}}
                                <td class="py-3 px-2 text-right text-ink-700 dark:text-ink-300 tabular-nums">{{ $u->audits_count }}</td>

                                {{-- Action --}}
                                <td class="py-3 pl-2 text-right">
                                    <flux:button href="{{ route('vitals.url', $u->id) }}" variant="ghost" size="sm" icon="arrow-right">View</flux:button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- All URLs section --}}
        <div class="{{ $pinnedUrls->isNotEmpty() ? 'mt-8' : '' }}">
            @if ($pinnedUrls->isNotEmpty())
                <div class="flex items-baseline gap-3 mb-3">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">All URLs</h2>
                    <span class="text-xs text-ink-500">{{ $allUrls->count() }}</span>
                </div>
            @endif
            <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
                <div class="overflow-x-auto -mx-6 px-6 md:mx-0 md:px-0">
                <table class="w-full text-sm min-w-[600px]">
                    <thead>
                        <tr class="text-left border-b border-ink-200 dark:border-ink-800">
                            <th class="py-3 pr-2 w-6"></th>
                            <th class="py-3 pr-3 font-semibold text-ink-500 text-xs uppercase tracking-wide">URL</th>
                            <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">Perf</th>
                            <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">A11y</th>
                            <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">BP</th>
                            <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">SEO</th>
                            <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-center">Trend</th>
                            <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Last</th>
                            <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Audits</th>
                            <th class="py-3 px-2 font-semibold text-ink-500 text-xs uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($allUrls as $u)
                        @php $last = $lastAudits[$u->id] ?? null; @endphp
                        <tr class="border-b border-ink-100 dark:border-ink-800/50 hover:bg-ink-50 dark:hover:bg-ink-900/40 transition-colors">
                            {{-- Star / pin button --}}
                            <td class="py-3 pr-2">
                                <flux:tooltip :content="$u->pinned_at ? __('vitals.tooltip.unpin') : __('vitals.tooltip.pin')">
                                    <button wire:click="togglePin({{ $u->id }})"
                                            type="button"
                                            class="transition-colors duration-150 {{ $u->pinned_at ? 'text-amber-500 hover:text-amber-600' : 'text-ink-300 hover:text-amber-500' }}">
                                        @if ($u->pinned_at)
                                            <flux:icon.star variant="solid" class="size-4" />
                                        @else
                                            <flux:icon.star class="size-4" />
                                        @endif
                                    </button>
                                </flux:tooltip>
                            </td>

                            {{-- URL column --}}
                            <td class="py-3 pr-3">
                                <a href="{{ route('vitals.url', $u->id) }}" class="block hover:underline">
                                    <div class="font-medium text-accent-600 dark:text-accent-400">{{ $u->label }}</div>
                                    <code class="text-[11px] text-ink-500">{{ $u->path }}</code>
                                </a>
                            </td>

                            {{-- Score cells --}}
                            @foreach (['score_performance' => 'Performance', 'score_accessibility' => 'Accessibility', 'score_best_practices' => 'Best Practices', 'score_seo' => 'SEO'] as $col => $colLabel)
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
                                        <span class="text-ink-300 dark:text-ink-700 text-sm">—</span>
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
                                    <span class="text-ink-300 dark:text-ink-700 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Last audit time --}}
                            <td class="py-3 px-2 text-right text-xs text-ink-500">
                                @if ($last !== null && $last->completed_at !== null)
                                    <flux:tooltip :content="__('vitals.tooltip.last_audit_at', ['timestamp' => $last->completed_at->toDayDateTimeString()])">
                                        <a href="{{ route('vitals.audit', $last->id) }}" class="hover:text-accent-500 hover:underline">
                                            {{ $last->completed_at->diffForHumans(short: true) }}
                                        </a>
                                    </flux:tooltip>
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Audit count --}}
                            <td class="py-3 px-2 text-right text-ink-700 dark:text-ink-300 tabular-nums">{{ $u->audits_count }}</td>

                            {{-- Action --}}
                            <td class="py-3 pl-2 text-right">
                                <flux:button href="{{ route('vitals.url', $u->id) }}" variant="ghost" size="sm" icon="arrow-right">View</flux:button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    @endif
</div>
