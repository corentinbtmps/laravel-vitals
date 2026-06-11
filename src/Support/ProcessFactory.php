<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

use Symfony\Component\Process\Process;

/**
 * Builds Symfony Process instances. Exists so drivers can be tested by
 * substituting a fake factory that returns pre-configured fake processes.
 */
class ProcessFactory
{
    /**
     * @param array<string, string|\Stringable|false>|null $env
     */
    public function fromShellCommandline(
        string $command,
        ?string $cwd = null,
        ?array $env = null,
        ?int $timeoutSeconds = null,
    ): Process {
        $process = Process::fromShellCommandline($command, $cwd, $env);

        if ($timeoutSeconds !== null) {
            $process->setTimeout((float) $timeoutSeconds);
        }

        return $process;
    }
}
