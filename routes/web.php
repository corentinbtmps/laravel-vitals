<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelVitals\Http\Middleware\Authorize;
use LaravelVitals\Livewire\Pages\AuditDetail;
use LaravelVitals\Livewire\Pages\Budgets;
use LaravelVitals\Livewire\Pages\Overview;
use LaravelVitals\Livewire\Pages\RecommendationsIndex;
use LaravelVitals\Livewire\Pages\UrlDetail;
use LaravelVitals\Livewire\Pages\UrlsList;

if ((bool) config('vitals.dashboard.enabled', true)) {
    Route::middleware([...config('vitals.dashboard.middleware', ['web']), Authorize::class])
        ->prefix(config('vitals.dashboard.path', 'vitals'))
        ->group(function (): void {
            Route::get('/',                      Overview::class)            ->name('vitals.dashboard');
            Route::get('/urls',                  UrlsList::class)            ->name('vitals.urls');
            Route::get('/urls/{url}',            UrlDetail::class)           ->name('vitals.url');
            Route::get('/audits/{audit}',        AuditDetail::class)         ->name('vitals.audit');
            Route::get('/budgets',               Budgets::class)             ->name('vitals.budgets');
            Route::get('/recommendations',       RecommendationsIndex::class)->name('vitals.recommendations');
        });

    // Public asset routes (no auth gate — needed for the dashboard layout to work for any visitor whose Authorize gate denied)
    Route::middleware(config('vitals.dashboard.middleware', ['web']))
        ->prefix(config('vitals.dashboard.path', 'vitals') . '/_assets')
        ->group(function (): void {
            Route::get('/{file}', \LaravelVitals\Http\Controllers\AssetController::class)
                ->where('file', '[a-zA-Z0-9.\-]+')
                ->name('vitals.assets');
        });
}
