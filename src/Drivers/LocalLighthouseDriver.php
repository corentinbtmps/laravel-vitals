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
 * Drives Lighthouse via a locally-installed `lighthouse` CLI.
 *
 * Configuration: config('vitals.drivers.local').
 */
final readonly class LocalLighthouseDriver implements LighthouseDriver
{
    public function __construct(
        private ProcessFactory $processes,
    ) {
    }

    public function audit(Url $url, AuditOptions $options): LighthouseReport
    {
        $config = (array) config('vitals.drivers.local', []);

        $command = $this->buildCommand($url, $options, $config);

        $process = $this->processes->fromShellCommandline(
            $command,
            timeoutSeconds: (int) ($config['timeout_seconds'] ?? 120),
        );

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new AuditException(
                "Lighthouse CLI failed for {$url->label}: " . $e->getMessage(),
                driver: 'local',
                previous: $e,
            );
        }

        $stdout = $process->getOutput();

        try {
            return LighthouseReport::fromLighthouseJson($stdout);
        } catch (JsonException $e) {
            throw new AuditException(
                "Lighthouse CLI returned invalid JSON for {$url->label}",
                driver: 'local',
                previous: $e,
            );
        }
    }

    public function isAvailable(): bool
    {
        $binary = (string) config('vitals.drivers.local.lighthouse_binary', 'lighthouse');

        // If a path is configured, check it exists on disk.
        if (str_contains($binary, '/')) {
            return is_file($binary) && is_executable($binary);
        }

        // Otherwise resolve via $PATH using `command -v`.
        $process = $this->processes->fromShellCommandline(
            sprintf('command -v %s', escapeshellarg($binary)),
            timeoutSeconds: 5,
        );

        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) !== '';
    }

    /**
     * @param array<string, mixed> $config
     */
    private function buildCommand(Url $url, AuditOptions $options, array $config): string
    {
        $appUrl = rtrim((string) config('app.url', 'http://localhost'), '/');
        $target = $appUrl . '/' . ltrim($url->path, '/');

        $chromeFlags = implode(' ', array_map(
            escapeshellarg(...),
            (array) ($config['chrome_flags'] ?? ['--headless', '--no-sandbox']),
        ));

        $headers = json_encode($options->extraHeaders, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $categoryArgs = implode(' ', array_map(
            static fn (string $cat): string => '--only-categories=' . str_replace('_', '-', $cat),
            $options->categories,
        ));

        // $options->device is a Device enum ('mobile'|'desktop') — safe without quoting.
        $device = $options->device->value;

        return sprintf(
            '%s %s --output=json --output-path=stdout --quiet --chrome-flags=%s --extra-headers=%s --form-factor=%s --throttling-method=simulate %s',
            escapeshellcmd((string) ($config['lighthouse_binary'] ?? 'lighthouse')),
            escapeshellarg($target),
            escapeshellarg($chromeFlags),
            escapeshellarg($headers),
            $device,
            $categoryArgs,
        );
    }
}
