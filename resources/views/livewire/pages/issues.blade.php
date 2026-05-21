<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">{{ __('vitals::vitals.pages.issues.title') }}</h1>
        <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.pages.issues.subtitle') }}</p>
    </div>

    {{-- Tabs --}}
    <flux:button.group>
        <flux:button
            wire:click="setTab('top')"
            size="sm"
            variant="{{ $tab === 'top' ? 'primary' : 'ghost' }}"
        >{{ __('vitals::vitals.pages.issues.tab_top') }}</flux:button>
        <flux:button
            wire:click="setTab('all')"
            size="sm"
            variant="{{ $tab === 'all' ? 'primary' : 'ghost' }}"
        >{{ __('vitals::vitals.pages.issues.tab_all') }}</flux:button>
    </flux:button.group>

    {{-- Tab content --}}
    @if ($tab === 'top')
        <livewire:vitals::pages.insights :key="'insights-'.$tab" />
    @else
        <livewire:vitals::pages.recommendations-index :key="'recos-'.$tab" />
    @endif
</div>
