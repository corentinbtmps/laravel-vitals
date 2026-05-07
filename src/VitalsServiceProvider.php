<?php

declare(strict_types=1);

namespace LaravelVitals;

use Illuminate\Support\Facades\Gate;
use LaravelVitals\Commands\AuditCommand;
use LaravelVitals\Commands\BoostDiffCommand;
use LaravelVitals\Commands\BoostInstallCommand;
use LaravelVitals\Commands\CheckRegressionsCommand;
use LaravelVitals\Commands\DemoCommand;
use LaravelVitals\Commands\DigestSendCommand;
use LaravelVitals\Commands\DiscoverCommand;
use LaravelVitals\Commands\DoctorCommand;
use LaravelVitals\Commands\InstallCommand;
use LaravelVitals\Commands\PurgeCommand;
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
            ->hasCommands([AuditCommand::class, BoostDiffCommand::class, BoostInstallCommand::class, CheckRegressionsCommand::class, DemoCommand::class, DigestSendCommand::class, DiscoverCommand::class, DoctorCommand::class, InstallCommand::class, PurgeCommand::class]);
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

            // Resolve sources: container override wins (used in tests).
            $sources = $app->bound('vitals.telemetry-sources')
                ? (array) $app->make('vitals.telemetry-sources')
                : [
                    $app->make(\LaravelVitals\Telemetry\Sources\PulseSource::class),
                    $app->make(\LaravelVitals\Telemetry\Sources\TelescopeSource::class),
                ];

            return new \LaravelVitals\Recommendations\RecommendationBuilder(
                $app->make(\LaravelVitals\Recommendations\RecommendationRegistry::class),
                $analyzers,
                $sources,
            );
        });

        $this->app->singleton(\LaravelVitals\Contracts\ChartRenderer::class, function (): \LaravelVitals\Charts\FluxProChartsRenderer|\LaravelVitals\Charts\ApexChartsRenderer {
            $configured = (string) config('vitals.ui.charts', 'auto');

            if ($configured === 'flux' || ($configured === 'auto' && class_exists('\\Flux\\Pro\\Charts\\Chart'))) {
                return new \LaravelVitals\Charts\FluxProChartsRenderer();
            }

            return new \LaravelVitals\Charts\ApexChartsRenderer();
        });
    }

    public function packageBooted(): void
    {
        \Livewire\Livewire::addNamespace('vitals', classNamespace: 'LaravelVitals\\Livewire');
        \Livewire\Livewire::component('vitals::pages.insights', \LaravelVitals\Livewire\Pages\Insights::class);
        \Livewire\Livewire::component('vitals::pages.learn', \LaravelVitals\Livewire\Pages\Learn::class);
        \Livewire\Livewire::component('vitals::components.onboarding-banner', \LaravelVitals\Livewire\Components\OnboardingBanner::class);

        $this->registerOnboardingSteps();

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

        // Register telemetry listeners ONCE at boot so they do not accumulate
        // across requests in long-running workers (Octane). Each listener
        // delegates to the currently active recorder bound in the container.
        \Illuminate\Support\Facades\DB::listen(function (\Illuminate\Database\Events\QueryExecuted $event): void {
            if (app()->bound('vitals.active-recorder')) {
                app('vitals.active-recorder')->recordQuery($event);
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Cache\Events\CacheHit::class, function (): void {
            if (app()->bound('vitals.active-recorder')) {
                app('vitals.active-recorder')->incrementCacheHits();
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Cache\Events\CacheMissed::class, function (): void {
            if (app()->bound('vitals.active-recorder')) {
                app('vitals.active-recorder')->incrementCacheMisses();
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Queue\Events\JobQueued::class, function (): void {
            if (app()->bound('vitals.active-recorder')) {
                app('vitals.active-recorder')->incrementJobsDispatched();
            }
        });

        $this->publishes([
            dirname(__DIR__) . '/dist' => public_path('vendor/vitals'),
        ], 'vitals-assets');

        $this->publishes([
            dirname(__DIR__) . '/resources/views/mail' => resource_path('views/vendor/vitals/mail'),
        ], 'vitals-mail-views');
    }

    private function registerOnboardingSteps(): void
    {
        \Spatie\Onboard\Facades\Onboard::addStep(__('vitals::vitals.onboarding.steps.urls.title'))
            ->link('/' . config('vitals.path', 'vitals') . '/urls')
            ->cta(__('vitals::vitals.onboarding.steps.urls.cta'))
            ->completeIf(fn (): bool => \LaravelVitals\Models\Url::query()->count() > 0);

        \Spatie\Onboard\Facades\Onboard::addStep(__('vitals::vitals.onboarding.steps.audit.title'))
            ->link('/' . config('vitals.path', 'vitals') . '/urls')
            ->cta(__('vitals::vitals.onboarding.steps.audit.cta'))
            ->completeIf(fn (): bool => \LaravelVitals\Models\Audit::query()->where('status', 'completed')->exists());

        \Spatie\Onboard\Facades\Onboard::addStep(__('vitals::vitals.onboarding.steps.notifications.title'))
            ->link('/' . config('vitals.path', 'vitals'))
            ->cta(__('vitals::vitals.onboarding.steps.notifications.cta'))
            ->completeIf(fn (): bool =>
                ! empty(config('vitals.notifications.slack.webhook')) ||
                ! empty(config('vitals.notifications.mail.to'))
            );

        \Spatie\Onboard\Facades\Onboard::addStep(__('vitals::vitals.onboarding.steps.budgets.title'))
            ->link('/' . config('vitals.path', 'vitals') . '/budgets')
            ->cta(__('vitals::vitals.onboarding.steps.budgets.cta'))
            ->completeIf(fn (): bool => is_array(config('vitals.budgets')) && count(config('vitals.budgets', [])) > 0);
    }
}
