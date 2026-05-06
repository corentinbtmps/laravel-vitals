<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Learn</h1>
            <p class="text-sm text-ink-500 mt-1">Reference for every issue Laravel Vitals can detect</p>
        </div>
        <flux:badge color="zinc">{{ $allCount }} known issues</flux:badge>
    </div>

    {{-- Filter tabs --}}
    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-4">
        <div class="flex flex-wrap gap-2">
            @foreach (['all' => 'All', 'performance' => 'Performance', 'accessibility' => 'Accessibility', 'best_practices' => 'Best Practices', 'seo' => 'SEO'] as $value => $label)
                <flux:button
                    wire:click="setFilter('{{ $value }}')"
                    variant="{{ $filter === $value ? 'filled' : 'ghost' }}"
                    size="sm"
                >{{ $label }}</flux:button>
            @endforeach
        </div>
    </div>

    @forelse ($grouped as $category => $items)
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-ink-400 mb-4">{{ str_replace('_', ' ', $category) }}</h2>
            <div class="space-y-6">
                @foreach ($items as $entry)
                    @php
                        $sevColor = match ($entry['descriptor']->severity) {
                            'critical' => 'accent',
                            'warning'  => 'amber',
                            default    => 'sky',
                        };
                        $sevFluxColor = match ($entry['descriptor']->severity) {
                            'critical' => 'rose',
                            'warning'  => 'amber',
                            default    => 'sky',
                        };
                    @endphp
                    <div id="{{ $entry['key'] }}" class="border-l-4 border-{{ $sevColor }}-500 pl-4 py-1">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-semibold text-base">{{ __($entry['descriptor']->titleKey) }}</h3>
                            <flux:badge color="{{ $sevFluxColor }}" size="sm">{{ $entry['descriptor']->severity }}</flux:badge>
                            <code class="text-[11px] text-ink-400 ml-auto">{{ $entry['key'] }}</code>
                        </div>
                        <p class="text-sm text-ink-500 dark:text-ink-400">{{ __($entry['descriptor']->descriptionKey) }}</p>

                        @if ($entry['docs'])
                            <div class="mt-3 flex items-start gap-2 text-sm">
                                <flux:icon.information-circle class="size-4 text-sky-500 shrink-0 mt-0.5" />
                                <p class="text-ink-700 dark:text-ink-300">{{ $entry['docs']['why'] }}</p>
                            </div>

                            @if (! empty($entry['docs']['impact']))
                                <div class="mt-2 flex items-center gap-2 text-xs">
                                    <flux:icon.bolt class="size-3.5 text-amber-500" />
                                    <span class="text-amber-700 dark:text-amber-400 font-medium">{{ $entry['docs']['impact'] }}</span>
                                </div>
                            @endif

                            @if (! empty($entry['docs']['docs']))
                                <div class="mt-3 flex flex-wrap gap-2">
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
                                <div class="mt-3 grid grid-cols-1 lg:grid-cols-2 gap-3">
                                    @if (! empty($entry['docs']['good']))
                                        <div class="rounded-2xl border border-emerald-200/60 dark:border-emerald-900/40 bg-emerald-50/40 dark:bg-emerald-900/10 overflow-hidden">
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 border-b border-emerald-200/60 dark:border-emerald-900/40">
                                                <flux:icon.check-circle class="size-3.5" />
                                                Recommended
                                            </div>
                                            <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-emerald-800 dark:text-emerald-200">{{ $entry['docs']['good'] }}</code></pre>
                                        </div>
                                    @endif
                                    @if (! empty($entry['docs']['bad']))
                                        <div class="rounded-2xl border border-accent-200/60 dark:border-accent-900/40 bg-accent-50/40 dark:bg-accent-900/10 overflow-hidden">
                                            <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-accent-700 dark:text-accent-300 border-b border-accent-200/60 dark:border-accent-900/40">
                                                <flux:icon.x-circle class="size-3.5" />
                                                Avoid
                                            </div>
                                            <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-accent-800 dark:text-accent-200">{{ $entry['docs']['bad'] }}</code></pre>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <p class="text-sm text-ink-500">No items match this filter.</p>
        </div>
    @endforelse
</div>
