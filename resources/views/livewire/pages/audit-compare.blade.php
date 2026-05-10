<div class="space-y-6">
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('vitals.urls') }}">URLs</flux:breadcrumbs.item>
        @if ($auditA->url)
            <flux:breadcrumbs.item href="{{ route('vitals.url', $auditA->url->id) }}">{{ $auditA->url->label }}</flux:breadcrumbs.item>
        @endif
        <flux:breadcrumbs.item>{{ __('vitals::vitals.compare.title') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Header: two audit cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach ([['audit' => $auditA, 'label' => __('vitals::vitals.compare.audit_a')], ['audit' => $auditB, 'label' => __('vitals::vitals.compare.audit_b')]] as $side)
            @php $a = $side['audit']; @endphp
            <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-5 flex items-start gap-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-100 dark:bg-accent-900/40">
                    <flux:icon name="{{ $a->device === 'mobile' ? 'device-phone-mobile' : 'computer-desktop' }}" class="size-5 text-accent-600 dark:text-accent-400" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-semibold uppercase tracking-wide text-ink-500 mb-0.5">{{ $side['label'] }}</div>
                    <div class="font-semibold text-sm truncate">{{ $a->url?->label }}</div>
                    <div class="text-xs text-ink-500 mt-0.5">{{ $a->completed_at?->format('M j, Y H:i') }}</div>
                    <div class="mt-1 flex items-center gap-2">
                        <flux:badge color="zinc" size="sm">{{ $a->device }}</flux:badge>
                        <flux:badge color="zinc" size="sm">{{ $a->driver }}</flux:badge>
                    </div>
                </div>
                <a href="{{ route('vitals.audit', $a) }}" class="text-xs text-accent-600 dark:text-accent-400 hover:underline shrink-0">{{ __('vitals::vitals.compare.view') }}</a>
            </div>
        @endforeach
    </div>

    {{-- Score grid --}}
    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
        <h2 class="text-base font-semibold mb-4">{{ __('vitals::vitals.compare.scores') }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-ink-200/60 dark:border-ink-800/60">
                        <th class="text-left py-2 pr-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.metric') }}</th>
                        <th class="text-right py-2 px-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.audit_a') }}</th>
                        <th class="text-right py-2 px-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.audit_b') }}</th>
                        <th class="text-right py-2 pl-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.delta') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ([
                        'score_performance'    => __('vitals::vitals.compare.performance'),
                        'score_accessibility'  => __('vitals::vitals.compare.accessibility'),
                        'score_best_practices' => __('vitals::vitals.compare.best_practices'),
                        'score_seo'            => __('vitals::vitals.compare.seo'),
                    ] as $key => $label)
                        @php
                            $row = $scoreDelta[$key];
                            $d = $row['delta'];
                            $dColor = $d === null ? 'ink' : ($d > 0 ? 'emerald' : ($d < 0 ? 'accent' : 'ink'));
                        @endphp
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $label }}</td>
                            <td class="py-2 px-4 text-right tabular-nums">{{ $row['a'] ?? '—' }}</td>
                            <td class="py-2 px-4 text-right tabular-nums">{{ $row['b'] ?? '—' }}</td>
                            <td class="py-2 pl-4 text-right">
                                @if ($d !== null && $d != 0)
                                    <flux:badge color="{{ $dColor }}" size="sm">
                                        {{ $d > 0 ? '▲ +' . $d : '▼ ' . $d }}
                                    </flux:badge>
                                @elseif ($d !== null)
                                    <span class="text-ink-400">→</span>
                                @else
                                    <span class="text-ink-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- CWV grid --}}
    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
        <h2 class="text-base font-semibold mb-4">{{ __('vitals::vitals.compare.cwv') }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-ink-200/60 dark:border-ink-800/60">
                        <th class="text-left py-2 pr-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.metric') }}</th>
                        <th class="text-right py-2 px-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.audit_a') }}</th>
                        <th class="text-right py-2 px-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.audit_b') }}</th>
                        <th class="text-right py-2 pl-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.delta') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ([
                        'lcp_ms'  => ['label' => 'LCP', 'unit' => 'ms', 'lower_is_better' => true],
                        'inp_ms'  => ['label' => 'INP', 'unit' => 'ms', 'lower_is_better' => true],
                        'cls'     => ['label' => 'CLS', 'unit' => '',   'lower_is_better' => true],
                        'ttfb_ms' => ['label' => 'TTFB','unit' => 'ms', 'lower_is_better' => true],
                    ] as $key => $meta)
                        @php
                            $row = $cwvDelta[$key];
                            $d = $row['delta'];
                            $isBetter = $d !== null && (($meta['lower_is_better'] && $d < 0) || (! $meta['lower_is_better'] && $d > 0));
                            $isWorse  = $d !== null && (($meta['lower_is_better'] && $d > 0) || (! $meta['lower_is_better'] && $d < 0));
                            $dColor   = $d === null ? 'ink' : ($isBetter ? 'emerald' : ($isWorse ? 'accent' : 'ink'));
                            $format   = fn ($v) => $v !== null ? (($key === 'cls') ? number_format((float)$v, 3) : number_format((float)$v, 0)) . ($meta['unit'] ? ' ' . $meta['unit'] : '') : '—';
                        @endphp
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $meta['label'] }}</td>
                            <td class="py-2 px-4 text-right tabular-nums">{{ $format($row['a']) }}</td>
                            <td class="py-2 px-4 text-right tabular-nums">{{ $format($row['b']) }}</td>
                            <td class="py-2 pl-4 text-right">
                                @if ($d !== null && $d != 0)
                                    <flux:badge color="{{ $dColor }}" size="sm">
                                        {{ $isBetter ? '▲ ' : '▼ ' }}{{ $format(abs($d)) }}
                                    </flux:badge>
                                @elseif ($d !== null)
                                    <span class="text-ink-400">→</span>
                                @else
                                    <span class="text-ink-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recommendations diff --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Resolved in B --}}
        <div class="rounded-2xl border border-emerald-200/60 dark:border-emerald-800/40 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.check-circle class="size-5 text-emerald-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.compare.resolved') }}</h2>
                <flux:badge color="emerald" size="sm">{{ $resolved->count() }}</flux:badge>
            </div>
            @if ($resolved->isEmpty())
                <p class="text-sm text-ink-500">{{ __('vitals::vitals.compare.none_resolved') }}</p>
            @else
                <ul class="space-y-2">
                    @foreach ($resolved as $r)
                        <li class="flex items-center gap-2 text-sm">
                            <flux:badge color="{{ $r->severity === 'critical' ? 'rose' : 'amber' }}" size="sm">{{ $r->severity }}</flux:badge>
                            <span class="text-emerald-700 dark:text-emerald-300 line-through opacity-70">{{ __($r->title_key) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- New in B --}}
        <div class="rounded-2xl border border-accent-200/60 dark:border-accent-800/40 bg-paper dark:bg-ink-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <flux:icon.exclamation-circle class="size-5 text-accent-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.compare.new_issues') }}</h2>
                <flux:badge color="{{ $newInB->count() > 0 ? 'rose' : 'emerald' }}" size="sm">{{ $newInB->count() }}</flux:badge>
            </div>
            @if ($newInB->isEmpty())
                <p class="text-sm text-ink-500">{{ __('vitals::vitals.compare.none_new') }}</p>
            @else
                <ul class="space-y-2">
                    @foreach ($newInB as $r)
                        <li class="flex items-center gap-2 text-sm">
                            <flux:badge color="{{ $r->severity === 'critical' ? 'rose' : 'amber' }}" size="sm">{{ $r->severity }}</flux:badge>
                            <span>{{ __($r->title_key) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Telemetry diff --}}
    @if ($auditA->telemetry || $auditB->telemetry)
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <h2 class="text-base font-semibold mb-4">{{ __('vitals::vitals.compare.telemetry') }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-200/60 dark:border-ink-800/60">
                            <th class="text-left py-2 pr-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.metric') }}</th>
                            <th class="text-right py-2 px-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.audit_a') }}</th>
                            <th class="text-right py-2 px-4 text-ink-500 font-medium">{{ __('vitals::vitals.compare.audit_b') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ([
                            'queries_count'   => __('vitals::vitals.compare.queries'),
                            'queries_time_ms' => __('vitals::vitals.compare.query_time'),
                            'peak_memory_bytes' => __('vitals::vitals.compare.peak_memory'),
                            'views_time_ms'   => __('vitals::vitals.compare.view_render'),
                        ] as $col => $label)
                            <tr>
                                <td class="py-2 pr-4 font-medium">{{ $label }}</td>
                                <td class="py-2 px-4 text-right tabular-nums">
                                    @if ($auditA->telemetry)
                                        {{ $col === 'peak_memory_bytes'
                                            ? ($auditA->telemetry->{$col} ? round($auditA->telemetry->{$col} / 1024 / 1024, 1) . ' MB' : '—')
                                            : ($auditA->telemetry->{$col} ?? '—') }}
                                    @else —
                                    @endif
                                </td>
                                <td class="py-2 px-4 text-right tabular-nums">
                                    @if ($auditB->telemetry)
                                        {{ $col === 'peak_memory_bytes'
                                            ? ($auditB->telemetry->{$col} ? round($auditB->telemetry->{$col} / 1024 / 1024, 1) . ' MB' : '—')
                                            : ($auditB->telemetry->{$col} ?? '—') }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
