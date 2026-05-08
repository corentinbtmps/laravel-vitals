<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laravel Vitals' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ route('vitals.favicon.svg') }}">
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
        /* Responsive helpers that bypass a Tailwind/Chrome parser issue
           where the first rule of compiled @media blocks is silently dropped. */
        .vitals-mobile-only { display: none; }
        .vitals-desktop-only { display: none; }
        @media (max-width: 1023.98px) {
            .vitals-padding { padding: 0 !important; }
            .vitals-mobile-only { display: flex; }
        }
        @media (min-width: 1024px) {
            .vitals-padding { padding: 0 !important; }
            .vitals-desktop-only { display: inline-flex; }
        }
    </style>
</head>
<body class="h-full bg-ink-50 text-ink-900 dark:bg-ink-950 dark:text-ink-100" data-flux-appearance>

<flux:header class="border-b border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900">
    <flux:brand href="{{ route('vitals.dashboard') }}">
        <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-accent-400 to-accent-600 shadow-sm">
                <svg viewBox="0 0 64 64" class="h-5 w-5" fill="none">
                    <path d="M8 34 H20 L24 24 L32 46 L38 18 L42 34 H56"
                          stroke="white" stroke-width="4"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="text-base font-semibold text-ink-900 dark:text-ink-100">Laravel Vitals</span>
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

    {{-- Spotlight trigger — full button with kbd hint (desktop only) --}}
    <button
        type="button"
        x-data
        x-on:click="$dispatch('modal-show', { name: 'vitals-spotlight' })"
        class="vitals-desktop-only items-center gap-2 rounded-lg border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900 hover:bg-ink-50 dark:hover:bg-ink-800/50 px-3 py-1.5 text-sm text-ink-500 hover:text-ink-700 dark:hover:text-ink-300 transition-colors"
        aria-label="{{ __('vitals::vitals.spotlight.button_label') }}"
    >
        <flux:icon.magnifying-glass class="size-4 shrink-0" />
        <span class="text-left">{{ __('vitals::vitals.spotlight.button_label') }}</span>
        <kbd class="inline-flex items-center gap-0.5 rounded border border-ink-200/60 dark:border-ink-700 bg-ink-50 dark:bg-ink-800 px-1.5 py-0.5 text-xs font-mono text-ink-500" x-data x-init="$el.firstElementChild.textContent = navigator.platform.toLowerCase().includes('mac') ? '⌘' : 'Ctrl'">
            <span></span>K
        </kbd>
    </button>

    <flux:spacer />

    <flux:button
        variant="ghost"
        icon="moon"
        x-data
        x-on:click="$flux.dark = ! $flux.dark"
        tooltip="Toggle theme"
    />

    {{-- Mobile: search icon + burger menu (shown on max-lg screens) --}}
    <div class="vitals-mobile-only items-center gap-1" x-data="{ open: false }" @resize.window="if (window.innerWidth >= 1024) open = false">
        {{-- Mobile search icon button — opens the same Spotlight modal --}}
        <button
            type="button"
            x-on:click="$dispatch('modal-show', { name: 'vitals-spotlight' })"
            class="flex h-9 w-9 items-center justify-center rounded-lg text-ink-500 hover:bg-ink-100 dark:hover:bg-ink-800 hover:text-ink-900 dark:hover:text-ink-100 transition-colors"
            aria-label="{{ __('vitals::vitals.spotlight.button_label') }}"
        >
            <flux:icon.magnifying-glass class="size-5" />
        </button>

        <button
            type="button"
            x-on:click="open = !open"
            class="flex h-9 w-9 items-center justify-center rounded-lg text-ink-500 hover:bg-ink-100 dark:hover:bg-ink-800 hover:text-ink-900 dark:hover:text-ink-100 transition-colors"
            aria-label="Open navigation"
        >
            <flux:icon.bars-3 x-show="!open" class="size-5" />
            <flux:icon.x-mark x-show="open" class="size-5" x-cloak />
        </button>

        {{-- Backdrop --}}
        <div
            x-show="open"
            x-cloak
            x-on:click="open = false"
            class="fixed inset-0 z-40 bg-ink-950/40 backdrop-blur-sm lg:hidden"
            x-transition.opacity
        ></div>

        {{-- Drawer (slides from right) --}}
        <nav
            x-show="open"
            x-cloak
            class="fixed inset-y-0 right-0 z-50 w-72 max-w-[85vw] bg-paper dark:bg-ink-900 border-l border-ink-200/60 dark:border-ink-800/60 shadow-2xl px-4 py-6 overflow-y-auto lg:hidden"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="flex items-center justify-between mb-6 px-2">
                <span class="text-base font-semibold text-ink-900 dark:text-ink-100">Menu</span>
                <button x-on:click="open = false" class="text-ink-400 hover:text-ink-600 dark:hover:text-ink-300 transition-colors">
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>
            <div class="flex flex-col gap-1">
                <a href="{{ route('vitals.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vitals.dashboard') ? 'bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300' : 'text-ink-700 dark:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800' }}">
                    <flux:icon.squares-2x2 class="size-4" />Overview
                </a>
                @if(Route::has('vitals.urls'))
                <a href="{{ route('vitals.urls') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vitals.url*') ? 'bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300' : 'text-ink-700 dark:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800' }}">
                    <flux:icon.link class="size-4" />URLs
                </a>
                @endif
                @if(Route::has('vitals.recommendations'))
                <a href="{{ route('vitals.recommendations') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vitals.recommendations') ? 'bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300' : 'text-ink-700 dark:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800' }}">
                    <flux:icon.light-bulb class="size-4" />Recommendations
                </a>
                @endif
                @if(Route::has('vitals.insights'))
                <a href="{{ route('vitals.insights') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vitals.insights') ? 'bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300' : 'text-ink-700 dark:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800' }}">
                    <flux:icon.sparkles class="size-4" />Insights
                </a>
                @endif
                @if(Route::has('vitals.learn'))
                <a href="{{ route('vitals.learn') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vitals.learn') ? 'bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300' : 'text-ink-700 dark:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800' }}">
                    <flux:icon.book-open class="size-4" />Learn
                </a>
                @endif
                @if(Route::has('vitals.budgets'))
                <a href="{{ route('vitals.budgets') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vitals.budgets') ? 'bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300' : 'text-ink-700 dark:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800' }}">
                    <flux:icon.chart-bar class="size-4" />Budgets
                </a>
                @endif
            </div>
        </nav>
    </div>
</flux:header>

<flux:main container>
    {{ $slot ?? '' }}
</flux:main>

@vitalsSpotlight

@livewireScripts
@fluxScripts
</body>
</html>
