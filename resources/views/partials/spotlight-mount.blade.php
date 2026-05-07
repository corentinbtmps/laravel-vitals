{{-- Cmd+K / Ctrl+K triggers (hidden, keyboard-only) --}}
<flux:modal.trigger name="vitals-spotlight" shortcut="meta.k">
    <span class="sr-only">Open Vitals Spotlight (Cmd+K)</span>
</flux:modal.trigger>
<flux:modal.trigger name="vitals-spotlight" shortcut="ctrl.k">
    <span class="sr-only">Open Vitals Spotlight (Ctrl+K)</span>
</flux:modal.trigger>

<flux:modal name="vitals-spotlight" variant="bare" class="!bg-transparent !p-0 !shadow-none !border-0 w-full max-w-2xl">
    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-white dark:bg-ink-900 shadow-xl overflow-hidden">
        <livewire:vitals::components.spotlight />
    </div>
</flux:modal>
