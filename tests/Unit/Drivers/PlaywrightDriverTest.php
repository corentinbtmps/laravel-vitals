<?php

declare(strict_types=1);

use LaravelVitals\Drivers\PlaywrightDriver;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\AuditOptions;
use LaravelVitals\Support\ProcessFactory;
use Symfony\Component\Process\Process;

beforeEach(function (): void {
    $this->fixture = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report.json');

    config()->set('vitals.drivers.playwright', [
        'node_binary'     => 'node',
        'timeout_seconds' => 60,
    ]);
});

function makePlaywrightFakeFactory(string $stdout, int $exitCode = 0): ProcessFactory
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

it('runs the playwright Node script and parses the report', function (): void {
    $factory = makePlaywrightFakeFactory($this->fixture, 0);
    $driver = new PlaywrightDriver($factory);

    $url = Url::create(['label' => 'home', 'path' => '/']);

    $report = $driver->audit($url, AuditOptions::default()->withExtraHeader('X-Vitals-Audit-Id', 'abc.def'));

    expect($report->scores['performance'])->toBe(92)
        ->and($report->metrics['lcp_ms'])->toBe(1850.4);

    expect($factory->lastCommand)
        ->toContain('vitals-playwright.mjs')
        ->and($factory->lastCommand)->toContain('--url=')
        ->and($factory->lastCommand)->toContain('--device=mobile')
        ->and($factory->lastCommand)->toContain('X-Vitals-Audit-Id');
});

it('throws AuditException when the script exits non-zero', function (): void {
    $factory = makePlaywrightFakeFactory('boom', 2);
    $driver = new PlaywrightDriver($factory);

    $url = Url::create(['label' => 'home', 'path' => '/']);

    expect(fn () => $driver->audit($url, AuditOptions::default()))
        ->toThrow(AuditException::class);
});

it('reports unavailable when node binary is missing', function (): void {
    config()->set('vitals.drivers.playwright.node_binary', '/nonexistent/node');

    $driver = new PlaywrightDriver(new ProcessFactory());

    expect($driver->isAvailable())->toBeFalse();
});
