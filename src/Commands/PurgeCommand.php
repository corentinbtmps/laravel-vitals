<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;

final class PurgeCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:purge {--demo : Only purge demo data}';

    /** @var string */
    protected $description = 'Delete vitals data (all by default, or only demo with --demo).';

    public function handle(): int
    {
        $demoOnly = (bool) $this->option('demo');

        if (! $demoOnly && ! $this->confirm('Delete ALL vitals data? This cannot be undone.', false)) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        $models = [
            \LaravelVitals\Models\BackendTelemetry::class,
            \LaravelVitals\Models\Recommendation::class,
            \LaravelVitals\Models\Audit::class,
            \LaravelVitals\Models\Url::class,
        ];

        foreach ($models as $class) {
            $query = $class::query();
            if ($demoOnly) {
                $query->where('is_demo', true);
            }
            $count = $query->count();
            $query->delete();
            $this->line("Deleted {$count} from " . class_basename($class));
        }

        return self::SUCCESS;
    }
}
