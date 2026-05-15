<div class="space-y-6">
    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('vitals.issues') }}">{{ __('vitals::vitals.pages.issues.title') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ __($descriptor->titleKey) }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span @class([
                    'size-2.5 rounded-full shrink-0',
                    $descriptor->severity->dotBackground(),
                ])></span>
                <h1 class="text-2xl font-semibold">{{ __($descriptor->titleKey) }}</h1>
                <flux:badge color="{{ $descriptor->severity->fluxBadgeColor() }}">{{ $descriptor->severity->label() }}</flux:badge>
            </div>
            <p class="text-sm text-ink-500 mt-1">{{ __($descriptor->descriptionKey) }}</p>
            <code class="inline-block mt-2 text-[11px] font-mono text-ink-400 bg-ink-100 dark:bg-ink-800 px-2 py-0.5 rounded">{{ $auditKey }}</code>
        </div>
        <div class="text-right shrink-0">
            <div class="text-3xl font-bold tabular-nums text-ink-900 dark:text-ink-100">{{ $occurrenceCount }}</div>
            <div class="text-xs text-ink-500 mt-0.5">{{ __('vitals::vitals.issue_detail.occurrences_label') }}</div>
        </div>
    </div>

    @if ($docs)
        {{-- Why it matters --}}
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-5">
            <div class="flex items-start gap-2">
                <flux:icon.information-circle class="size-4 text-sky-500 shrink-0 mt-0.5" />
                <p class="text-sm text-ink-700 dark:text-ink-300">{{ $docs['why'] }}</p>
            </div>
            @if (! empty($docs['impact']))
                <div class="mt-3 flex items-center gap-2 text-xs pt-3 border-t border-ink-200 dark:border-ink-800">
                    <flux:icon.bolt class="size-3.5 text-amber-500" />
                    <span class="text-amber-700 dark:text-amber-400 font-medium">{{ $docs['impact'] }}</span>
                </div>
            @endif
            @if (! empty($docs['docs']))
                <div class="mt-3 flex flex-wrap gap-2 pt-3 border-t border-ink-200 dark:border-ink-800">
                    @foreach ($docs['docs'] as $doc)
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
        </div>
    @endif

    {{-- Where this happens --}}
    @if (! empty($grouped))
        <div>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon name="map-pin" class="size-5 text-accent-500" />
                <h2 class="text-base font-semibold">{{ __('vitals::vitals.issue_detail.where_this_happens') }}</h2>
                <flux:badge color="amber" size="sm">{{ $occurrenceCount }}</flux:badge>
            </div>

            <div class="space-y-4">
                @foreach ($grouped as $urlPath => $group)
                    <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 overflow-hidden">
                        {{-- URL header — tinted strip to visually separate from the card body --}}
                        <div class="flex items-center justify-between px-5 py-3 border-b border-ink-200 dark:border-ink-800 bg-ink-100 dark:bg-ink-800">
                            <div class="flex items-center gap-2 min-w-0">
                                <flux:icon.link class="size-4 text-accent-500 shrink-0" />
                                <span class="font-semibold text-ink-900 dark:text-ink-100 truncate">{{ $group['url_label'] }}</span>
                                <code class="text-[11px] text-ink-500 font-mono truncate hidden sm:inline">{{ $group['url_path'] }}</code>
                            </div>
                            @if ($group['url_id'] !== null)
                                <flux:button href="{{ route('vitals.url', $group['url_id']) }}" variant="ghost" size="sm" icon="arrow-right">{{ __('vitals::vitals.actions.view_url') }}</flux:button>
                            @endif
                        </div>

                        {{-- Each audit occurrence (lighter dividers — borders should feel subtle inside a card) --}}
                        <div class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($group['occurrences'] as $occ)
                                <div class="p-4 space-y-3">
                                    <div class="flex items-center justify-between gap-3 flex-wrap">
                                        <div class="flex items-center gap-2 text-xs text-ink-500">
                                            <flux:icon.clock class="size-3.5" />
                                            <span>{{ $occ['audit_date'] ?? __('vitals::vitals.issue_detail.unknown_date') }}</span>
                                        </div>
                                        @if ($occ['audit_id'])
                                            <flux:button href="{{ route('vitals.audit', $occ['audit_id']) }}" variant="ghost" size="sm" icon="arrow-right">{{ __('vitals::vitals.actions.view_audit') }}</flux:button>
                                        @endif
                                    </div>

                                    {{-- Code references --}}
                                    @if (! empty($occ['code_references']))
                                        <div>
                                            <div class="flex items-center gap-1.5 text-xs font-medium text-ink-500 dark:text-ink-400 mb-2">
                                                <flux:icon name="code-bracket" class="size-3.5" />
                                                {{ __('vitals::vitals.audit_detail.found_in_app') }}
                                            </div>
                                            <div class="space-y-2">
                                                @foreach ($occ['code_references'] as $ref)
                                                    <x-vitals::code-reference :ref="$ref" />
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Per-occurrence detail items (What to fix) --}}
                                    @if (! empty($occ['detail_items']))
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-500 mb-2">
                                                {{ __('vitals::vitals.issue_detail.what_to_fix') }}
                                            </p>
                                            <ul class="space-y-2">
                                                @foreach ($occ['detail_items'] as $item)
                                                    <li class="flex items-start justify-between gap-3 rounded-lg border border-ink-200 dark:border-ink-800 bg-canvas dark:bg-ink-950 p-3">
                                                        <div class="min-w-0 flex-1">
                                                            <code class="text-xs font-mono text-ink-700 dark:text-ink-300 break-all">{{ $item['url'] ?? '' }}</code>
                                                            @if (! empty($item['hint']))
                                                                <p class="mt-1 text-xs text-ink-500">{{ $item['hint'] }}</p>
                                                            @endif
                                                        </div>
                                                        @if (! empty($item['wasted_label']))
                                                            <flux:badge color="amber" size="sm">{{ $item['wasted_label'] }}</flux:badge>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    {{-- N+1 query patterns --}}
                                    @if (! empty($occ['top_patterns']))
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-ink-500 mb-2">{{ __('vitals::vitals.audit_detail.repeated_queries') }}</p>
                                            <ul class="space-y-2">
                                                @foreach ($occ['top_patterns'] as $pattern)
                                                    <li class="rounded-lg border border-ink-200 dark:border-ink-800 bg-canvas dark:bg-ink-950 p-3">
                                                        <code class="text-xs font-mono text-accent-700 dark:text-accent-300 break-all">{{ $pattern['sql'] }}</code>
                                                        <div class="mt-1 flex items-center gap-3 text-xs text-ink-500">
                                                            <span class="tabular-nums font-medium">×{{ $pattern['occurrences'] }}</span>
                                                            @if ($pattern['caller'])
                                                                <code class="text-[11px]">{{ $pattern['caller'] }}</code>
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 p-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30 mb-4">
                <flux:icon.check-circle class="size-6 text-emerald-600 dark:text-emerald-400" />
            </div>
            <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100">{{ __('vitals::vitals.issue_detail.no_occurrences_title') }}</h3>
            <p class="mt-2 text-sm text-ink-500 max-w-md mx-auto">{{ __('vitals::vitals.issue_detail.no_occurrences_body') }}</p>
        </div>
    @endif

    {{-- Good / bad code blocks from docs --}}
    @if ($docs && (! empty($docs['good']) || ! empty($docs['bad'])))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            @if (! empty($docs['good']))
                <div class="rounded-2xl border border-emerald-200/60 dark:border-emerald-900/40 bg-emerald-50/40 dark:bg-emerald-900/10 overflow-hidden">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 border-b border-emerald-200/60 dark:border-emerald-900/40">
                        <flux:icon.check-circle class="size-3.5" />
                        {{ __('vitals::vitals.audit_detail.recommended') }}
                    </div>
                    <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-emerald-800 dark:text-emerald-200">{{ $docs['good'] }}</code></pre>
                </div>
            @endif
            @if (! empty($docs['bad']))
                <div class="rounded-2xl border border-accent-200/60 dark:border-accent-900/40 bg-accent-50/40 dark:bg-accent-900/10 overflow-hidden">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-accent-700 dark:text-accent-300 border-b border-accent-200/60 dark:border-accent-900/40">
                        <flux:icon.x-circle class="size-3.5" />
                        {{ __('vitals::vitals.audit_detail.avoid') }}
                    </div>
                    <pre class="p-3 text-[11px] leading-snug overflow-x-auto"><code class="text-accent-800 dark:text-accent-200">{{ $docs['bad'] }}</code></pre>
                </div>
            @endif
        </div>
    @endif
</div>
