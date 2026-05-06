<?php

declare(strict_types=1);

namespace LaravelVitals;

use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Vitals package service provider.
 *
 * Wiring of config, migrations, views, routes, commands and assets is
 * delegated to spatie/laravel-package-tools' fluent registration API.
 */
final class VitalsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-vitals')
            ->hasConfigFile('vitals')
            ->discoversMigrations()
            ->runsMigrations()
            ->hasViews()
            ->hasRoute('web');
    }

    /**
     * Register translations from the package's lang/ directory.
     *
     * spatie/laravel-package-tools's hasTranslations() hard-codes resources/lang/,
     * so we wire the lang/ path in ourselves and skip that helper entirely.
     */
    protected function bootPackageTranslations(): static
    {
        $langPath = __DIR__ . '/../lang';

        $this->loadTranslationsFrom($langPath, 'vitals');

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$langPath => lang_path('vendor/vitals')],
                'vitals-translations',
            );
        }

        return $this;
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Vitals::class);
        $this->app->alias(Vitals::class, 'vitals');

        $this->app->singleton(\LaravelVitals\Support\ProcessFactory::class);
        $this->app->singleton(\LaravelVitals\Drivers\LighthouseDriverManager::class);
        $this->app->bind(
            \LaravelVitals\Contracts\LighthouseDriver::class,
            fn ($app) => $app->make(\LaravelVitals\Drivers\LighthouseDriverManager::class)->resolve(),
        );
    }

    public function packageBooted(): void
    {
        Gate::define('viewVitals', function ($user = null): bool {
            $callback = app(Vitals::class)->authorizeCallback();

            if ($callback !== null) {
                return (bool) $callback($user);
            }

            // Default: allow only in the local environment.
            return app()->environment('local');
        });
    }
}
