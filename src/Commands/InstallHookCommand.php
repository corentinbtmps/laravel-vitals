<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;

/**
 * `php artisan vitals:install-hook`
 *
 * Installs a git hook (pre-commit or pre-push) that runs `vitals:doctor`
 * and aborts the git operation when any check fails.
 *
 * Options:
 *   --type=pre-commit|pre-push  Which hook to install (default: pre-commit)
 *   --uninstall                 Revert the hook (restore backup if present)
 */
final class InstallHookCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:install-hook
        {--type=pre-commit : Hook type: pre-commit or pre-push}
        {--uninstall       : Remove the hook instead of installing it}';

    /** @var string */
    protected $description = 'Install a git hook that runs vitals:doctor before each commit or push.';

    private const HOOK_MARKER = '# Installed by laravel-vitals';

    public function handle(): int
    {
        $type = is_string($this->option('type')) ? $this->option('type') : 'pre-commit';

        if (! in_array($type, ['pre-commit', 'pre-push'], true)) {
            $this->error("Invalid hook type \"{$type}\". Use pre-commit or pre-push.");
            return self::FAILURE;
        }

        $gitDir = $this->locateGitDir();

        if ($gitDir === null) {
            $this->error('No .git directory found. Run this command from inside a git repository.');
            return self::FAILURE;
        }

        $hookPath = $gitDir . '/hooks/' . $type;

        if ($this->option('uninstall')) {
            return $this->uninstall($hookPath, $type);
        }

        return $this->install($hookPath, $type);
    }

    private function install(string $hookPath, string $type): int
    {
        // Backup any existing hook that we didn't install.
        if (file_exists($hookPath)) {
            $content = (string) file_get_contents($hookPath);

            if (! str_contains($content, self::HOOK_MARKER)) {
                $backup = $hookPath . '.backup-' . time();
                copy($hookPath, $backup);
                $this->line("  ↩ Existing hook backed up to: {$backup}");
            } else {
                $this->line("  ↩ Overwriting a previously installed laravel-vitals hook.");
            }
        }

        $hookContent = $this->buildHookContent($type);

        $written = file_put_contents($hookPath, $hookContent);

        if ($written === false) {
            $this->error("Failed to write hook to {$hookPath}.");
            return self::FAILURE;
        }

        chmod($hookPath, 0755);

        $this->info("  ✓ {$type} hook installed at {$hookPath}");
        $this->line("    Runs `php artisan vitals:doctor --quiet` before every {$type}.");
        $this->line("    To remove: php artisan vitals:install-hook --type={$type} --uninstall");

        return self::SUCCESS;
    }

    private function uninstall(string $hookPath, string $type): int
    {
        if (! file_exists($hookPath)) {
            $this->warn("No {$type} hook found at {$hookPath}.");
            return self::SUCCESS;
        }

        $content = (string) file_get_contents($hookPath);

        if (! str_contains($content, self::HOOK_MARKER)) {
            $this->warn("The {$type} hook at {$hookPath} was not installed by laravel-vitals. Leaving it untouched.");
            return self::SUCCESS;
        }

        // Look for a backup to restore.
        $pattern = $hookPath . '.backup-*';
        $backups = glob($pattern) ?: [];

        if (count($backups) > 0) {
            // Restore the most-recent backup.
            rsort($backups);
            $latest = $backups[0];
            copy($latest, $hookPath);
            unlink($latest);
            $this->info("  ✓ Restored previous hook from {$latest}");
        } else {
            unlink($hookPath);
            $this->info("  ✓ {$type} hook removed (no backup to restore).");
        }

        return self::SUCCESS;
    }

    private function buildHookContent(string $type): string
    {
        return <<<BASH
        #!/usr/bin/env bash
        # Installed by laravel-vitals
        # Hook type: {$type}
        # Remove with: php artisan vitals:install-hook --type={$type} --uninstall
        set -e

        php artisan vitals:doctor --quiet
        BASH;
    }

    private function locateGitDir(): ?string
    {
        // Walk up from the current working directory to find .git.
        $dir = (string) getcwd();

        for ($i = 0; $i < 10; $i++) {
            if (is_dir($dir . '/.git')) {
                return $dir . '/.git';
            }

            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }

        return null;
    }
}
