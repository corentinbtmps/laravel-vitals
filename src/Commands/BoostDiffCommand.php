<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class BoostDiffCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:boost:diff';

    /** @var string */
    protected $description = 'Show whether installed Boost guidelines / Claude skill differ from the package version.';

    public function handle(Filesystem $files): int
    {
        $base = base_path();
        $packageRoot = dirname(__DIR__, 2);

        $pairs = [
            'Boost guidelines' => [
                'src'  => $packageRoot . '/stubs/ai-guidelines/vitals.blade.php',
                'dest' => $base . '/.ai/guidelines/vitals.blade.php',
            ],
            'Claude skill' => [
                'src'  => $packageRoot . '/stubs/claude-skills/SKILL.md',
                'dest' => $base . '/.claude/skills/laravel-vitals/SKILL.md',
            ],
        ];

        foreach ($pairs as $label => $paths) {
            if (! $files->exists($paths['dest'])) {
                $this->line("{$label}: not installed.");
                continue;
            }

            $shipped = (string) $files->get($paths['src']);
            $installed = (string) $files->get($paths['dest']);

            if (hash('sha256', $shipped) === hash('sha256', $installed)) {
                $this->line("{$label}: identical with shipped version.");
            } else {
                $this->line("{$label}: contents differ from shipped version.");
            }
        }

        return self::SUCCESS;
    }
}
