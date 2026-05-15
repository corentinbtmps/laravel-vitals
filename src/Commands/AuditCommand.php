<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use LaravelVitals\Enums\Device;
use LaravelVitals\Enums\Severity;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\UrlSeeder;
use LaravelVitals\Vitals;

/**
 * `php artisan vitals:audit [label?] [--all] [--device=...] [--driver=...]
 *                           [--format=table|json] [--sync]`
 *
 * Runs a single audit (when --all is not set) or a batch of audits.
 *
 * --sync forces synchronous execution. Without it, --all dispatches a Bus
 * batch to the queue.
 */
final class AuditCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:audit
        {label? : URL label declared in config(\'vitals.urls\') or vitals_urls table}
        {--all : Audit every enabled URL via Bus::batch}
        {--device=mobile : mobile|desktop}
        {--driver= : Override the configured LighthouseDriver for this run}
        {--format=table : table|json|junit}
        {--sync : Force synchronous execution (single audit always sync; --all dispatches via queue otherwise)}
        {--fail-on-budget : Exit non-zero when any audit violates the configured budgets (1=warning, 2=critical)}
        {--force : Bypass the concurrency lock and run even if another audit is already in progress}';

    /** @var string */
    protected $description = 'Run one or more Lighthouse audits.';

    public function handle(Vitals $vitals, UrlSeeder $seeder): int
    {
        $driver = $this->option('driver');
        if (is_string($driver) && $driver !== '') {
            $vitals->driver($driver);
        }

        $seeder->sync();

        if ($this->option('all')) {
            return $this->handleAll($vitals);
        }

        return $this->handleSingle($vitals);
    }

    private function handleSingle(Vitals $vitals): int
    {
        $label = $this->argument('label');

        if (! is_string($label) || $label === '') {
            $this->error('Provide a URL label or use --all.');
            return self::FAILURE;
        }

        $url = Url::where('label', $label)->first();
        if ($url === null && ! array_key_exists($label, (array) config('vitals.urls', []))) {
            $this->error("URL [$label] not found in config or database.");
            return self::FAILURE;
        }

        $rawDevice = $this->option('device');
        $device = Device::tryFrom(is_string($rawDevice) ? $rawDevice : '') ?? Device::Mobile;

        // Concurrency lock — prevent parallel audits of the same URL.
        $urlId  = $url !== null ? $url->id : md5($label);
        $lockKey = "vitals:audit:{$urlId}";
        $lockTtl = (int) config('vitals.audit_timeout_seconds', 300);

        if (! $this->option('force')) {
            $lock = Cache::lock($lockKey, $lockTtl);

            if (! $lock->get()) {
                $this->error("Another audit of [{$label}] is already running. Wait for it to finish or use --force to bypass.");
                // EX_TEMPFAIL (75) — try again later
                return 75;
            }
        }

        try {
            $audit = $vitals->audit($label, $device, sync: true);
        } catch (AuditException $e) {
            $this->error($e->getMessage());
            if (! $this->option('force') && isset($lock)) {
                $lock->release();
            }
            return self::FAILURE;
        } finally {
            if (! $this->option('force') && isset($lock)) {
                $lock->release();
            }
        }

        /** @var Audit $fresh */
        $fresh = $audit->fresh() ?? $audit;

        $this->renderAudits([$fresh]);

        if ($this->option('fail-on-budget')) {
            $violations = \LaravelVitals\Budgets\PerfBudget::evaluate($fresh);
            $worst = $violations->worstSeverity();

            if (! $violations->isEmpty()) {
                app(\LaravelVitals\Notifications\Channels\VitalsNotifier::class)
                    ->send('budget_violation', new \LaravelVitals\Notifications\BudgetViolated($fresh, $violations));
            }

            if ($worst === Severity::Critical) {
                return 2;
            }
            if ($worst === Severity::Warning) {
                return 1;
            }
        }

        return self::SUCCESS;
    }

    private function handleAll(Vitals $vitals): int
    {
        $rawDevice = $this->option('device');
        $device = Device::tryFrom(is_string($rawDevice) ? $rawDevice : '') ?? Device::Mobile;

        if ($this->option('sync')) {
            // Synchronous mode: build one audit per URL ourselves and run them inline.
            $audits = [];

            foreach (Url::query()->where('enabled', true)->get() as $url) {
                $audits[] = $vitals->audit($url, $device, sync: true);
            }

            $this->renderAudits(array_map(static fn (Audit $a): Audit => $a->fresh() ?? $a, $audits));

            if ($this->option('fail-on-budget')) {
                $worst = null;
                foreach ($audits as $a) {
                    $aFresh = $a->fresh() ?? $a;
                    $violations = \LaravelVitals\Budgets\PerfBudget::evaluate($aFresh);

                    if (! $violations->isEmpty()) {
                        app(\LaravelVitals\Notifications\Channels\VitalsNotifier::class)
                            ->send('budget_violation', new \LaravelVitals\Notifications\BudgetViolated($aFresh, $violations));
                    }

                    $sev = $violations->worstSeverity();
                    if ($sev === Severity::Critical) {
                        $worst = Severity::Critical;
                        break;
                    }
                    if ($sev === Severity::Warning) {
                        $worst = Severity::Warning;
                    }
                }
                if ($worst === Severity::Critical) {
                    return 2;
                }
                if ($worst === Severity::Warning) {
                    return 1;
                }
            }

            return self::SUCCESS;
        }

        $batch = $vitals->auditAll($device);

        $this->info("Dispatched batch {$batch->id} with {$batch->totalJobs} job(s).");
        return self::SUCCESS;
    }

    /**
     * @param array<int, Audit> $audits
     */
    private function renderAudits(array $audits): void
    {
        if ($this->option('format') === 'json') {
            $payload = [
                'audits' => array_map(static fn (Audit $a): array => [
                    'id'     => $a->id,
                    'label'  => $a->url?->label,
                    'status' => $a->status->value,
                    'scores' => [
                        'performance'    => $a->score_performance,
                        'accessibility'  => $a->score_accessibility,
                        'best_practices' => $a->score_best_practices,
                        'seo'            => $a->score_seo,
                    ],
                    'metrics' => [
                        'lcp_ms'  => $a->lcp_ms,
                        'cls'     => $a->cls,
                        'inp_ms'  => $a->inp_ms,
                        'ttfb_ms' => $a->ttfb_ms,
                        'fcp_ms'  => $a->fcp_ms,
                        'si_ms'   => $a->si_ms,
                        'tbt_ms'  => $a->tbt_ms,
                    ],
                    'report_path' => $a->report_path,
                ], $audits),
            ];

            $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $this->line($encoded !== false ? $encoded : '{}');
            return;
        }

        if ($this->option('format') === 'junit') {
            $rows = array_map(static fn (Audit $a): array => [
                'audit' => $a,
                'violations' => \LaravelVitals\Budgets\PerfBudget::evaluate($a),
            ], $audits);

            $this->line(\LaravelVitals\Commands\Output\JUnitFormatter::format($rows));
            return;
        }

        $this->table(
            ['Label', 'Device', 'Status', 'Perf', 'A11y', 'BP', 'SEO', 'LCP', 'CLS', 'INP'],
            array_map(static fn (Audit $a): array => [
                $a->url?->label,
                $a->device->value,
                $a->status->value,
                $a->score_performance,
                $a->score_accessibility,
                $a->score_best_practices,
                $a->score_seo,
                $a->lcp_ms,
                $a->cls,
                $a->inp_ms,
            ], $audits),
        );

        foreach ($audits as $audit) {
            $this->info('Audit for [' . $audit->url?->label . ']:');
            $this->info('  status: ' . $audit->status->value);
        }
    }
}
