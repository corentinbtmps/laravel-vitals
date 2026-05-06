<?php

declare(strict_types=1);

use LaravelVitals\Support\ProcessFactory;
use Symfony\Component\Process\Process;

it('produces a Symfony Process for the given command and timeout', function (): void {
    $factory = new ProcessFactory();

    $process = $factory->fromShellCommandline('echo hello', timeoutSeconds: 5);

    expect($process)->toBeInstanceOf(Process::class)
        ->and($process->getCommandLine())->toContain('echo hello')
        ->and($process->getTimeout())->toBe(5.0);
});
