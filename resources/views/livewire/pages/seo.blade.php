<div class="space-y-6">

    {{-- Page header + period selector --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('vitals::vitals.seo.page_title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('vitals::vitals.seo.page_subtitle') }}</p>
        </div>

        {{-- Period selector --}}
        <div class="flex items-center gap-1 rounded-xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-1 flex-shrink-0">
            @foreach ($availablePeriods as $p)
                <button
                    wire:click="setPeriod('{{ $p->value }}')"
                    @class([
                        'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                        'bg-accent-500 text-white shadow-sm'  => $period === $p,
                        'text-ink-500 hover:text-ink-800 dark:hover:text-ink-200 hover:bg-ink-100 dark:hover:bg-ink-800' => $period !== $p,
                    ])
                >{{ $p->buttonLabel() }}</button>
            @endforeach
        </div>
    </div>

    {{-- Top stats card --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <p class="text-xs uppercase tracking-wider text-ink-500 mb-2">{{ __('vitals::vitals.seo.avg_score') }}</p>
            @if ($avgScore !== null)
                @php $scoreColor = \LaravelVitals\Support\Health::colorForScore($avgScore); @endphp
                <div @class([
                    'text-4xl font-bold tabular-nums',
                    'text-emerald-500' => $scoreColor === 'emerald',
                    'text-amber-500'   => $scoreColor === 'amber',
                    'text-accent-500'  => $scoreColor === 'accent',
                    'text-ink-400'     => $scoreColor === 'ink',
                ])>{{ $avgScore }}</div>
                <p class="text-sm text-ink-500 mt-1">{{ \LaravelVitals\Support\Health::grade($avgScore) }}</p>
            @else
                <p class="text-2xl font-semibold text-ink-300">—</p>
            @endif
        </div>

        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <p class="text-xs uppercase tracking-wider text-ink-500 mb-2">{{ __('vitals::vitals.seo.urls_with_failures') }}</p>
            <div @class([
                'text-4xl font-bold tabular-nums',
                'text-accent-500' => $urlsWithFailures > 0,
                'text-emerald-500' => $urlsWithFailures === 0,
            ])>{{ $urlsWithFailures }}</div>
            <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.seo.urls_label') }}</p>
        </div>
    </div>

    {{-- Per-URL SEO table --}}
    @if (!empty($perUrl))
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 overflow-hidden">
            <div class="px-6 py-4 border-b border-ink-100 dark:border-ink-800">
                <h2 class="text-sm font-semibold">{{ __('vitals::vitals.seo.per_url_title') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 dark:border-ink-800 text-left">
                            <th class="px-6 py-3 text-xs font-medium text-ink-500 uppercase tracking-wider">{{ __('vitals::vitals.tables.url') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-ink-500 uppercase tracking-wider text-center">{{ __('vitals::vitals.seo.custom_score') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-ink-500 uppercase tracking-wider text-center">{{ __('vitals::vitals.seo.lighthouse_score') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-ink-500 uppercase tracking-wider text-center">{{ __('vitals::vitals.seo.failing_checks', ['count' => '']) }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-ink-500 uppercase tracking-wider text-right">{{ __('vitals::vitals.tables.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-50 dark:divide-ink-800/50">
                        @foreach ($perUrl as $row)
                            @php
                                $score = $row['vitals_seo_score'];
                                $scoreColor = $score !== null ? \LaravelVitals\Support\Health::colorForScore($score) : 'ink';
                            @endphp
                            <tr class="hover:bg-ink-50/50 dark:hover:bg-ink-800/30 transition-colors">
                                <td class="px-6 py-3">
                                    <div class="font-medium">{{ $row['url']->label }}</div>
                                    <div class="text-xs text-ink-400 font-mono">{{ $row['url']->path }}</div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if ($score !== null)
                                        <div @class([
                                            'text-xl font-bold tabular-nums',
                                            'text-emerald-500' => $scoreColor === 'emerald',
                                            'text-amber-500'   => $scoreColor === 'amber',
                                            'text-accent-500'  => $scoreColor === 'accent',
                                            'text-ink-400'     => $scoreColor === 'ink',
                                        ])>{{ $score }}</div>
                                        <div class="text-xs text-ink-400">{{ $row['grade'] }}</div>
                                    @else
                                        <span class="text-ink-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if ($row['lighthouse_seo'] !== null)
                                        <span class="text-sm tabular-nums text-ink-600 dark:text-ink-400">{{ $row['lighthouse_seo'] }}</span>
                                    @else
                                        <span class="text-ink-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if ($row['failing_checks'] > 0)
                                        <flux:badge color="rose" size="sm">{{ $row['failing_checks'] }}</flux:badge>
                                    @else
                                        <flux:badge color="emerald" size="sm">0</flux:badge>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('vitals.audit.seo', $row['audit']->id) }}"
                                       class="text-xs text-accent-600 hover:text-accent-700 dark:text-accent-400 dark:hover:text-accent-300 font-medium transition-colors">
                                        {{ __('vitals::vitals.actions.view') }} →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Top failing checks with category filter --}}
    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 overflow-hidden">
        <div class="px-6 py-4 border-b border-ink-100 dark:border-ink-800 flex items-center justify-between gap-4 flex-wrap">
            <h2 class="text-sm font-semibold">{{ __('vitals::vitals.seo.top_failing') }}</h2>

            {{-- Category tabs --}}
            <div class="flex items-center gap-1">
                @foreach (['all', 'configuration', 'content', 'meta', 'performance'] as $cat)
                    <button
                        wire:click="setCategory('{{ $cat }}')"
                        @class([
                            'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                            'bg-accent-500 text-white' => $category === $cat,
                            'text-ink-500 hover:text-ink-700 dark:hover:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800' => $category !== $cat,
                        ])
                    >
                        @if ($cat === 'all')
                            {{ __('vitals::vitals.actions.all_categories') }}
                        @else
                            {{ __('vitals::vitals.seo.categories.' . $cat) }}
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        @if ($topFailing->isNotEmpty())
            <div class="divide-y divide-ink-50 dark:divide-ink-800/50">
                @foreach ($topFailing as $item)
                    <div class="flex items-center gap-4 px-6 py-3 hover:bg-ink-50/50 dark:hover:bg-ink-800/30 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ __($item->title_key) }}</div>
                            <div class="text-xs text-ink-400 font-mono">{{ $item->audit_key }}</div>
                        </div>
                        <flux:badge
                            color="{{ match($item->severity?->value ?? '') { 'critical' => 'rose', 'warning' => 'amber', default => 'sky' } }}"
                            size="sm"
                        >× {{ $item->occurrences }} {{ __('vitals::vitals.seo.urls_label') }}</flux:badge>
                        @if ($item->sample_audit_id)
                            <a href="{{ route('vitals.audit.seo', $item->sample_audit_id) }}"
                               class="text-xs text-accent-600 hover:text-accent-700 dark:text-accent-400 shrink-0 transition-colors">
                                {{ __('vitals::vitals.actions.view') }} →
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-10 text-center">
                <flux:icon name="check-badge" class="size-10 text-emerald-400 mx-auto mb-2" />
                <p class="text-sm text-ink-500">{{ __('vitals::vitals.seo.no_seo_issues') }}</p>
            </div>
        @endif
    </div>

</div>
