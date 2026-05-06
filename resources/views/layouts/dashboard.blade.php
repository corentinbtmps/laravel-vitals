<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laravel Vitals' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ route('vitals.favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ route('vitals.favicon.ico') }}">
    <link rel="stylesheet" href="{{ route('vitals.assets', 'dashboard.css') }}">
    <script defer src="{{ route('vitals.assets', 'dashboard.js') }}"></script>
    @livewireStyles
    @fluxAppearance
    <style>
        [data-flux-navbar-items][data-current] {
            color: rgb(244 63 94) !important;
        }
        [data-flux-navbar-items][data-current]::after {
            background-color: rgb(244 63 94) !important;
        }
    </style>
</head>
<body class="h-full bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100" data-flux-appearance>

<flux:header class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
    <flux:brand href="{{ route('vitals.dashboard') }}">
        <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-rose-400 to-rose-600 shadow-sm">
                <svg viewBox="0 0 64 64" class="h-5 w-5" fill="none">
                    <path d="M8 34 H20 L24 24 L32 46 L38 18 L42 34 H56"
                          stroke="white" stroke-width="4"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Laravel Vitals</span>
        </div>
    </flux:brand>

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item
            href="{{ route('vitals.dashboard') }}"
            icon="squares-2x2"
            :current="request()->routeIs('vitals.dashboard')"
        >Overview</flux:navbar.item>

        @if(Route::has('vitals.urls'))
        <flux:navbar.item
            href="{{ route('vitals.urls') }}"
            icon="link"
            :current="request()->routeIs('vitals.url*')"
        >URLs</flux:navbar.item>
        @endif

        @if(Route::has('vitals.recommendations'))
        <flux:navbar.item
            href="{{ route('vitals.recommendations') }}"
            icon="light-bulb"
            :current="request()->routeIs('vitals.recommendations')"
        >Recommendations</flux:navbar.item>
        @endif

        @if(Route::has('vitals.insights'))
        <flux:navbar.item
            href="{{ route('vitals.insights') }}"
            icon="sparkles"
            :current="request()->routeIs('vitals.insights')"
        >Insights</flux:navbar.item>
        @endif

        @if(Route::has('vitals.learn'))
        <flux:navbar.item
            href="{{ route('vitals.learn') }}"
            icon="book-open"
            :current="request()->routeIs('vitals.learn')"
        >Learn</flux:navbar.item>
        @endif

        @if(Route::has('vitals.budgets'))
        <flux:navbar.item
            href="{{ route('vitals.budgets') }}"
            icon="chart-bar"
            :current="request()->routeIs('vitals.budgets')"
        >Budgets</flux:navbar.item>
        @endif
    </flux:navbar>

    <flux:spacer />

    <flux:button
        variant="ghost"
        icon="moon"
        x-data
        x-on:click="$flux.dark = ! $flux.dark"
        tooltip="Toggle theme"
    />
</flux:header>

<flux:main container>
    {{ $slot ?? '' }}
</flux:main>

@livewireScripts
@fluxScripts
</body>
</html>
