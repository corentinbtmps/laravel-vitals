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

<flux:header class="border-b border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900">
    {{-- Custom brand markup — flux:brand wraps content in h-6 rounded-sm overflow-hidden
         which clips a 32px logo asymmetrically. We render the link ourselves to keep
         the logo at its intended 32×32 size with full rounded-lg radius visible. --}}
    <a href="{{ route('vitals.dashboard') }}" class="flex items-center gap-2.5 shrink-0 mr-8 xl:mr-10">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-accent-400 to-accent-600 shadow-sm">
            <svg viewBox="0 0 64 64" class="h-5 w-5" fill="none">
                <path d="M8 34 H20 L24 24 L28 42 L32 16 L36 42 L40 24 L44 34 H56"
                      stroke="white" stroke-width="4"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <span class="text-base font-semibold text-ink-900 dark:text-ink-100">Laravel Vitals</span>
    </a>

    @php
        $navDivider = '<span class="self-center mx-2 h-5 w-px bg-ink-200 dark:bg-ink-800" aria-hidden="true"></span>';
    @endphp
    <flux:navbar class="-mb-px max-lg:hidden gap-1">
        <flux:navbar.item
            href="{{ route('vitals.dashboard') }}"
            icon="squares-2x2"
            :current="request()->routeIs('vitals.dashboard')"
        >Overview</flux:navbar.item>

        {!! $navDivider !!}

        @if(Route::has('vitals.urls'))
        <flux:navbar.item
            href="{{ route('vitals.urls') }}"
            icon="link"
            :current="request()->routeIs('vitals.url*')"
        >URLs</flux:navbar.item>
        @endif

        @if(Route::has('vitals.issues'))
        <flux:navbar.item
            href="{{ route('vitals.issues') }}"
            icon="exclamation-triangle"
            :current="request()->routeIs('vitals.issues')"
        >{{ __('vitals::vitals.nav.issues') }}</flux:navbar.item>
        @endif

        @if(Route::has('vitals.seo'))
        <flux:navbar.item
            href="{{ route('vitals.seo') }}"
            icon="globe-alt"
            :current="request()->routeIs('vitals.seo')"
        >{{ __('vitals::vitals.nav.seo') }}</flux:navbar.item>
        @endif

        {!! $navDivider !!}

        @if(Route::has('vitals.rum'))
        <flux:navbar.item
            href="{{ route('vitals.rum') }}"
            icon="signal"
            :current="request()->routeIs('vitals.rum')"
        >RUM</flux:navbar.item>
        @endif

        @if(Route::has('vitals.queries'))
        <flux:navbar.item
            href="{{ route('vitals.queries') }}"
            icon="circle-stack"
            :current="request()->routeIs('vitals.queries')"
        >Queries</flux:navbar.item>
        @endif

        {!! $navDivider !!}

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

    {{-- Spotlight trigger — full button with kbd hint (desktop only, right-aligned) --}}
    <button
        type="button"
        x-data
        x-on:click="$dispatch('modal-show', { name: 'vitals-spotlight' })"
        class="vitals-desktop-only items-center gap-2 rounded-lg border border-ink-200 dark:border-ink-800 bg-paper dark:bg-ink-900 hover:bg-ink-50 dark:hover:bg-ink-800/50 px-3 py-1.5 text-sm text-ink-500 hover:text-ink-700 dark:hover:text-ink-300 transition-colors"
        aria-label="{{ __('vitals::vitals.spotlight.button_label') }}"
    >
        <flux:icon.magnifying-glass class="size-4 shrink-0" />
        <span class="text-left">{{ __('vitals::vitals.spotlight.button_label') }}</span>
        <kbd class="inline-flex items-center gap-0.5 rounded border border-ink-200/60 dark:border-ink-700 bg-ink-50 dark:bg-ink-800 px-1.5 py-0.5 text-xs font-mono text-ink-500" x-data x-init="$el.firstElementChild.textContent = navigator.platform.toLowerCase().includes('mac') ? '⌘' : 'Ctrl'">
            <span></span>K
        </kbd>
    </button>

    <flux:button
        variant="ghost"
        icon="moon"
        x-data
        x-on:click="$flux.dark = ! $flux.dark"
        :tooltip="__('vitals::vitals.layout.toggle_theme')"
    />

    {{-- Mobile: search icon + burger menu (shown on max-lg screens) --}}
    <div class="vitals-mobile-only items-center gap-1" x-data="{ open: false }" @resize.window="if (window.innerWidth >= 1024) open = false">
        {{-- Mobile search icon button — opens the same Spotlight modal --}}
        <flux:button
            variant="ghost"
            icon="magnifying-glass"
            x-on:click="$dispatch('modal-show', { name: 'vitals-spotlight' })"
            aria-label="{{ __('vitals::vitals.spotlight.button_label') }}"
        />

        <flux:button
            variant="ghost"
            x-on:click="open = !open"
            aria-label="{{ __('vitals::vitals.layout.open_navigation') }}"
        >
            <flux:icon.bars-3 x-show="!open" class="size-5" />
            <flux:icon.x-mark x-show="open" class="size-5" x-cloak />
        </flux:button>

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
            class="fixed inset-y-0 right-0 z-50 w-72 max-w-[85vw] bg-paper dark:bg-ink-900 border-l border-ink-200 dark:border-ink-800 shadow-2xl px-4 py-6 overflow-y-auto lg:hidden"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="flex items-center justify-between mb-6 px-2">
                <span class="text-base font-semibold text-ink-900 dark:text-ink-100">Menu</span>
                <flux:button x-on:click="open = false" variant="ghost" size="sm" icon="x-mark" />
            </div>
            <flux:sidebar.nav>
                <flux:sidebar.item
                    href="{{ route('vitals.dashboard') }}"
                    icon="squares-2x2"
                    :current="request()->routeIs('vitals.dashboard')"
                >Overview</flux:sidebar.item>

                <flux:sidebar.group :heading="__('vitals::vitals.nav.group_audit')" class="mt-4">
                    @if(Route::has('vitals.urls'))
                        <flux:sidebar.item
                            href="{{ route('vitals.urls') }}"
                            icon="link"
                            :current="request()->routeIs('vitals.url*')"
                        >URLs</flux:sidebar.item>
                    @endif
                    @if(Route::has('vitals.issues'))
                        <flux:sidebar.item
                            href="{{ route('vitals.issues') }}"
                            icon="exclamation-triangle"
                            :current="request()->routeIs('vitals.issues')"
                        >{{ __('vitals::vitals.nav.issues') }}</flux:sidebar.item>
                    @endif
                    @if(Route::has('vitals.seo'))
                        <flux:sidebar.item
                            href="{{ route('vitals.seo') }}"
                            icon="globe-alt"
                            :current="request()->routeIs('vitals.seo')"
                        >{{ __('vitals::vitals.nav.seo') }}</flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('vitals::vitals.nav.group_telemetry')" class="mt-4">
                    @if(Route::has('vitals.rum'))
                        <flux:sidebar.item
                            href="{{ route('vitals.rum') }}"
                            icon="signal"
                            :current="request()->routeIs('vitals.rum')"
                        >RUM</flux:sidebar.item>
                    @endif
                    @if(Route::has('vitals.queries'))
                        <flux:sidebar.item
                            href="{{ route('vitals.queries') }}"
                            icon="circle-stack"
                            :current="request()->routeIs('vitals.queries')"
                        >Queries</flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('vitals::vitals.nav.group_reference')" class="mt-4">
                    @if(Route::has('vitals.learn'))
                        <flux:sidebar.item
                            href="{{ route('vitals.learn') }}"
                            icon="book-open"
                            :current="request()->routeIs('vitals.learn')"
                        >Learn</flux:sidebar.item>
                    @endif
                    @if(Route::has('vitals.budgets'))
                        <flux:sidebar.item
                            href="{{ route('vitals.budgets') }}"
                            icon="chart-bar"
                            :current="request()->routeIs('vitals.budgets')"
                        >Budgets</flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>
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
