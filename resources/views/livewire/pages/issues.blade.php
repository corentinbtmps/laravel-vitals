<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">{{ __('vitals::vitals.pages.issues.title') }}</h1>
        <p class="text-sm text-ink-500 mt-1">{{ __('vitals::vitals.pages.issues.subtitle') }}</p>
    </div>

    {{-- Tabs --}}
    <flux:radio.group wire:model.live="tab" variant="segmented" size="sm">
        <flux:radio value="top" :label="__('vitals::vitals.pages.issues.tab_top')" />
        <flux:radio value="all" :label="__('vitals::vitals.pages.issues.tab_all')" />
    </flux:radio.group>

    {{-- Tab content --}}
    @if ($tab === 'top')
        <livewire:vitals::pages.insights :key="'insights-'.$tab" />
    @else
        <livewire:vitals::pages.recommendations-index :key="'recos-'.$tab" />
    @endif
</div>
