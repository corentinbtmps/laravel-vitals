<div class="rounded-lg border border-ink-200 bg-canvas dark:bg-ink-950 dark:border-ink-800 p-3 text-sm font-mono">
    <div class="flex items-center justify-between mb-2">
        <span class="text-ink-700 dark:text-ink-300">
            {{ $ref['file'] }}<span class="text-ink-500">:{{ $ref['line_start'] }}</span>
        </span>
        @php $template = config('vitals.ui.editor_url_template'); @endphp
        @if (is_string($template) && $template !== '')
            @php
                $editor = str_replace(['{path}', '{line}'], [base_path($ref['file']), (string) $ref['line_start']], $template);
            @endphp
            <a href="{{ $editor }}" class="text-accent-500 hover:underline text-xs">{{ __('vitals::vitals.actions.open') }}</a>
        @endif
    </div>
    <pre class="whitespace-pre-wrap text-xs leading-snug text-ink-800 dark:text-ink-200">{{ $ref['snippet'] }}</pre>
    @if (! empty($ref['hint']))
        <div class="mt-2 text-xs text-ink-600 dark:text-ink-400">
            <strong>{{ __('vitals::vitals.audit_detail.hint') }}:</strong> {{ $ref['hint'] }}
        </div>
    @endif
</div>
