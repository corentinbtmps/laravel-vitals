<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class BoostInstallCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:boost:install {--force : Overwrite existing files}';

    /** @var string */
    protected $description = 'Re-publish Laravel Boost guidelines and the Claude skill.';

    public function handle(Filesystem $files): int
    {
        $base = base_path();
        $packageRoot = dirname(__DIR__, 2);

        $pairs = [
            $packageRoot . '/stubs/ai-guidelines/vitals.blade.php' => $base . '/.ai/guidelines/vitals.blade.php',
            $packageRoot . '/stubs/claude-skills/SKILL.md'         => $base . '/.claude/skills/laravel-vitals/SKILL.md',
        ];

        foreach ($pairs as $src => $dest) {
            if ($files->exists($dest) && ! $this->option('force')) {
                $this->warn("Skipped (exists): {$dest}");
                continue;
            }
            $files->ensureDirectoryExists(dirname($dest));
            $files->copy($src, $dest);
            $this->line("Published: {$dest}");
        }

        return self::SUCCESS;
    }
}
