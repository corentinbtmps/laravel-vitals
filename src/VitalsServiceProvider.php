<?php

declare(strict_types=1);

namespace LaravelVitals;

use Illuminate\Support\Facades\Gate;
use LaravelVitals\Commands\AuditCommand;
use LaravelVitals\Commands\DiscoverCommand;
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
            ->hasViewComponents('vitals', \LaravelVitals\View\Components\CodeReference::class)
            ->hasRoute('web')
            ->hasCommands([AuditCommand::class, DiscoverCommand::class]);
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

        $this->app->bind(\LaravelVitals\Telemetry\TelemetryRecorder::class);

        $this->app->singleton(\LaravelVitals\Recommendations\RecommendationRegistry::class);

        $this->app->singleton(\LaravelVitals\Recommendations\RecommendationBuilder::class, function ($app): \LaravelVitals\Recommendations\RecommendationBuilder {
            $analyzers = [
                $app->make(\LaravelVitals\Analyzers\BladeAssetAnalyzer::class),
                $app->make(\LaravelVitals\Analyzers\ImageAnalyzer::class),
                $app->make(\LaravelVitals\Analyzers\LaravelConfigAnalyzer::class),
                $app->make(\LaravelVitals\Analyzers\ComposerAnalyzer::class),
                $app->make(\LaravelVitals\Analyzers\ViteConfigAnalyzer::class),
                $app->make(\LaravelVitals\Analyzers\BladeViewAnalyzer::class),
                $app->make(\LaravelVitals\Analyzers\EnvironmentAnalyzer::class),
            ];

            foreach ((array) config('vitals.analyzers.custom', []) as $class) {
                if (is_string($class) && class_exists($class)) {
                    $analyzers[] = $app->make($class);
                }
            }

            return new \LaravelVitals\Recommendations\RecommendationBuilder(
                $app->make(\LaravelVitals\Recommendations\RecommendationRegistry::class),
                $analyzers,
            );
        });

        $this->app->singleton(\LaravelVitals\Contracts\ChartRenderer::class, function () {
            $configured = (string) config('vitals.ui.charts', 'auto');

            if ($configured === 'flux' || ($configured === 'auto' && class_exists('\\Flux\\Pro\\Charts\\Chart'))) {
                return new \LaravelVitals\Charts\FluxProChartsRenderer();
            }

            return new \LaravelVitals\Charts\ApexChartsRenderer();
        });
    }

    public function packageBooted(): void
    {
        \Livewire\Livewire::component('vitals::pages.overview', \LaravelVitals\Livewire\Pages\Overview::class);
        \Livewire\Livewire::component('vitals::pages.urls-list', \LaravelVitals\Livewire\Pages\UrlsList::class);
        \Livewire\Livewire::component('vitals::pages.audit-detail', \LaravelVitals\Livewire\Pages\AuditDetail::class);

        Gate::define('viewVitals', function ($user = null): bool {
            $callback = app(Vitals::class)->authorizeCallback();

            if ($callback !== null) {
                return (bool) $callback($user);
            }

            // Default: allow only in the local environment.
            return app()->environment('local');
        });

        // Provide a sensible default for the "vitals" filesystem disk if the host
        // app has not declared one. Hosts can override by editing config/filesystems.php.
        if (! config()->has('filesystems.disks.vitals')) {
            config()->set('filesystems.disks.vitals', [
                'driver' => 'local',
                'root'   => storage_path('app/vitals'),
                'throw'  => false,
            ]);
        }

        if ((bool) config('vitals.telemetry.auto_register', true)) {
            $router = $this->app->make(\Illuminate\Routing\Router::class);
            $router->pushMiddlewareToGroup('web', \LaravelVitals\Http\Middleware\CaptureVitalsTelemetry::class);
        }
    }
}
