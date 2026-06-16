<?php

declare(strict_types=1);

namespace LaravelVitals\Tests;

use LaravelVitals\VitalsServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Service providers loaded for every test.
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            VitalsServiceProvider::class,
        ];
    }

    /**
     * Define the testing environment.
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');

        // Defaults to an in-memory SQLite database. Set DB_CONNECTION=pgsql (and the
        // matching DB_* vars) to run the suite against PostgreSQL — the CI matrix uses
        // this to catch driver-specific issues such as the missing max(uuid) aggregate.
        if (getenv('DB_CONNECTION') === 'pgsql') {
            $app['config']->set('database.connections.testing', [
                'driver'   => 'pgsql',
                'host'     => getenv('DB_HOST') ?: '127.0.0.1',
                'port'     => getenv('DB_PORT') ?: '5432',
                'database' => getenv('DB_DATABASE') ?: 'vitals_test',
                'username' => getenv('DB_USERNAME') ?: 'postgres',
                'password' => getenv('DB_PASSWORD') ?: 'postgres',
                'charset'  => 'utf8',
                'prefix'   => '',
                'schema'   => 'public',
            ]);
        } else {
            $app['config']->set('database.connections.testing', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);
        }

        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('app.env', 'local');
        $app['env'] = 'local';
    }

    /**
     * Run the package migrations against the in-memory testing database.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
