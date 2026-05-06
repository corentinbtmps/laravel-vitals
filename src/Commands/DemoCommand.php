<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use LaravelVitals\Demo\DemoSeeder;
use LaravelVitals\Models\Url;

final class DemoCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:demo {--reseed : Drop existing demo data first}';

    /** @var string */
    protected $description = 'Seed Laravel Vitals with fictional demo data for screenshots and exploration.';

    public function handle(DemoSeeder $seeder): int
    {
        if ($this->option('reseed')) {
            $this->purgeDemo();
        }

        if (Url::where('is_demo', true)->exists()) {
            $this->warn('Demo data already exists. Use --reseed to regenerate.');
            return self::SUCCESS;
        }

        $this->info('Seeding demo data...');
        $seeder->seed();
        $this->info('Done. Visit /vitals to explore.');

        return self::SUCCESS;
    }

    private function purgeDemo(): void
    {
        \LaravelVitals\Models\BackendTelemetry::where('is_demo', true)->delete();
        \LaravelVitals\Models\Recommendation::where('is_demo', true)->delete();
        \LaravelVitals\Models\Audit::where('is_demo', true)->delete();
        Url::where('is_demo', true)->delete();
    }
}
