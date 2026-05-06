<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laravel Vitals' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
    @fluxAppearance
</head>
<body class="h-full bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
<div class="min-h-full">
    <header class="border-b bg-white dark:bg-zinc-900 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('vitals.dashboard') }}" class="font-bold text-lg">
                Laravel Vitals
            </a>
            <nav class="flex gap-4 text-sm">
                <a href="{{ route('vitals.urls') }}" class="hover:underline">URLs</a>
                <a href="{{ route('vitals.recommendations') }}" class="hover:underline">Recommendations</a>
                <a href="{{ route('vitals.budgets') }}" class="hover:underline">Budgets</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8">
        {{ $slot ?? '' }}
    </main>
</div>

@livewireScripts
@fluxScripts
</body>
</html>
