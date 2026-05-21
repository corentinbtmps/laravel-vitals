<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('vitals::vitals.pages.urls.title') }}</h1>
            <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.pages.urls.subtitle') }}</p>
        </div>
        <flux:badge color="zinc">{{ $urls->count() }} {{ __('vitals::vitals.overview_page.configured') }}</flux:badge>
    </div>

    @if ($urls->isEmpty())
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-accent-100 dark:bg-accent-900/30 mb-4">
                <flux:icon.link class="size-6 text-accent-600 dark:text-accent-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.empty.urls_no_urls.title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.empty.urls_no_urls.body') }}</p>
            <div class="mt-6 rounded-xl bg-ink-50 dark:bg-ink-950 border border-ink-200 dark:border-ink-800 p-4 text-left max-w-sm mx-auto">
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
                    <h2 class="text-xs font-semibold uppercase tracking-[0.08em] text-amber-600 dark:text-amber-500">{{ __('vitals::vitals.urls_page.favorites') }}</h2>
                    <span class="text-xs text-ink-500">{{ $pinnedUrls->count() }}</span>
                </div>
                <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="w-8"></flux:table.column>
                            <flux:table.column align="center">{{ __('vitals::vitals.tables.global') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('vitals::vitals.tables.perf_grade') }}</flux:table.column>
                            <flux:table.column>URL</flux:table.column>
                            <flux:table.column align="center">Perf</flux:table.column>
                            <flux:table.column align="center">A11y</flux:table.column>
                            <flux:table.column align="center">BP</flux:table.column>
                            <flux:table.column align="center">SEO</flux:table.column>
                            <flux:table.column align="center">{{ __('vitals::vitals.tables.trend') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('vitals::vitals.tables.last') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('vitals::vitals.tables.audits') }}</flux:table.column>
                            <flux:table.column align="end" class="w-20"></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($pinnedUrls as $u)
                                @php $last = $lastAudits[$u->id] ?? null; @endphp
                                <flux:table.row :key="$u->id">
                                    {{-- Star / pin button --}}
                                    <flux:table.cell>
                                        <flux:button
                                            wire:click="togglePin({{ $u->id }})"
                                            variant="ghost"
                                            size="xs"
                                            icon="star"
                                            icon:variant="solid"
                                            class="text-amber-500 hover:text-amber-600"
                                            :tooltip="$u->pinned_at ? __('vitals::vitals.tooltip.unpin') : __('vitals::vitals.tooltip.pin')"
                                        />
                                    </flux:table.cell>

                                    {{-- Global grade badge --}}
                                    @php
                                        $globalGrade = $last?->global_grade;
                                        $globalScore = $last !== null
                                            ? (int) round(collect([$last->score_performance, $last->score_accessibility, $last->score_best_practices, $last->score_seo])->filter(fn ($v) => $v !== null)->avg() ?? 0)
                                            : null;
                                        $globalColor = \LaravelVitals\Support\Health::colorForScore($globalScore);
                                    @endphp
                                    <flux:table.cell align="center">
                                        @if ($globalGrade !== null)
                                            <span @class([
                                                'inline-flex items-center justify-center size-9 rounded-xl font-bold text-base',
                                                'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' => $globalColor === 'emerald',
                                                'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'         => $globalColor === 'amber',
                                                'bg-accent-100 dark:bg-accent-900/30 text-accent-700 dark:text-accent-300'     => $globalColor === 'accent',
                                                'bg-ink-100 dark:bg-ink-800 text-ink-500 dark:text-ink-400'                    => $globalColor === 'ink',
                                            ])>{{ $globalGrade }}</span>
                                        @else
                                            <span class="text-ink-300 dark:text-ink-700 text-sm">—</span>
                                        @endif
                                    </flux:table.cell>

                                    {{-- Performance grade badge --}}
                                    @php
                                        $perfGrade = $last?->performance_grade;
                                        $perfColor = \LaravelVitals\Support\Health::colorForScore($last?->score_performance);
                                    @endphp
                                    <flux:table.cell align="center">
                                        @if ($perfGrade !== null)
                                            <span @class([
                                                'inline-flex items-center justify-center size-8 rounded-lg font-semibold text-sm',
                                                'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' => $perfColor === 'emerald',
                                                'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'         => $perfColor === 'amber',
                                                'bg-accent-100 dark:bg-accent-900/30 text-accent-700 dark:text-accent-300'     => $perfColor === 'accent',
                                                'bg-ink-100 dark:bg-ink-800 text-ink-500 dark:text-ink-400'                    => $perfColor === 'ink',
                                            ])>{{ $perfGrade }}</span>
                                        @else
                                            <span class="text-ink-300 dark:text-ink-700 text-sm">—</span>
                                        @endif
                                    </flux:table.cell>

                                    {{-- URL column --}}
                                    <flux:table.cell variant="strong">
                                        <flux:link href="{{ route('vitals.url', $u->id) }}" variant="ghost" class="block">
                                            <div class="font-medium">{{ $u->label }}</div>
                                            <code class="text-[11px] text-ink-500">{{ $u->path }}</code>
                                        </flux:link>
                                    </flux:table.cell>

                                    {{-- Score cells --}}
                                    @foreach (['score_performance' => 'Performance', 'score_accessibility' => 'Accessibility', 'score_best_practices' => 'Best Practices', 'score_seo' => 'SEO'] as $col => $colLabel)
                                        @php
                                            $score = $last?->{$col};
                                            $badgeClasses = \LaravelVitals\Support\ScoreColorClasses::badge($score);
                                        @endphp
                                        <flux:table.cell align="center">
                                            @if ($score !== null)
                                                <span @class([
                                                    'inline-flex items-center justify-center size-9 rounded-xl font-semibold text-sm tabular-nums',
                                                    ...$badgeClasses,
                                                ])>{{ $score }}</span>
                                            @else
                                                <span class="text-ink-300 dark:text-ink-700 text-sm">—</span>
                                            @endif
                                        </flux:table.cell>
                                    @endforeach

                                    {{-- Sparkline --}}
                                    <flux:table.cell align="center">
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
                                    </flux:table.cell>

                                    {{-- Last audit time --}}
                                    <flux:table.cell align="end">
                                        @if ($last !== null && $last->completed_at !== null)
                                            <flux:tooltip :content="__('vitals::vitals.tooltip.last_audit_at', ['timestamp' => $last->completed_at->toDayDateTimeString()])">
                                                <flux:link href="{{ route('vitals.audit', $last->id) }}" variant="subtle" class="text-xs">
                                                    {{ $last->completed_at->diffForHumans(short: true) }}
                                                </flux:link>
                                            </flux:tooltip>
                                        @else
                                            —
                                        @endif
                                    </flux:table.cell>

                                    {{-- Audit count --}}
                                    <flux:table.cell align="end">
                                        <span class="tabular-nums">{{ $u->audits_count }}</span>
                                    </flux:table.cell>

                                    {{-- Action --}}
                                    <flux:table.cell align="end">
                                        <flux:button href="{{ route('vitals.url', $u->id) }}" variant="ghost" size="sm" icon="arrow-right">{{ __('vitals::vitals.actions.view') }}</flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </div>
        @endif

        {{-- All URLs section --}}
        <div class="{{ $pinnedUrls->isNotEmpty() ? 'mt-8' : '' }}">
            @if ($pinnedUrls->isNotEmpty())
                <div class="flex items-baseline gap-3 mb-3">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">{{ __('vitals::vitals.urls_page.all_urls') }}</h2>
                    <span class="text-xs text-ink-500">{{ $allUrls->count() }}</span>
                </div>
            @endif
            <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-8"></flux:table.column>
                        <flux:table.column align="center">{{ __('vitals::vitals.tables.global') }}</flux:table.column>
                        <flux:table.column align="center">{{ __('vitals::vitals.tables.perf_grade') }}</flux:table.column>
                        <flux:table.column>URL</flux:table.column>
                        <flux:table.column align="center">Perf</flux:table.column>
                        <flux:table.column align="center">A11y</flux:table.column>
                        <flux:table.column align="center">BP</flux:table.column>
                        <flux:table.column align="center">SEO</flux:table.column>
                        <flux:table.column align="center">{{ __('vitals::vitals.tables.trend') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('vitals::vitals.tables.last') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('vitals::vitals.tables.audits') }}</flux:table.column>
                        <flux:table.column align="end" class="w-20"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($allUrls as $u)
                            @php $last = $lastAudits[$u->id] ?? null; @endphp
                            <flux:table.row :key="$u->id">
                                {{-- Star / pin button --}}
                                <flux:table.cell>
                                    <flux:button
                                        wire:click="togglePin({{ $u->id }})"
                                        variant="ghost"
                                        size="xs"
                                        icon="star"
                                        :icon:variant="$u->pinned_at ? 'solid' : 'outline'"
                                        @class([
                                            'text-amber-500 hover:text-amber-600' => (bool) $u->pinned_at,
                                            'text-ink-300 hover:text-amber-500'   => ! $u->pinned_at,
                                        ])
                                        :tooltip="$u->pinned_at ? __('vitals::vitals.tooltip.unpin') : __('vitals::vitals.tooltip.pin')"
                                    />
                                </flux:table.cell>

                                {{-- Global grade badge --}}
                                @php
                                    $globalGrade = $last?->global_grade;
                                    $globalScore = $last !== null
                                        ? (int) round(collect([$last->score_performance, $last->score_accessibility, $last->score_best_practices, $last->score_seo])->filter(fn ($v) => $v !== null)->avg() ?? 0)
                                        : null;
                                    $globalColor = \LaravelVitals\Support\Health::colorForScore($globalScore);
                                @endphp
                                <flux:table.cell align="center">
                                    @if ($globalGrade !== null)
                                        <span @class([
                                            'inline-flex items-center justify-center size-9 rounded-xl font-bold text-base',
                                            'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' => $globalColor === 'emerald',
                                            'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'         => $globalColor === 'amber',
                                            'bg-accent-100 dark:bg-accent-900/30 text-accent-700 dark:text-accent-300'     => $globalColor === 'accent',
                                            'bg-ink-100 dark:bg-ink-800 text-ink-500 dark:text-ink-400'                    => $globalColor === 'ink',
                                        ])>{{ $globalGrade }}</span>
                                    @else
                                        <span class="text-ink-300 dark:text-ink-700 text-sm">—</span>
                                    @endif
                                </flux:table.cell>

                                {{-- Performance grade badge --}}
                                @php
                                    $perfGrade = $last?->performance_grade;
                                    $perfColor = \LaravelVitals\Support\Health::colorForScore($last?->score_performance);
                                @endphp
                                <flux:table.cell align="center">
                                    @if ($perfGrade !== null)
                                        <span @class([
                                            'inline-flex items-center justify-center size-8 rounded-lg font-semibold text-sm',
                                            'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' => $perfColor === 'emerald',
                                            'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'         => $perfColor === 'amber',
                                            'bg-accent-100 dark:bg-accent-900/30 text-accent-700 dark:text-accent-300'     => $perfColor === 'accent',
                                            'bg-ink-100 dark:bg-ink-800 text-ink-500 dark:text-ink-400'                    => $perfColor === 'ink',
                                        ])>{{ $perfGrade }}</span>
                                    @else
                                        <span class="text-ink-300 dark:text-ink-700 text-sm">—</span>
                                    @endif
                                </flux:table.cell>

                                {{-- URL column --}}
                                <flux:table.cell variant="strong">
                                    <flux:link href="{{ route('vitals.url', $u->id) }}" variant="ghost" class="block">
                                        <div class="font-medium">{{ $u->label }}</div>
                                        <code class="text-[11px] text-ink-500">{{ $u->path }}</code>
                                    </flux:link>
                                </flux:table.cell>

                                {{-- Score cells --}}
                                @foreach (['score_performance' => 'Performance', 'score_accessibility' => 'Accessibility', 'score_best_practices' => 'Best Practices', 'score_seo' => 'SEO'] as $col => $colLabel)
                                    @php
                                        $score = $last?->{$col};
                                        $badgeClasses = \LaravelVitals\Support\ScoreColorClasses::badge($score);
                                    @endphp
                                    <flux:table.cell align="center">
                                        @if ($score !== null)
                                            <span @class([
                                                'inline-flex items-center justify-center size-9 rounded-xl font-semibold text-sm tabular-nums',
                                                ...$badgeClasses,
                                            ])>{{ $score }}</span>
                                        @else
                                            <span class="text-ink-300 dark:text-ink-700 text-sm">—</span>
                                        @endif
                                    </flux:table.cell>
                                @endforeach

                                {{-- Sparkline --}}
                                <flux:table.cell align="center">
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
                                </flux:table.cell>

                                {{-- Last audit time --}}
                                <flux:table.cell align="end">
                                    @if ($last !== null && $last->completed_at !== null)
                                        <flux:tooltip :content="__('vitals::vitals.tooltip.last_audit_at', ['timestamp' => $last->completed_at->toDayDateTimeString()])">
                                            <flux:link href="{{ route('vitals.audit', $last->id) }}" variant="subtle" class="text-xs">
                                                {{ $last->completed_at->diffForHumans(short: true) }}
                                            </flux:link>
                                        </flux:tooltip>
                                    @else
                                        —
                                    @endif
                                </flux:table.cell>

                                {{-- Audit count --}}
                                <flux:table.cell align="end">
                                    <span class="tabular-nums">{{ $u->audits_count }}</span>
                                </flux:table.cell>

                                {{-- Action --}}
                                <flux:table.cell align="end">
                                    <flux:button href="{{ route('vitals.url', $u->id) }}" variant="ghost" size="sm" icon="arrow-right">{{ __('vitals::vitals.actions.view') }}</flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    @endif
</div>
