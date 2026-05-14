<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">{{ __('vitals::vitals.pages.issues.title') }}</h1>
        <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.pages.issues.subtitle') }}</p>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-1 border-b border-ink-200/60 dark:border-ink-800/60">
        <button
            wire:click="setTab('top')"
            @class([
                'px-4 py-2 -mb-px text-sm font-medium border-b-2 transition-colors',
                'border-accent-500 text-accent-600 dark:text-accent-400'               => $tab === 'top',
                'border-transparent text-ink-500 hover:text-ink-700 dark:hover:text-ink-300' => $tab !== 'top',
            ])
        >{{ __('vitals::vitals.pages.issues.tab_top') }}</button>

        <button
            wire:click="setTab('all')"
            @class([
                'px-4 py-2 -mb-px text-sm font-medium border-b-2 transition-colors',
                'border-accent-500 text-accent-600 dark:text-accent-400'               => $tab === 'all',
                'border-transparent text-ink-500 hover:text-ink-700 dark:hover:text-ink-300' => $tab !== 'all',
            ])
        >{{ __('vitals::vitals.pages.issues.tab_all') }}</button>
    </div>

    {{-- Tab content --}}
    @if ($tab === 'top')
        <livewire:vitals::pages.insights :key="'insights-'.$tab" />
    @else
        <livewire:vitals::pages.recommendations-index :key="'recos-'.$tab" />
    @endif
</div>
