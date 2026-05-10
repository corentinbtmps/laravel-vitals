<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('vitals.status.title', config('app.name', 'Status')) }}</title>
    <link rel="stylesheet" href="{{ route('vitals.assets', 'dashboard.css') }}">
    @livewireStyles
</head>
<body class="min-h-full bg-canvas text-ink-900 dark:bg-ink-950 dark:text-ink-100">
    <div class="max-w-3xl mx-auto px-4 py-12">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
