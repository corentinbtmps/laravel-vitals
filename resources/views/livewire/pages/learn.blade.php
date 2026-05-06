<div class="space-y-8">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-semibold tracking-[-0.02em] text-ink-900 dark:text-ink-100">Learn</h1>
            <p class="mt-1 text-sm text-ink-500">Reference for every issue Laravel Vitals can detect</p>
        </div>
        <span class="text-xs text-ink-400 tabular-nums">{{ $allCount }} known issues</span>
    </div>

    {{-- Filter tabs — flat, no card --}}
    <div class="flex flex-wrap gap-1.5 border-b border-ink-200 dark:border-ink-800 pb-4">
        @foreach (['all' => 'All', 'performance' => 'Performance', 'accessibility' => 'Accessibility', 'best_practices' => 'Best Practices', 'seo' => 'SEO'] as $value => $label)
            <button
                wire:click="setFilter('{{ $value }}')"
                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors duration-150
                    {{ $filter === $value
                        ? 'bg-ink-900 text-ink-50 dark:bg-ink-100 dark:text-ink-950'
                        : 'text-ink-500 hover:text-ink-800 dark:hover:text-ink-200 hover:bg-ink-100 dark:hover:bg-ink-800' }}"
            >{{ $label }}</button>
        @endforeach
    </div>

    @forelse ($grouped as $category => $items)
        <div class="space-y-6">
            <p class="label-caps text-ink-400">{{ str_replace('_', ' ', $category) }}</p>

            @foreach ($items as $entry)
                @php
                    $sevDotClass = match ($entry['descriptor']->severity) {
                        'critical' => 'bg-accent-500',
                        'warning'  => 'bg-amber-500',
                        default    => 'bg-emerald-500',
                    };
                    $sevTextClass = match ($entry['descriptor']->severity) {
                        'critical' => 'text-accent-600 dark:text-accent-500',
                        'warning'  => 'text-amber-600 dark:text-amber-400',
                        default    => 'text-emerald-600 dark:text-emerald-400',
                    };
                    $sevBorderClass = match ($entry['descriptor']->severity) {
                        'critical' => 'border-accent-200 dark:border-accent-700/30',
                        'warning'  => 'border-amber-200 dark:border-amber-900/40',
                        default    => 'border-ink-200 dark:border-ink-800',
                    };
                @endphp
                <div id="{{ $entry['key'] }}" class="border {{ $sevBorderClass }} rounded-xl bg-canvas dark:bg-ink-900 p-5">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="flex items-start gap-2.5 flex-1 min-w-0">
                            <span class="mt-1.5 shrink-0 size-1.5 rounded-full {{ $sevDotClass }}"></span>
                            <h3 class="font-semibold text-base text-ink-800 dark:text-ink-200">{{ __($entry['descriptor']->titleKey) }}</h3>
                        </div>
                        <code class="shrink-0 text-[11px] text-ink-400 font-mono">{{ $entry['key'] }}</code>
                    </div>
                    <p class="text-sm text-ink-500 dark:text-ink-400 ml-4">{{ __($entry['descriptor']->descriptionKey) }}</p>

                    @if ($entry['docs'])
                        <div class="mt-3 ml-4 text-sm text-ink-600 dark:text-ink-300">{{ $entry['docs']['why'] }}</div>

                        @if (! empty($entry['docs']['impact']))
                            <div class="mt-2 ml-4 text-xs font-medium text-amber-600 dark:text-amber-400">{{ $entry['docs']['impact'] }}</div>
                        @endif

                        @if (! empty($entry['docs']['docs']))
                            <div class="mt-3 ml-4 flex flex-wrap gap-2">
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

                        @if (! empty($entry['docs']['good']) || ! empty($entry['docs']['bad']))
                            <div class="mt-4 ml-4 grid grid-cols-1 lg:grid-cols-2 gap-3">
                                @if (! empty($entry['docs']['good']))
                                    <div class="rounded-lg border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/40 dark:bg-emerald-900/10 overflow-hidden">
                                        <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 border-b border-emerald-200 dark:border-emerald-900/40">
                                            <flux:icon.check-circle class="size-3.5" />
                                            Recommended
                                        </div>
                                        <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-emerald-800 dark:text-emerald-200 font-mono">{{ $entry['docs']['good'] }}</code></pre>
                                    </div>
                                @endif
                                @if (! empty($entry['docs']['bad']))
                                    <div class="rounded-lg border border-accent-200 dark:border-accent-700/30 bg-accent-50/40 dark:bg-accent-700/5 overflow-hidden">
                                        <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-accent-700 dark:text-accent-400 border-b border-accent-200 dark:border-accent-700/30">
                                            <flux:icon.x-circle class="size-3.5" />
                                            Avoid
                                        </div>
                                        <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-accent-800 dark:text-accent-300 font-mono">{{ $entry['docs']['bad'] }}</code></pre>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    @empty
        <div class="border border-ink-200 dark:border-ink-800 rounded-xl bg-canvas dark:bg-ink-900 p-8 text-center">
            <p class="text-sm text-ink-500">No items match this filter.</p>
        </div>
    @endforelse
</div>
