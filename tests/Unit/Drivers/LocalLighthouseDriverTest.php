<?php

declare(strict_types=1);

use LaravelVitals\Drivers\LocalLighthouseDriver;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\AuditOptions;
use LaravelVitals\Support\ProcessFactory;
use Symfony\Component\Process\Process;

beforeEach(function (): void {
    $this->fixture = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report.json');
});

function makeFakeFactory(string $stdout, int $exitCode = 0): ProcessFactory
{
    return new class($stdout, $exitCode) extends ProcessFactory {
        public string $lastCommand = '';

        public function __construct(private string $stdout, private int $exitCode)
        {
        }

        public function fromShellCommandline(
            string $command,
            ?string $cwd = null,
            ?array $env = null,
            ?int $timeoutSeconds = null,
        ): Process {
            $this->lastCommand = $command;

            // Use a heredoc-style cat trick to write the fixture verbatim, then exit.
            // We base64-encode the stdout to avoid shell-escaping headaches.
            $b64 = base64_encode($this->stdout);
            $script = sprintf('echo %s | base64 -d; exit %d', escapeshellarg($b64), $this->exitCode);

            $process = Process::fromShellCommandline($script, $cwd, $env);

            if ($timeoutSeconds !== null) {
                $process->setTimeout((float) $timeoutSeconds);
            }

            return $process;
        }
    };
}

it('runs the lighthouse CLI and returns a normalised report', function (): void {
    config()->set('vitals.drivers.local', [
        'node_binary'       => 'node',
        'lighthouse_binary' => 'lighthouse',
        'chrome_flags'      => ['--headless', '--no-sandbox'],
        'timeout_seconds'   => 60,
    ]);

    $factory = makeFakeFactory($this->fixture, 0);
    $driver = new LocalLighthouseDriver($factory);

    $url = Url::create(['label' => 'home', 'path' => '/']);

    $report = $driver->audit($url, AuditOptions::default()->withExtraHeader('X-Vitals-Audit-Id', 'abc.def'));

    expect($report->scores['performance'])->toBe(92)
        ->and($report->metrics['lcp_ms'])->toBe(1850.4);

    // The command should reference the lighthouse binary, --output=json, the headers, and form factor.
    expect($factory->lastCommand)
        ->toContain('lighthouse')
        ->and($factory->lastCommand)->toContain('--output=json')
        ->and($factory->lastCommand)->toContain('X-Vitals-Audit-Id')
        ->and($factory->lastCommand)->toContain('--form-factor=mobile');
});

it('throws AuditException when lighthouse exits non-zero', function (): void {
    $factory = makeFakeFactory('boom', 1);
    $driver = new LocalLighthouseDriver($factory);

    $url = Url::create(['label' => 'home', 'path' => '/']);

    expect(fn () => $driver->audit($url, AuditOptions::default()))
        ->toThrow(AuditException::class);
});

it('throws AuditException when lighthouse output is not valid JSON', function (): void {
    $factory = makeFakeFactory('not json', 0);
    $driver = new LocalLighthouseDriver($factory);

    $url = Url::create(['label' => 'home', 'path' => '/']);

    expect(fn () => $driver->audit($url, AuditOptions::default()))
        ->toThrow(AuditException::class);
});

it('reports unavailable when the lighthouse binary cannot be found', function (): void {
    config()->set('vitals.drivers.local.lighthouse_binary', '/nonexistent/lighthouse');

    $driver = new LocalLighthouseDriver(new ProcessFactory());

    expect($driver->isAvailable())->toBeFalse();
});
