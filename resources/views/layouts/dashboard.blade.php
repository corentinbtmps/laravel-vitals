<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laravel Vitals' }}</title>
    <link rel="stylesheet" href="{{ route('vitals.assets', 'dashboard.css') }}">
    <script defer src="{{ route('vitals.assets', 'dashboard.js') }}"></script>
    @livewireStyles
    @fluxAppearance
</head>
<body class="h-full bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100" data-flux-appearance>

<flux:header class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
    <flux:brand href="{{ route('vitals.dashboard') }}" name="Laravel Vitals" class="text-rose-500">
        <flux:icon.heart class="text-rose-500 size-5" />
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
        x-on:click="document.documentElement.classList.toggle('dark')"
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
