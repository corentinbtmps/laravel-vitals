<div class="space-y-8">
    {{-- Header --}}
    <div class="text-center space-y-3">
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-12 mx-auto mb-4">
        @else
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-accent-400 to-accent-600 shadow-sm mx-auto mb-4">
                <svg viewBox="0 0 64 64" class="h-7 w-7" fill="none">
                    <path d="M8 34 H20 L24 24 L28 42 L32 16 L36 42 L40 24 L44 34 H56"
                          stroke="white" stroke-width="4"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        @endif
        <h1 class="text-2xl font-bold tracking-tight">{{ $appName }}</h1>
        <p class="text-ink-500 text-sm">{{ $description }}</p>
    </div>

    {{-- Uptime card --}}
    <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6 text-center">
        <div @class([
            'text-5xl font-bold tabular-nums',
            'text-emerald-500' => $uptime >= 99,
            'text-amber-500'   => $uptime >= 95 && $uptime < 99,
            'text-accent-500'  => $uptime < 95,
        ])>
            {{ number_format($uptime, 2) }}%
        </div>
        <div class="text-sm text-ink-500 mt-2">{{ __('vitals::vitals.status.uptime_30d') }}</div>
        @if ($uptime >= 99)
            <div class="mt-3 inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 text-sm font-medium">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ __('vitals::vitals.status.operational') }}
            </div>
        @elseif ($uptime >= 95)
            <div class="mt-3 inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400 text-sm font-medium">
                <span class="size-2 rounded-full bg-amber-500"></span>
                {{ __('vitals::vitals.status.degraded') }}
            </div>
        @else
            <div class="mt-3 inline-flex items-center gap-1.5 text-accent-600 dark:text-accent-400 text-sm font-medium">
                <span class="size-2 rounded-full bg-accent-500"></span>
                {{ __('vitals::vitals.status.disrupted') }}
            </div>
        @endif
    </div>

    {{-- CWV split --}}
    @if ($cwvSplit['total'] > 0)
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <h2 class="text-base font-semibold mb-4">{{ __('vitals::vitals.status.cwv_last_7d') }}</h2>
            @php
                $total = $cwvSplit['total'];
                $goodPct  = round($cwvSplit['good'] / $total * 100);
                $needsPct = round($cwvSplit['needs_improvement'] / $total * 100);
                $poorPct  = 100 - $goodPct - $needsPct;
            @endphp
            <div class="flex rounded-full overflow-hidden h-4 mb-4">
                @if ($goodPct > 0)
                    <div class="bg-emerald-400" style="width: {{ $goodPct }}%"></div>
                @endif
                @if ($needsPct > 0)
                    <div class="bg-amber-400" style="width: {{ $needsPct }}%"></div>
                @endif
                @if ($poorPct > 0)
                    <div class="bg-accent-400" style="width: {{ $poorPct }}%"></div>
                @endif
            </div>
            <div class="flex justify-between text-xs text-ink-500">
                <span class="text-emerald-600 dark:text-emerald-400">{{ $goodPct }}% {{ __('vitals::vitals.status.good') }}</span>
                <span class="text-amber-600 dark:text-amber-400">{{ $needsPct }}% {{ __('vitals::vitals.status.needs_improvement') }}</span>
                <span class="text-accent-600 dark:text-accent-400">{{ $poorPct }}% {{ __('vitals::vitals.status.poor') }}</span>
            </div>
        </div>
    @endif

    {{-- Incidents --}}
    @if ($incidents->isNotEmpty())
        <div class="rounded-2xl border border-amber-200/60 dark:border-amber-800/40 bg-paper dark:bg-ink-900 p-6">
            <h2 class="text-base font-semibold mb-4">{{ __('vitals::vitals.status.recent_incidents') }}</h2>
            <ul class="space-y-3">
                @foreach ($incidents as $inc)
                    <li class="flex items-center gap-3 text-sm">
                        <span class="size-2 rounded-full bg-amber-400 shrink-0"></span>
                        <span class="font-medium">{{ $inc->url?->label }}</span>
                        <span class="text-ink-500 text-xs">{{ __('vitals::vitals.status.score') }}: {{ $inc->score_performance }}</span>
                        <span class="text-ink-400 text-xs ml-auto">{{ $inc->completed_at?->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 p-6">
            <div class="text-center py-4">
                <p class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">{{ __('vitals::vitals.status.no_incidents') }}</p>
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <div class="text-center text-xs text-ink-400">
        {{ __('vitals::vitals.status.updated_at', ['time' => $updatedAt->toRfc1123String()]) }}
        · {{ __('vitals::vitals.status.powered_by') }}
        <a href="https://github.com/corentinbtmps/laravel-vitals" class="hover:underline text-ink-500" target="_blank" rel="noopener noreferrer">Laravel Vitals</a>
    </div>
</div>
