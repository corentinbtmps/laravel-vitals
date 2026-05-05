<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelVitals\Http\Controllers\PlaceholderController;
use LaravelVitals\Http\Middleware\Authorize;

Route::middleware([...config('vitals.dashboard.middleware', ['web']), Authorize::class])
    ->prefix(config('vitals.dashboard.path', 'vitals'))
    ->group(function (): void {
        Route::get('/', PlaceholderController::class)->name('vitals.dashboard');
    });
