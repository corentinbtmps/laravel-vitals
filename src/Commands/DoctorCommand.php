<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use LaravelVitals\Drivers\LighthouseDriverManager;

/**
 * `php artisan vitals:doctor`
 *
 * Diagnostic check for common configuration issues.
 * Exit code 0 = all pass, 1 = at least one failure.
 * --quiet suppresses passing checks (CI-friendly).
 */
final class DoctorCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:doctor';

    /** @var string */
    protected $description = 'Diagnose Laravel Vitals configuration and dependencies.';

    /**
     * Accumulated rows: [ status, label, note ]
     *
     * @var array<int, array{status: string, label: string, note: string}>
     */
    private array $rows = [];

    private bool $hadFailures = false;

    public function handle(LighthouseDriverManager $manager): int
    {
        // --quiet is a built-in Symfony console flag; use the output's verbosity.
        $quiet = $this->getOutput()->isQuiet();

        // ── Database ──────────────────────────────────────────────────────────
        $this->section('Database');

        foreach (['vitals_urls', 'vitals_audits', 'vitals_audit_recommendations', 'vitals_backend_telemetry'] as $table) {
            $exists = Schema::hasTable($table);
            $this->check($exists ? 'pass' : 'fail', $table, $exists ? '' : 'Run php artisan migrate');
        }

        // ── Vitals config published ───────────────────────────────────────────
        $this->section('Package');

        $configPublished = file_exists(config_path('vitals.php'));
        $this->check(
            $configPublished ? 'pass' : 'warn',
            'config/vitals.php exists in host app',
            $configPublished ? '' : 'Run: php artisan vendor:publish --tag=vitals-config',
        );

        // At least one URL configured
        $urls = (array) config('vitals.urls', []);
        $urlsConfigured = count($urls) > 0;
        $this->check(
            $urlsConfigured ? 'pass' : 'warn',
            'At least one URL configured',
            $urlsConfigured ? '' : 'Add URLs under vitals.urls in config/vitals.php',
        );

        // ── Dist assets ───────────────────────────────────────────────────────
        $this->section('Dist assets');

        $distDir = dirname(__DIR__, 2) . '/dist';
        $assets  = ['dashboard.css', 'dashboard.js', 'favicon.svg'];
        foreach ($assets as $asset) {
            $exists = file_exists($distDir . '/' . $asset);
            $this->check($exists ? 'pass' : 'fail', "dist/{$asset}", $exists ? '' : 'Run npm run build');
        }

        // Geist woff2 fonts
        $fonts = glob($distDir . '/geist-*.woff2') ?: [];
        $hasFonts = count($fonts) > 0;
        $this->check(
            $hasFonts ? 'pass' : 'fail',
            'Geist fonts (dist/geist-*.woff2)',
            $hasFonts ? '' : 'Run npm run build — fonts are missing from dist/',
        );

        // ── Environment ───────────────────────────────────────────────────────
        $this->section('Environment');

        $isProduction = app()->environment('production');

        $debugOff = ! (bool) config('app.debug', false);
        if ($isProduction) {
            $this->check($debugOff ? 'pass' : 'fail', 'APP_DEBUG=false in production', $debugOff ? '' : 'Set APP_DEBUG=false in .env');
        } else {
            $this->check('info', 'APP_DEBUG (not production — skipped)', '');
        }

        $cacheDriver = (string) config('cache.default', 'file');
        if ($isProduction) {
            $this->check(
                $cacheDriver !== 'file' ? 'pass' : 'warn',
                "Cache driver ({$cacheDriver})",
                $cacheDriver === 'file' ? 'Consider redis or memcached in production for better performance' : '',
            );
        } else {
            $this->check('info', "Cache driver: {$cacheDriver} (not production — skipped)", '');
        }

        $sessionDriver = (string) config('session.driver', 'file');
        if ($isProduction) {
            $this->check(
                $sessionDriver !== 'file' ? 'pass' : 'warn',
                "Session driver ({$sessionDriver})",
                $sessionDriver === 'file' ? 'File sessions cause lock contention under concurrency — use redis or database' : '',
            );
        } else {
            $this->check('info', "Session driver: {$sessionDriver} (not production — skipped)", '');
        }

        // ── Optional integrations ─────────────────────────────────────────────
        $this->section('Optional integrations');

        $octaneInstalled = class_exists('Laravel\\Octane\\OctaneServiceProvider');
        $this->check('info', 'Laravel Octane', $octaneInstalled ? 'detected ✓' : 'not installed — consider Octane for lower TTFB');

        $pulseInstalled = class_exists('Laravel\\Pulse\\PulseServiceProvider');
        $this->check('info', 'Laravel Pulse', $pulseInstalled ? 'detected ✓' : 'not installed (optional)');

        $telescopeInstalled = class_exists('Laravel\\Telescope\\TelescopeServiceProvider');
        $this->check('info', 'Laravel Telescope', $telescopeInstalled ? 'detected ✓' : 'not installed (optional)');

        // ── Lighthouse drivers ────────────────────────────────────────────────
        $this->section('Lighthouse drivers');

        foreach (['local', 'playwright', 'pagespeed'] as $name) {
            try {
                $available = $manager->driver($name)->isAvailable();
                $this->check($available ? 'pass' : 'warn', $name, $available ? '' : $manager->installHint($name));
            } catch (\Throwable $e) {
                $this->check('fail', $name, $e->getMessage());
            }
        }

        // ── Storage ───────────────────────────────────────────────────────────
        $this->section('Storage');

        $disk = (string) config('vitals.storage.disk', 'local');
        try {
            $probe = 'vitals-doctor-' . uniqid();
            \Illuminate\Support\Facades\Storage::disk($disk)->put($probe, 'ok');
            \Illuminate\Support\Facades\Storage::disk($disk)->delete($probe);
            $this->check('pass', "Disk \"{$disk}\" writable", '');
        } catch (\Throwable $e) {
            $this->check('fail', "Disk \"{$disk}\" writable", $e->getMessage());
        }

        // ── Notifications ─────────────────────────────────────────────────────
        $this->section('Notifications');

        $enabled  = (bool) config('vitals.notifications.enabled');
        $this->check($enabled ? 'pass' : 'info', 'Notifications enabled', $enabled ? '' : 'Set vitals.notifications.enabled = true to receive alerts');

        $channels = (array) config('vitals.notifications.channels', []);
        if (in_array('mail', $channels, true)) {
            $to = (string) config('vitals.notifications.mail.to', '');
            $this->check($to !== '' ? 'pass' : 'warn', 'mail.to', $to !== '' ? $to : 'NOT SET — add notifications.mail.to');
        }
        if (in_array('slack', $channels, true)) {
            $webhook = (string) config('vitals.notifications.slack.webhook_url', '');
            $this->check($webhook !== '' ? 'pass' : 'warn', 'slack.webhook_url', $webhook !== '' ? 'SET' : 'NOT SET');
        }

        // ── Telemetry sources ─────────────────────────────────────────────────
        $this->section('Telemetry sources');

        foreach ([
            \LaravelVitals\Telemetry\Sources\PulseSource::class    => 'Pulse',
            \LaravelVitals\Telemetry\Sources\TelescopeSource::class => 'Telescope',
        ] as $class => $label) {
            $available = (new $class())->isAvailable();
            $this->check('info', $label, $available ? 'available' : 'table not found (optional)');
        }

        // ── Render table ──────────────────────────────────────────────────────
        $this->renderTable($quiet);

        if ($this->hadFailures) {
            if (! $quiet) {
                $this->newLine();
                $this->warn('Some checks failed. Resolve them before running audits.');
            }
            return self::FAILURE;
        }

        if (! $quiet) {
            $this->newLine();
            $this->info('All checks passed.');
        }
        return self::SUCCESS;
    }

    private function section(string $name): void
    {
        $this->rows[] = ['status' => 'section', 'label' => $name, 'note' => ''];
    }

    private function check(string $status, string $label, string $note): void
    {
        if ($status === 'fail') {
            $this->hadFailures = true;
        }

        $this->rows[] = ['status' => $status, 'label' => $label, 'note' => $note];
    }

    private function renderTable(bool $quiet): void
    {
        $pendingSection = null;
        $sectionPrinted = false;

        foreach ($this->rows as $row) {
            if ($row['status'] === 'section') {
                $pendingSection = $row['label'];
                $sectionPrinted = false;
                continue;
            }

            // In quiet mode skip rows that are not problems.
            $silentRow = $quiet && in_array($row['status'], ['pass', 'info'], true);

            if (! $silentRow) {
                // Print the section header lazily before the first visible row.
                if ($pendingSection !== null && ! $sectionPrinted) {
                    $this->newLine();
                    $this->info($pendingSection);
                    $sectionPrinted = true;
                }

                $icon = match ($row['status']) {
                    'pass'  => '  ✓',
                    'fail'  => '  ✗',
                    'warn'  => '  ⚠',
                    default => '  ℹ',
                };

                $line = $icon . ' ' . $row['label'];
                if ($row['note'] !== '') {
                    $line .= ' — ' . $row['note'];
                }

                $this->line($line);
            }
        }
    }
}
