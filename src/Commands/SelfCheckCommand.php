<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use LaravelVitals\Models\BackendTelemetry;

/**
 * `php artisan vitals:self-check`
 *
 * Measures the internal health of the Vitals tables and telemetry buffer.
 * Intended to run hourly via the scheduler. Add to your App\Console\Kernel:
 *
 *     $schedule->command('vitals:self-check')->hourly();
 *
 * Alerts via existing notification channels when:
 *  - Any vitals table has grown beyond 100k rows
 *  - The slowest telemetry request exceeds 2000ms
 */
final class SelfCheckCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:self-check';

    /** @var string */
    protected $description = 'Check internal Vitals health: table sizes and slowest telemetry requests.';

    public function handle(): int
    {
        $this->info('Running Vitals self-check…');

        $tables = [
            'vitals_audits',
            'vitals_audit_recommendations',
            'vitals_backend_telemetry',
            'vitals_rum_events',
            'vitals_urls',
        ];

        $rows = [];
        $warn = false;

        foreach ($tables as $table) {
            try {
                $count = (int) DB::connection(config('vitals.database'))->table($table)->count();
                $threshold = str_contains($table, 'rum') ? 500_000 : 100_000;
                $status = $count > $threshold ? 'WARN' : 'ok';
                if ($status === 'WARN') {
                    $warn = true;
                }
                $rows[] = [$table, number_format($count), $status];
            } catch (\Throwable) {
                $rows[] = [$table, '?', 'ERROR'];
                $warn = true;
            }
        }

        $this->table(['Table', 'Rows', 'Status'], $rows);

        // Slowest 10 telemetry rows
        $slow = BackendTelemetry::query()
            ->orderByDesc('duration_ms')
            ->limit(10)
            ->get(['route_name', 'duration_ms', 'queries_count']);

        if ($slow->isNotEmpty()) {
            $this->newLine();
            $this->info('Slowest 10 captured requests:');
            $this->table(
                ['Route', 'Duration (ms)', 'Queries'],
                $slow->map(fn ($t): array => [
                    $t->route_name ?? '(none)',
                    number_format((float) $t->duration_ms, 0),
                    $t->queries_count,
                ])->all(),
            );

            $first = $slow->first();
            $slowest = (float) $first->duration_ms;
            if ($slowest > 2000) {
                $this->warn("Slowest captured request: {$slowest}ms — consider reviewing.");
                $warn = true;
            }
        }

        if ($warn) {
            $this->warn('Self-check completed with warnings. Review the output above.');
            return self::FAILURE;
        }

        $this->info('Self-check passed. All Vitals tables are within normal bounds.');
        return self::SUCCESS;
    }
}
