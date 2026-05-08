@php
    use Illuminate\Support\Str;
    $groups = ['urls' => 'URLs', 'audits' => 'Audits', 'recommendations' => 'Recommendations', 'learn' => 'Learn'];
    $totalShown = 0;
    foreach ($groups as $type => $label) {
        $totalShown += count($resultsByType[$type] ?? []);
    }
@endphp

<div
    x-data="{
        focusedIndex: 0,
        totalResults: {{ $totalShown }},
        navigate(delta) {
            if (this.totalResults === 0) return;
            this.focusedIndex = (this.focusedIndex + delta + this.totalResults) % this.totalResults;
            this.$nextTick(() => {
                const el = this.$root.querySelectorAll('[data-spotlight-result]')[this.focusedIndex];
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },
        open() {
            const el = this.$root.querySelectorAll('[data-spotlight-result]')[this.focusedIndex];
            if (el && el.href) window.location.href = el.href;
        },
    }"
    @keydown.arrow-down.prevent="navigate(1)"
    @keydown.arrow-up.prevent="navigate(-1)"
    @keydown.enter.prevent="open()"
    class="w-full max-w-2xl"
>
    {{-- Search input --}}
    <div class="border-b border-ink-200/60 dark:border-ink-800/60 px-4 py-3">
        <div class="flex items-center gap-3">
            <flux:icon.magnifying-glass class="size-5 text-ink-400 shrink-0" />
            <input
                wire:model.live.debounce.150ms="query"
                type="text"
                autofocus
                placeholder="{{ __('vitals::vitals.spotlight.placeholder') }}"
                class="flex-1 bg-transparent border-0 outline-none text-base text-ink-900 dark:text-ink-100 placeholder:text-ink-400"
            >
            <kbd class="hidden md:inline-block text-[10px] font-mono px-1.5 py-0.5 rounded border border-ink-200/60 dark:border-ink-800/60 text-ink-400">Esc</kbd>
        </div>
    </div>

    {{-- Results --}}
    @php $index = 0; @endphp
    <div class="max-h-96 overflow-y-auto py-2">
        @forelse ($groups as $type => $groupLabel)
            @if (! empty($resultsByType[$type] ?? []))
                <div class="px-2 pt-2">
                    <div class="px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em] text-ink-400">
                        {{ __('vitals::vitals.spotlight.group_' . $type) }}
                    </div>
                    @foreach ($resultsByType[$type] as $result)
                        <a
                            href="{{ $result->url }}"
                            data-spotlight-result
                            data-index="{{ $index }}"
                            class="flex items-center gap-3 rounded-md px-2 py-2 text-sm hover:bg-ink-100 dark:hover:bg-ink-800 transition-colors"
                            x-bind:class="focusedIndex === {{ $index }} ? 'bg-ink-100 dark:bg-ink-800' : ''"
                        >
                            <span class="flex-1 truncate text-ink-800 dark:text-ink-200">{{ $result->title }}</span>
                            <flux:icon.arrow-up-right class="size-3.5 text-ink-400 shrink-0" />
                        </a>
                        @php $index++; @endphp
                    @endforeach
                </div>
            @endif
        @empty
        @endforelse

        @if ($totalShown === 0 && mb_strlen(trim($query)) >= 2)
            <div class="px-4 py-8 text-center text-sm text-ink-500">
                {{ __('vitals::vitals.spotlight.empty') }}
            </div>
        @elseif (mb_strlen(trim($query)) < 2)
            <div class="px-4 py-8 text-center text-sm text-ink-500">
                {{ __('vitals::vitals.spotlight.hint') }}
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="border-t border-ink-200/60 dark:border-ink-800/60 px-4 py-2 flex items-center justify-between text-[10px] text-ink-400">
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-1">
                <kbd class="font-mono px-1 py-0.5 rounded border border-ink-200/60 dark:border-ink-800/60">↑↓</kbd>
                {{ __('vitals::vitals.spotlight.kbd_navigate') }}
            </span>
            <span class="flex items-center gap-1">
                <kbd class="font-mono px-1 py-0.5 rounded border border-ink-200/60 dark:border-ink-800/60">↵</kbd>
                {{ __('vitals::vitals.spotlight.kbd_open') }}
            </span>
        </div>
        <span>{{ $totalShown }} {{ Str::plural('result', $totalShown) }}</span>
    </div>
</div>
