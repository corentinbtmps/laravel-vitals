<div class="rounded border bg-zinc-50 p-3 text-sm font-mono dark:bg-zinc-900 dark:border-zinc-800">
    <div class="flex items-center justify-between mb-2">
        <span class="text-zinc-700 dark:text-zinc-300">
            {{ $ref['file'] }}<span class="text-zinc-500">:{{ $ref['line_start'] }}</span>
        </span>
        @php $template = config('vitals.ui.editor_url_template'); @endphp
        @if (is_string($template) && $template !== '')
            @php
                $editor = str_replace(['{path}', '{line}'], [base_path($ref['file']), (string) $ref['line_start']], $template);
            @endphp
            <a href="{{ $editor }}" class="text-blue-500 hover:underline text-xs">Open</a>
        @endif
    </div>
    <pre class="whitespace-pre-wrap text-xs leading-snug">{{ $ref['snippet'] }}</pre>
    @if (! empty($ref['hint']))
        <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-400">
            <strong>Hint:</strong> {{ $ref['hint'] }}
        </div>
    @endif
</div>
