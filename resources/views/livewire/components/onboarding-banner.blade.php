<div class="rounded-2xl border border-accent-200/60 dark:border-accent-900/40 bg-gradient-to-br from-accent-50/50 to-paper dark:from-accent-950/20 dark:to-ink-900 p-6 mb-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <h2 class="text-base font-semibold text-ink-900 dark:text-ink-100">
                {{ __('vitals::vitals.onboarding.banner_title') }}
            </h2>
            <p class="text-sm text-ink-500 mt-1">
                {{ __('vitals::vitals.onboarding.banner_subtitle', ['count' => $completed, 'total' => $total]) }}
            </p>
        </div>
        <button
            wire:click="dismiss"
            type="button"
            class="text-xs text-ink-400 hover:text-ink-600 dark:hover:text-ink-300 transition-colors shrink-0"
            title="{{ __('vitals::vitals.onboarding.dismiss_confirm') }}"
        >
            {{ __('vitals::vitals.onboarding.dismiss') }}
        </button>
    </div>

    {{-- Progress bar --}}
    <div class="h-1 rounded-full bg-ink-200/60 dark:bg-ink-800/60 overflow-hidden mb-4">
        <div class="h-full bg-accent-500 transition-all duration-500" style="width: {{ $percentage }}%;"></div>
    </div>

    {{-- Steps list --}}
    <div class="space-y-2">
        @foreach ($steps as $step)
            @php $done = $step->complete(); @endphp
            <div class="flex items-center gap-3 py-1">
                <div class="flex h-5 w-5 items-center justify-center rounded-full {{ $done ? 'bg-emerald-500' : 'bg-ink-200 dark:bg-ink-800' }} shrink-0">
                    @if ($done)
                        <flux:icon.check class="size-3 text-white" />
                    @endif
                </div>
                <span class="flex-1 text-sm {{ $done ? 'text-ink-400 line-through' : 'text-ink-800 dark:text-ink-200' }}">
                    {{ $step->title }}
                </span>
                @if (! $done && $step === $nextStep)
                    <flux:button href="{{ $step->link }}" size="sm" variant="filled" color="accent">
                        {{ $step->cta }}
                    </flux:button>
                @endif
            </div>
        @endforeach
    </div>
</div>
