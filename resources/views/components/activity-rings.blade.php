@props(['scores' => []])
@php
    $rings = [
        ['key' => 'performance',   'label' => 'Performance',   'color' => 'oklch(64% 0.220 12)',  'r' => 44],
        ['key' => 'accessibility', 'label' => 'Accessibility', 'color' => '#10b981', 'r' => 34],
        ['key' => 'seo',           'label' => 'SEO',           'color' => '#0ea5e9', 'r' => 24],
    ];
@endphp
<div class="relative flex h-28 w-28 items-center justify-center shrink-0">
    <svg viewBox="0 0 100 100" class="h-full w-full -rotate-90">
        @foreach ($rings as $ring)
            @php
                $score = max(0, min(100, (int) ($scores[$ring['key']] ?? 0)));
                $circumference = 2 * M_PI * $ring['r'];
                $dash = round(($score / 100) * $circumference, 2);
            @endphp
            {{-- Track --}}
            <circle cx="50" cy="50" r="{{ $ring['r'] }}"
                    fill="none" stroke="currentColor"
                    class="text-ink-100 dark:text-ink-800"
                    stroke-width="6"/>
            {{-- Progress --}}
            <circle cx="50" cy="50" r="{{ $ring['r'] }}"
                    fill="none" stroke="{{ $ring['color'] }}"
                    stroke-width="6" stroke-linecap="round"
                    stroke-dasharray="{{ $dash }} {{ round($circumference, 2) }}"/>
        @endforeach
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        {{ $slot }}
    </div>
</div>
