<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('vitals::vitals.pages.learn.title') }}</h1>
            <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.pages.learn.subtitle') }}</p>
        </div>
        <flux:badge color="zinc">{{ $allCount }} {{ __('vitals::vitals.learn_page.known_issues') }}</flux:badge>
    </div>

    {{-- Browse tile grid (shown when filter = all) --}}
    @if ($filter === 'all')
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach ($this->categoryTiles() as $key => $tile)
                <button type="button"
                        wire:click="$set('filter', '{{ $key }}')"
                        @class([
                            'group rounded-2xl border p-5 text-left transition-colors duration-150',
                            ...\LaravelVitals\Support\LearnTileClasses::tileBorder($tile['color']),
                            ...\LaravelVitals\Support\LearnTileClasses::tileBg($tile['color']),
                            ...\LaravelVitals\Support\LearnTileClasses::hoverBg($tile['color']),
                        ])>
                    <div class="flex items-center justify-between mb-3">
                        <span @class([
                            'flex h-8 w-8 items-center justify-center rounded-lg',
                            ...\LaravelVitals\Support\LearnTileClasses::iconBg($tile['color']),
                        ])>
                            <flux:icon name="{{ $tile['icon'] }}" @class([
                                'size-4',
                                ...\LaravelVitals\Support\LearnTileClasses::iconText($tile['color']),
                            ]) />
                        </span>
                        @if ($tile['active'] > 0)
                            <span @class([
                                'text-xs font-semibold tabular-nums',
                                ...\LaravelVitals\Support\LearnTileClasses::countText($tile['color']),
                            ])>
                                {{ $tile['active'] }} {{ __('vitals::vitals.learn_page.active') }}
                            </span>
                        @endif
                    </div>
                    <div @class([
                        'text-base font-semibold text-ink-900 dark:text-ink-100 transition-colors',
                        ...\LaravelVitals\Support\LearnTileClasses::groupHoverText($tile['color']),
                    ])>{{ $tile['label'] }}</div>
                    <div class="text-xs text-ink-500 mt-0.5">{{ $tile['count'] }} {{ Str::plural('issue', $tile['count']) }}</div>
                </button>
            @endforeach
        </div>
    @else
        {{-- Back to browse link --}}
        <div class="flex items-center gap-3">
            <flux:button wire:click="$set('filter', 'all')" variant="ghost" size="sm" icon="arrow-left">{{ __('vitals::vitals.learn_page.all_categories_label') }}</flux:button>
            <span class="text-sm text-ink-500">{{ __('vitals::vitals.learn_page.showing') }} {{ ucfirst(str_replace('_', ' ', $filter)) }}</span>
        </div>
    @endif

    {{-- Filter tabs --}}
    <div class="flex">
        <flux:radio.group wire:model.live="filter" variant="segmented" size="sm" class="shrink-0">
            <flux:radio value="all" label="All" />
            <flux:radio value="performance" label="Performance" />
            <flux:radio value="accessibility" label="Accessibility" />
            <flux:radio value="best_practices" label="Best Practices" />
            <flux:radio value="seo" label="SEO" />
        </flux:radio.group>
    </div>

    @forelse ($grouped as $category => $items)
        <div class="mt-10 first:mt-0">
            {{-- Category label --}}
            <div class="flex items-baseline gap-3 mb-4">
                <h2 class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-400">
                    {{ str_replace('_', ' ', $category) }}
                </h2>
                <span class="text-xs text-ink-500">{{ count($items) }} {{ Str::plural(__('vitals::vitals.learn_page.item'), count($items)) }}</span>
                <div class="flex-1 h-px bg-ink-200 dark:bg-ink-800"></div>
            </div>

            {{-- Cards grid --}}
            <div class="space-y-4">
                @foreach ($items as $entry)
                    @php $sev = $entry['descriptor']->severity; @endphp
                    <article id="{{ $entry['key'] }}"
                             @class([
                                'rounded-2xl border p-6 scroll-mt-24',
                                ...$sev->containerClasses(),
                             ])>
                        {{-- Header --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <flux:icon name="{{ $sev->fluxCalloutIcon() }}" @class([
                                'size-5 shrink-0',
                                $sev->iconTextColor(),
                            ]) />
                            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __($entry['descriptor']->titleKey) }}</h3>
                            <flux:badge color="{{ $sev->fluxBadgeColor() }}" size="sm">{{ $sev->label() }}</flux:badge>
                            @if (($entry['active_count'] ?? 0) > 0)
                                <flux:link href="{{ route('vitals.issue.detail', ['auditKey' => $entry['key']]) }}" class="text-xs font-semibold inline-flex items-center gap-1">
                                    <flux:icon name="rectangle-stack" class="size-3" />
                                    {{ __('vitals::vitals.learn_page.active_in_app', ['count' => $entry['active_count']]) }}
                                </flux:link>
                            @endif
                            <code class="ml-auto text-[11px] font-mono text-ink-400">{{ $entry['key'] }}</code>
                        </div>

                        {{-- Description --}}
                        <p class="mt-2 text-sm text-ink-600 dark:text-ink-400">{{ __($entry['descriptor']->descriptionKey) }}</p>

                        @if ($entry['docs'])
                            {{-- Why --}}
                            <div class="mt-4 rounded-xl border border-ink-200 dark:border-ink-800 bg-canvas dark:bg-ink-950 p-4">
                                <div class="flex items-start gap-2">
                                    <flux:icon.information-circle class="size-4 text-sky-500 shrink-0 mt-0.5" />
                                    <p class="text-sm text-ink-700 dark:text-ink-300">{{ $entry['docs']['why'] }}</p>
                                </div>
                                @if (! empty($entry['docs']['impact']))
                                    <div class="mt-3 flex items-center gap-2 text-xs pt-3 border-t border-ink-200 dark:border-ink-800">
                                        <flux:icon.bolt class="size-3.5 text-amber-500" />
                                        <span class="text-amber-700 dark:text-amber-400 font-medium">{{ $entry['docs']['impact'] }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Doc links --}}
                            @if (! empty($entry['docs']['docs']))
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($entry['docs']['docs'] as $doc)
                                        <flux:button
                                            href="{{ $doc['url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            size="sm"
                                            variant="ghost"
                                            icon="arrow-top-right-on-square"
                                        >{{ $doc['label'] }}</flux:button>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Good / Bad code blocks --}}
                            @if (! empty($entry['docs']['good']) || ! empty($entry['docs']['bad']))
                                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-3">
                                    @if (! empty($entry['docs']['good']))
                                        <div class="rounded-2xl border border-emerald-200/60 dark:border-emerald-900/40 bg-emerald-50/40 dark:bg-emerald-900/10 overflow-hidden">
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 border-b border-emerald-200/60 dark:border-emerald-900/40">
                                                <flux:icon.check-circle class="size-3.5" />
                                                {{ __('vitals::vitals.audit_detail.recommended') }}
                                            </div>
                                            <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-emerald-800 dark:text-emerald-200">{{ $entry['docs']['good'] }}</code></pre>
                                        </div>
                                    @endif
                                    @if (! empty($entry['docs']['bad']))
                                        <div class="rounded-2xl border border-accent-200/60 dark:border-accent-900/40 bg-accent-50/40 dark:bg-accent-900/10 overflow-hidden">
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-accent-700 dark:text-accent-300 border-b border-accent-200/60 dark:border-accent-900/40">
                                                <flux:icon.x-circle class="size-3.5" />
                                                {{ __('vitals::vitals.audit_detail.avoid') }}
                                            </div>
                                            <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-accent-800 dark:text-accent-200">{{ $entry['docs']['bad'] }}</code></pre>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-6">
            <p class="text-sm text-ink-500">No items match this filter.</p>
        </div>
    @endforelse
</div>
