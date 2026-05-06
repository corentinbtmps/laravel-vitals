<?php

declare(strict_types=1);

namespace LaravelVitals\Drivers;

use JsonException;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\AuditOptions;
use LaravelVitals\Support\LighthouseReport;
use LaravelVitals\Support\ProcessFactory;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Drives Lighthouse via Playwright. Spawns a Node process that runs the
 * `playwright-lighthouse` package against the target URL.
 *
 * Requires:
 *   - node 18+ on $PATH
 *   - `playwright` and `playwright-lighthouse` npm packages (host-installed)
 *
 * Configuration: config('vitals.drivers.playwright').
 */
final class PlaywrightDriver implements LighthouseDriver
{
    public function __construct(
        private readonly ProcessFactory $processes,
    ) {
    }

    public function audit(Url $url, AuditOptions $options): LighthouseReport
    {
        $config = (array) config('vitals.drivers.playwright', []);

        $node = (string) ($config['node_binary'] ?? 'node');
        $timeout = (int) ($config['timeout_seconds'] ?? 120);

        $script = $this->scriptPath();

        $appUrl = rtrim((string) config('app.url', 'http://localhost'), '/');
        $target = $appUrl . '/' . ltrim($url->path, '/');

        $device = preg_replace('/[^a-z]/', '', strtolower($options->device)) ?: 'mobile';
        $headers = json_encode($options->extraHeaders, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $command = sprintf(
            '%s %s --url=%s --device=%s --headers=%s',
            escapeshellcmd($node),
            escapeshellarg($script),
            escapeshellarg($target),
            $device,
            escapeshellarg($headers),
        );

        $process = $this->processes->fromShellCommandline($command, timeoutSeconds: $timeout);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new AuditException(
                "Playwright runner failed for {$url->label}: " . $e->getMessage(),
                driver: 'playwright',
                previous: $e,
            );
        }

        try {
            return LighthouseReport::fromLighthouseJson($process->getOutput());
        } catch (JsonException $e) {
            throw new AuditException(
                "Playwright runner returned invalid JSON for {$url->label}",
                driver: 'playwright',
                previous: $e,
            );
        }
    }

    public function isAvailable(): bool
    {
        $node = (string) config('vitals.drivers.playwright.node_binary', 'node');

        if (str_contains($node, '/')) {
            return is_file($node) && is_executable($node);
        }

        $process = $this->processes->fromShellCommandline(
            sprintf('command -v %s', escapeshellarg($node)),
            timeoutSeconds: 5,
        );

        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) !== '';
    }

    private function scriptPath(): string
    {
        return dirname(__DIR__, 2) . '/stubs/node/vitals-playwright.mjs';
    }
}
