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
            ->hasMigrations([
                'create_vitals_urls_table',
                'create_vitals_audits_table',
                'create_vitals_audit_recommendations_table',
                'create_vitals_backend_telemetry_table',
            ])
            ->hasViews()
            ->hasTranslations()
            ->hasRoute('web');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Vitals::class);
        $this->app->alias(Vitals::class, 'vitals');
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
