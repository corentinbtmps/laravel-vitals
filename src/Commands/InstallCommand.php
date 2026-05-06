<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * `php artisan vitals:install`
 *
 * Publishes Boost guidelines and the Claude Code skill into the host project.
 * Use --no-boost / --no-claude-skill to opt out of either.
 */
final class InstallCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:install
        {--no-boost : Do not publish .ai/guidelines/vitals.blade.php}
        {--no-claude-skill : Do not publish .claude/skills/laravel-vitals/SKILL.md}
        {--force : Overwrite existing files without prompting}';

    /** @var string */
    protected $description = 'Install Laravel Vitals AI integration files (Boost + Claude).';

    public function handle(Filesystem $files): int
    {
        $base = base_path();
        $packageRoot = dirname(__DIR__, 2);

        if (! $this->option('no-boost')) {
            $this->publishBoost($files, $packageRoot, $base);
        }

        if (! $this->option('no-claude-skill')) {
            $this->publishClaudeSkill($files, $packageRoot, $base);
        }

        $this->info('Done. Run `php artisan vitals:audit --help` to see available options.');

        return self::SUCCESS;
    }

    private function publishBoost(Filesystem $files, string $packageRoot, string $base): void
    {
        $src  = $packageRoot . '/stubs/ai-guidelines/vitals.blade.php';
        $dest = $base . '/.ai/guidelines/vitals.blade.php';

        if ($files->exists($dest) && ! $this->option('force')) {
            if (! $this->confirm("Publish Boost guidelines to .ai/guidelines/vitals.blade.php?", true)) {
                return;
            }
        }

        $files->ensureDirectoryExists(dirname($dest));
        $files->copy($src, $dest);
        $this->line("Published: {$dest}");
    }

    private function publishClaudeSkill(Filesystem $files, string $packageRoot, string $base): void
    {
        $src  = $packageRoot . '/stubs/claude-skills/SKILL.md';
        $dest = $base . '/.claude/skills/laravel-vitals/SKILL.md';

        if ($files->exists($dest) && ! $this->option('force')) {
            if (! $this->confirm("Publish Claude skill to .claude/skills/laravel-vitals/SKILL.md?", true)) {
                return;
            }
        }

        $files->ensureDirectoryExists(dirname($dest));
        $files->copy($src, $dest);
        $this->line("Published: {$dest}");
    }
}
