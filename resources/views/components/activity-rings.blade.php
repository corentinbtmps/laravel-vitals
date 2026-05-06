@props(['scores' => []])
@php
    $rings = [
        ['key' => 'performance',   'label' => 'Performance',   'color' => '#f43f5e', 'r' => 56],
        ['key' => 'accessibility', 'label' => 'Accessibility', 'color' => '#10b981', 'r' => 44],
        ['key' => 'seo',           'label' => 'SEO',           'color' => '#0ea5e9', 'r' => 32],
    ];
@endphp
<div class="relative flex h-40 w-40 items-center justify-center">
    <svg viewBox="0 0 128 128" class="h-full w-full -rotate-90">
        @foreach ($rings as $ring)
            @php
                $score = max(0, min(100, (int) ($scores[$ring['key']] ?? 0)));
                $circumference = 2 * M_PI * $ring['r'];
                $dash = round(($score / 100) * $circumference, 2);
            @endphp
            <circle cx="64" cy="64" r="{{ $ring['r'] }}"
                    fill="none" stroke="currentColor"
                    class="text-zinc-100 dark:text-zinc-800"
                    stroke-width="8"/>
            <circle cx="64" cy="64" r="{{ $ring['r'] }}"
                    fill="none" stroke="{{ $ring['color'] }}"
                    stroke-width="8" stroke-linecap="round"
                    stroke-dasharray="{{ $dash }} {{ round($circumference, 2) }}"/>
        @endforeach
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        {{ $slot }}
    </div>
</div>
