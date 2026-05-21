<div class="rounded-lg border border-ink-200 bg-canvas dark:bg-ink-950 dark:border-ink-800 p-3 text-sm font-mono">
    <div class="mb-2">
        @php $editor = \LaravelVitals\Support\EditorUrl::for($ref['file'], $ref['line_start']); @endphp
        @if ($editor)
            <flux:link href="{{ $editor }}" class="inline-flex items-center gap-1.5" :tooltip="__('vitals::vitals.actions.open_in_editor')">
                <span>{{ $ref['file'] }}<span>:{{ $ref['line_start'] }}</span></span>
                <flux:icon name="arrow-top-right-on-square" class="size-3" />
            </flux:link>
        @else
            <span class="text-ink-700 dark:text-ink-300">
                {{ $ref['file'] }}<span class="text-ink-500">:{{ $ref['line_start'] }}</span>
            </span>
        @endif
    </div>
    <pre class="whitespace-pre-wrap text-xs leading-snug text-ink-800 dark:text-ink-200">{{ $ref['snippet'] }}</pre>
    @if (! empty($ref['hint']))
        <div class="mt-2 text-xs text-ink-600 dark:text-ink-400">
            <strong>{{ __('vitals::vitals.audit_detail.hint') }}:</strong> {{ $ref['hint'] }}
        </div>
    @endif
</div>
