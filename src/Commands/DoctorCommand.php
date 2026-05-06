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
 */
final class DoctorCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:doctor';

    /** @var string */
    protected $description = 'Diagnose Laravel Vitals configuration and dependencies.';

    public function handle(LighthouseDriverManager $manager): int
    {
        $hadFailures = false;

        // Database
        $this->info('Database');
        foreach (['vitals_urls', 'vitals_audits', 'vitals_audit_recommendations', 'vitals_backend_telemetry'] as $table) {
            $exists = Schema::hasTable($table);
            $this->line($this->mark($exists) . " {$table}");
            if (! $exists) {
                $hadFailures = true;
            }
        }

        // Lighthouse drivers
        $this->newLine();
        $this->info('Lighthouse drivers');
        foreach (['local', 'playwright', 'pagespeed'] as $name) {
            try {
                $available = $manager->driver($name)->isAvailable();
                $this->line($this->mark($available) . " {$name}");
            } catch (\Throwable $e) {
                $this->line('  ✗ ' . $name . ' (' . $e->getMessage() . ')');
            }
        }

        // Storage
        $this->newLine();
        $this->info('Storage');
        $disk = (string) config('vitals.storage.disk', 'local');
        $this->line('  → disk: ' . $disk);
        try {
            $probe = 'vitals-doctor-' . uniqid();
            \Illuminate\Support\Facades\Storage::disk($disk)->put($probe, 'ok');
            \Illuminate\Support\Facades\Storage::disk($disk)->delete($probe);
            $this->line('  ✓ writable');
        } catch (\Throwable $e) {
            $this->line('  ✗ not writable (' . $e->getMessage() . ')');
            $hadFailures = true;
        }

        // Notifications
        $this->newLine();
        $this->info('Notifications');
        $enabled = (bool) config('vitals.notifications.enabled');
        $this->line($this->mark($enabled) . ' enabled');
        $channels = (array) config('vitals.notifications.channels', []);
        $this->line('  → channels: ' . (count($channels) > 0 ? implode(', ', $channels) : 'none'));
        if (in_array('mail', $channels, true)) {
            $to = (string) config('vitals.notifications.mail.to', '');
            $this->line($this->mark($to !== '') . ' mail.to: ' . ($to !== '' ? $to : 'NOT SET'));
        }
        if (in_array('slack', $channels, true)) {
            $webhook = (string) config('vitals.notifications.slack.webhook_url', '');
            $this->line($this->mark($webhook !== '') . ' slack.webhook_url: ' . ($webhook !== '' ? 'SET' : 'NOT SET'));
        }

        // Telemetry sources
        $this->newLine();
        $this->info('Telemetry sources');
        foreach ([
            \LaravelVitals\Telemetry\Sources\PulseSource::class    => 'Pulse',
            \LaravelVitals\Telemetry\Sources\TelescopeSource::class => 'Telescope',
        ] as $class => $label) {
            $available = (new $class())->isAvailable();
            $this->line($this->mark($available) . " {$label}" . ($available ? '' : ' (table not found, optional)'));
        }

        $this->newLine();
        if ($hadFailures) {
            $this->warn('Some checks failed. Resolve them before running audits.');
            return self::FAILURE;
        }

        $this->info('All checks passed.');
        return self::SUCCESS;
    }

    private function mark(bool $ok): string
    {
        return $ok ? '  ✓' : '  ✗';
    }
}
