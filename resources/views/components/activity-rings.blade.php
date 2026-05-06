@props(['scores' => []])
@php
    $rings = [
        ['key' => 'performance',   'label' => 'Performance',   'color' => '#f43f5e', 'r' => 58],
        ['key' => 'accessibility', 'label' => 'Accessibility', 'color' => '#10b981', 'r' => 42],
        ['key' => 'seo',           'label' => 'SEO',           'color' => '#0ea5e9', 'r' => 26],
    ];
@endphp
<div class="relative flex h-40 w-40 items-center justify-center" {{ $attributes }}>
    <svg viewBox="0 0 128 128" class="h-full w-full -rotate-90" aria-label="Audit scores">
        @foreach ($rings as $ring)
            @php
                $rawScore = $scores[$ring['key']] ?? null;
                $hasScore = $rawScore !== null;
                $score = $hasScore ? max(0, min(100, (int) $rawScore)) : 0;
            @endphp
            {{-- Track --}}
            <circle cx="64" cy="64" r="{{ $ring['r'] }}"
                    fill="none" stroke="currentColor"
                    class="text-ink-100 dark:text-ink-700"
                    stroke-width="10" pathLength="100"/>
            {{-- Filled arc — only when we have a non-zero score --}}
            @if ($hasScore && $score > 0)
                <circle cx="64" cy="64" r="{{ $ring['r'] }}"
                        fill="none" stroke="{{ $ring['color'] }}"
                        stroke-width="10" stroke-linecap="round"
                        pathLength="100"
                        stroke-dasharray="{{ $score }} 100"
                        class="vitals-ring"
                        style="--ring-target: {{ $score }};">
                    <title>{{ $ring['label'] }} — {{ $score }}/100</title>
                </circle>
            @else
                {{-- Transparent circle to provide hover title even with no score --}}
                <circle cx="64" cy="64" r="{{ $ring['r'] }}" fill="none" stroke="transparent" stroke-width="10">
                    <title>{{ $ring['label'] }} — no data</title>
                </circle>
            @endif
        @endforeach
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
        {{ $slot }}
    </div>
</div>
