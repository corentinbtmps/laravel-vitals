<div class="space-y-6">
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('vitals.urls') }}">URLs</flux:breadcrumbs.item>
        @if ($audit->url)
            <flux:breadcrumbs.item href="{{ route('vitals.url', $audit->url->id) }}">{{ $audit->url->label }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('vitals.audit', $audit->id) }}">{{ __('vitals::vitals.seo.audit') }}</flux:breadcrumbs.item>
        @endif
        <flux:breadcrumbs.item>{{ __('vitals::vitals.seo.title') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Hero --}}
    <div class="rounded-3xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6 md:p-8">
        <div class="flex items-start justify-between gap-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">{{ __('vitals::vitals.seo.heading') }}</h1>
                <p class="mt-1 text-sm text-ink-500">{{ $audit->url?->label }} · {{ $audit->completed_at?->format('M j, Y H:i') }}</p>
            </div>
            @if ($audit->score_seo !== null)
                @php
                    $seoColor = \LaravelVitals\Support\Health::colorForScore($audit->score_seo);
                    $seoGrade = \LaravelVitals\Support\Health::grade($audit->score_seo);
                @endphp
                <div class="text-right shrink-0">
                    <div @class([
                        'text-4xl font-semibold tabular-nums leading-none',
                        'text-emerald-500' => $seoColor === 'emerald',
                        'text-amber-500'   => $seoColor === 'amber',
                        'text-accent-500'  => $seoColor === 'accent',
                        'text-ink-400'     => $seoColor === 'ink',
                    ])>{{ $audit->score_seo }}</div>
                    <div class="text-sm text-ink-500 mt-1">SEO score · {{ $seoGrade }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Checks grid --}}
    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
        <h2 class="text-base font-semibold mb-4">{{ __('vitals::vitals.seo.checks') }}</h2>
        <div class="divide-y divide-ink-100 dark:divide-ink-800">
            @foreach ($checks as $check)
                @php
                    $checkIcon = match ($check['status']) {
                        'pass'  => 'check-circle',
                        'fail'  => 'x-circle',
                        'warn'  => 'exclamation-triangle',
                        default => 'minus-circle',
                    };
                    $checkBadgeColor = match ($check['status']) {
                        'pass'  => 'emerald',
                        'fail'  => 'rose',
                        'warn'  => 'amber',
                        default => 'zinc',
                    };
                @endphp
                <div class="flex items-center gap-3 py-3">
                    <flux:icon name="{{ $checkIcon }}" @class([
                        'size-5 shrink-0',
                        'text-emerald-500' => $check['status'] === 'pass',
                        'text-accent-500'  => $check['status'] === 'fail',
                        'text-amber-500'   => $check['status'] === 'warn',
                        'text-ink-400'     => ! in_array($check['status'], ['pass', 'fail', 'warn'], true),
                    ]) />
                    <span class="flex-1 text-sm font-medium">{{ $check['label'] }}</span>
                    @if ($check['value'])
                        <span class="text-xs text-ink-500">{{ $check['value'] }}</span>
                    @endif
                    <flux:badge color="{{ $checkBadgeColor }}" size="sm">
                        {{ __('vitals::vitals.seo.status_' . $check['status']) }}
                    </flux:badge>
                </div>
            @endforeach
        </div>
    </div>

    {{-- SEO recommendations --}}
    @if ($seoRecos->isNotEmpty())
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <h2 class="text-base font-semibold mb-4">{{ __('vitals::vitals.seo.recommendations') }}</h2>
            <ul class="space-y-3">
                @foreach ($seoRecos as $r)
                    <li class="rounded-lg border border-ink-100 dark:border-ink-800 p-4">
                        <div class="flex items-start gap-3">
                            <flux:badge color="{{ \LaravelVitals\Enums\Severity::fromString($r->severity)->fluxBadgeColor() }}" size="sm">{{ \LaravelVitals\Enums\Severity::fromString($r->severity)->label() }}</flux:badge>
                            <div>
                                <div class="font-medium text-sm">{{ __($r->title_key) }}</div>
                                <div class="text-xs text-ink-500 mt-1">{{ __($r->description_key, $r->translation_params ?? []) }}</div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="text-center py-6">
                <flux:icon name="check-badge" class="size-10 text-emerald-400 mx-auto mb-2" />
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ __('vitals::vitals.seo.no_seo_issues') }}</p>
            </div>
        </div>
    @endif
</div>
