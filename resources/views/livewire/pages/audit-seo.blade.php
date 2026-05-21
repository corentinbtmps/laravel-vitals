<div class="space-y-6">
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('vitals.urls') }}">URLs</flux:breadcrumbs.item>
        @if ($audit->url)
            <flux:breadcrumbs.item href="{{ route('vitals.url', $audit->url->id) }}">{{ $audit->url->label }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('vitals.audit', $audit->id) }}">{{ __('vitals::vitals.seo.audit') }}</flux:breadcrumbs.item>
        @endif
        <flux:breadcrumbs.item>{{ __('vitals::vitals.seo.title') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Hero with score breakdown --}}
    <div class="rounded-3xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">{{ __('vitals::vitals.seo.heading') }}</h1>
                <p class="mt-1 text-sm text-ink-500">{{ $audit->url?->label }} · {{ $audit->completed_at?->format('M j, Y H:i') }}</p>
            </div>

            {{-- Score breakdown side by side --}}
            <div class="flex items-center gap-6 shrink-0">
                @if ($lighthouseScore !== null)
                    @php $lhColor = \LaravelVitals\Support\Health::colorForScore($lighthouseScore); @endphp
                    <div class="text-center">
                        <div @class([
                            'text-3xl font-semibold tabular-nums leading-none',
                            'text-emerald-500' => $lhColor === 'emerald',
                            'text-amber-500'   => $lhColor === 'amber',
                            'text-accent-500'  => $lhColor === 'accent',
                            'text-ink-400'     => $lhColor === 'ink',
                        ])>{{ $lighthouseScore }}</div>
                        <div class="text-xs text-ink-500 mt-1">{{ __('vitals::vitals.seo.lighthouse_score') }}</div>
                    </div>
                @endif

                @if ($vitalsSeoScore !== null)
                    @php $vsColor = \LaravelVitals\Support\Health::colorForScore($vitalsSeoScore); @endphp
                    <div class="text-center">
                        <div @class([
                            'text-4xl font-bold tabular-nums leading-none',
                            'text-emerald-500' => $vsColor === 'emerald',
                            'text-amber-500'   => $vsColor === 'amber',
                            'text-accent-500'  => $vsColor === 'accent',
                            'text-ink-400'     => $vsColor === 'ink',
                        ])>{{ $vitalsSeoScore }}</div>
                        <div class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.seo.custom_score') }} · {{ $vitalsSeoGrade }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Category-grouped checks --}}
    @php
        $categoryOrder = ['configuration', 'content', 'meta', 'performance'];
        $statusIcon = [
            'critical' => 'x-circle',
            'warning'  => 'exclamation-triangle',
            'info'     => 'information-circle',
            'pass'     => 'check-circle',
        ];
    @endphp

    @foreach ($categoryOrder as $cat)
        @if (!empty($checksGrouped[$cat]))
            <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 overflow-hidden">
                <div class="px-6 py-4 border-b border-ink-100 dark:border-ink-800 flex items-center gap-3">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-500">{{ __('vitals::vitals.seo.categories.' . $cat) }}</h2>
                    @php
                        $failCount = count(array_filter($checksGrouped[$cat], fn($c) => $c['status'] !== 'pass'));
                    @endphp
                    @if ($failCount > 0)
                        <flux:badge color="rose" size="sm">{{ $failCount }} {{ __('vitals::vitals.seo.failing_checks', ['count' => $failCount]) }}</flux:badge>
                    @endif
                </div>
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($checksGrouped[$cat] as $check)
                        @php
                            $isPassing = $check['status'] === 'pass';
                            $icon = $statusIcon[$check['status']] ?? 'information-circle';
                        @endphp
                        <div class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <flux:icon name="{{ $icon }}" @class([
                                    'size-5 shrink-0 mt-0.5',
                                    'text-emerald-500' => $isPassing,
                                    'text-accent-500'  => $check['status'] === 'critical',
                                    'text-amber-500'   => $check['status'] === 'warning',
                                    'text-sky-400'     => $check['status'] === 'info',
                                ]) />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-medium">{{ __($check['title_key']) }}</span>
                                        @if (!$isPassing)
                                            <flux:badge
                                                color="{{ match($check['status']) { 'critical' => 'rose', 'warning' => 'amber', default => 'sky' } }}"
                                                size="sm"
                                            >{{ ucfirst($check['status']) }}</flux:badge>
                                        @endif
                                    </div>

                                    @if (!$isPassing && ($check['actual'] || $check['expected']))
                                        <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink-500">
                                            @if ($check['actual'])
                                                <span><span class="font-medium text-ink-700 dark:text-ink-300">Found:</span> {{ $check['actual'] }}</span>
                                            @endif
                                            @if ($check['expected'])
                                                <span><span class="font-medium text-ink-700 dark:text-ink-300">Expected:</span> {{ $check['expected'] }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if (!$isPassing && $check['hint_key'])
                                        @php $hint = __($check['hint_key']); @endphp
                                        @if ($hint !== $check['hint_key'])
                                            <p class="mt-1.5 text-xs text-ink-500 italic">{{ $hint }}</p>
                                        @endif
                                    @endif

                                    @if (!$isPassing && $check['doc_url'])
                                        <div class="mt-1.5">
                                            <flux:link href="{{ $check['doc_url'] }}" external class="text-xs inline-flex items-center gap-1">
                                                {{ __('vitals::vitals.actions.read_docs') }}
                                                <flux:icon name="arrow-top-right-on-square" class="size-3" />
                                            </flux:link>
                                        </div>
                                    @endif

                                    @if (!$isPassing && !empty($check['detail_items']))
                                        <div class="mt-3 rounded-lg border border-ink-100 dark:border-ink-800 overflow-hidden">
                                            <div class="max-h-48 overflow-y-auto">
                                                @foreach (array_slice($check['detail_items'], 0, 10) as $item)
                                                    <div class="flex items-center gap-2 px-3 py-1.5 text-xs border-b border-ink-50 dark:border-ink-800/50 last:border-0">
                                                        <span class="font-mono text-ink-600 dark:text-ink-400 truncate flex-1">{{ $item['url'] ?? $item['status'] ?? '' }}</span>
                                                        @if (!empty($item['status']))
                                                            <flux:badge color="rose" size="sm">{{ $item['status'] }}</flux:badge>
                                                        @endif
                                                        @if (!empty($item['size']))
                                                            <flux:badge color="amber" size="sm">{{ $item['size'] }}</flux:badge>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if ($isPassing && $check['actual'])
                                    <span class="text-xs text-ink-400 shrink-0">{{ $check['actual'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    {{-- Empty state --}}
    @if ($seoRecos->isEmpty())
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <div class="text-center py-6">
                <flux:icon name="check-badge" class="size-10 text-emerald-400 mx-auto mb-2" />
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ __('vitals::vitals.seo.no_seo_issues') }}</p>
            </div>
        </div>
    @endif
</div>
