<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelVitals\Http\Controllers\VitalsApiController;
use LaravelVitals\Http\Middleware\Authorize;
use LaravelVitals\Livewire\Pages\AuditDetail;
use LaravelVitals\Livewire\Pages\Budgets;
use LaravelVitals\Livewire\Pages\Insights;
use LaravelVitals\Livewire\Pages\Learn;
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
            Route::get('/insights',              Insights::class)            ->name('vitals.insights');
            Route::get('/recommendations',       RecommendationsIndex::class)->name('vitals.recommendations');
            Route::get('/learn',                 Learn::class)               ->name('vitals.learn');

            // JSON API v1
            Route::prefix('api/v1')->name('vitals.api.')->group(function (): void {
                Route::get('/audits',                    [VitalsApiController::class, 'audits'])      ->name('audits');
                Route::get('/audits/{audit}',            [VitalsApiController::class, 'audit'])       ->name('audit');
                Route::get('/urls',                      [VitalsApiController::class, 'urls'])        ->name('urls');
                Route::get('/urls/{url}/latest',         [VitalsApiController::class, 'urlLatest'])  ->name('url.latest');
                Route::get('/recommendations',           [VitalsApiController::class, 'recommendations'])->name('recommendations');
            });
        });

    // Public asset routes (no auth gate — needed for the dashboard layout to work for any visitor whose Authorize gate denied)
    Route::middleware(config('vitals.dashboard.middleware', ['web']))
        ->prefix(config('vitals.dashboard.path', 'vitals') . '/_assets')
        ->group(function (): void {
            Route::get('/{file}', \LaravelVitals\Http\Controllers\AssetController::class)
                ->where('file', '[a-zA-Z0-9.\-]+')
                ->name('vitals.assets');

            Route::get('/favicon.svg', function (): \Symfony\Component\HttpFoundation\Response {
                $path = dirname(__DIR__) . '/dist/favicon.svg';
                abort_unless(is_file($path), 404);

                return response(file_get_contents($path))
                    ->header('Content-Type', 'image/svg+xml')
                    ->header('Cache-Control', 'public, max-age=86400');
            })->name('vitals.favicon.svg');

            Route::get('/favicon.ico', function (): \Symfony\Component\HttpFoundation\Response {
                $path = dirname(__DIR__) . '/dist/favicon.ico';
                abort_unless(is_file($path), 404);

                return response(file_get_contents($path))
                    ->header('Content-Type', 'image/x-icon')
                    ->header('Cache-Control', 'public, max-age=86400');
            })->name('vitals.favicon.ico');
        });
}
