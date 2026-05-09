<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

// The hook command locates .git by walking up from getcwd().
// In tests we need a real .git/hooks dir — use the package's own .git.

beforeEach(function (): void {
    // Use a temp directory with a fake .git/hooks so tests don't mess with the real repo.
    $this->tempDir = sys_get_temp_dir() . '/vitals-hook-test-' . uniqid();
    mkdir($this->tempDir . '/.git/hooks', 0755, true);

    // Point getcwd() stub — we can't easily override getcwd(), so instead we
    // create a small wrapper. Since InstallHookCommand uses getcwd() directly,
    // we'll chdir into the temp dir and restore afterward.
    $this->originalCwd = getcwd();
    chdir($this->tempDir);
});

afterEach(function (): void {
    chdir((string) $this->originalCwd);
    File::deleteDirectory($this->tempDir);
});

it('installs a pre-commit hook file', function (): void {
    $this->artisan('vitals:install-hook')
        ->expectsOutputToContain('pre-commit hook installed')
        ->assertSuccessful();

    expect(file_exists($this->tempDir . '/.git/hooks/pre-commit'))->toBeTrue();
});

it('installs a pre-push hook when --type=pre-push is passed', function (): void {
    $this->artisan('vitals:install-hook', ['--type' => 'pre-push'])
        ->assertSuccessful();

    expect(file_exists($this->tempDir . '/.git/hooks/pre-push'))->toBeTrue();
});

it('makes the hook file executable', function (): void {
    $this->artisan('vitals:install-hook')->assertSuccessful();

    $hookPath = $this->tempDir . '/.git/hooks/pre-commit';
    $perms    = fileperms($hookPath) & 0o777;

    // Owner execute bit should be set (0755 = 493 decimal).
    expect($perms & 0o100)->toBeGreaterThan(0);
});

it('hook file contains the vitals:doctor command', function (): void {
    $this->artisan('vitals:install-hook')->assertSuccessful();

    $content = (string) file_get_contents($this->tempDir . '/.git/hooks/pre-commit');

    expect($content)->toContain('vitals:doctor --quiet');
});

it('hook file contains the laravel-vitals marker', function (): void {
    $this->artisan('vitals:install-hook')->assertSuccessful();

    $content = (string) file_get_contents($this->tempDir . '/.git/hooks/pre-commit');

    expect($content)->toContain('Installed by laravel-vitals');
});

it('backs up an existing hook before overwriting', function (): void {
    $hookPath = $this->tempDir . '/.git/hooks/pre-commit';

    // Write an existing hook that was NOT installed by laravel-vitals.
    file_put_contents($hookPath, "#!/bin/bash\necho 'existing hook'");

    $this->artisan('vitals:install-hook')
        ->expectsOutputToContain('backed up')
        ->assertSuccessful();

    // A backup file should exist.
    $backups = glob($hookPath . '.backup-*') ?: [];
    expect(count($backups))->toBeGreaterThan(0);

    // The backup should contain the original content.
    $backupContent = (string) file_get_contents($backups[0]);
    expect($backupContent)->toContain('existing hook');
});

it('does not back up its own previously installed hook', function (): void {
    $hookPath = $this->tempDir . '/.git/hooks/pre-commit';

    // Write a hook as if already installed by laravel-vitals.
    file_put_contents($hookPath, "#!/bin/bash\n# Installed by laravel-vitals\nphp artisan vitals:doctor --quiet\n");

    $this->artisan('vitals:install-hook')->assertSuccessful();

    $backups = glob($hookPath . '.backup-*') ?: [];
    expect(count($backups))->toBe(0);
});

it('uninstalls the hook when --uninstall is passed', function (): void {
    $hookPath = $this->tempDir . '/.git/hooks/pre-commit';

    // Install first.
    $this->artisan('vitals:install-hook')->assertSuccessful();
    expect(file_exists($hookPath))->toBeTrue();

    // Now uninstall.
    $this->artisan('vitals:install-hook', ['--uninstall' => true])
        ->assertSuccessful();

    expect(file_exists($hookPath))->toBeFalse();
});

it('restores backup when uninstalling', function (): void {
    $hookPath = $this->tempDir . '/.git/hooks/pre-commit';

    // Write an existing hook and install over it (creates backup).
    file_put_contents($hookPath, "#!/bin/bash\necho 'original'");
    $this->artisan('vitals:install-hook')->assertSuccessful();

    // Uninstall should restore the backup.
    $this->artisan('vitals:install-hook', ['--uninstall' => true])->assertSuccessful();

    $content = (string) file_get_contents($hookPath);
    expect($content)->toContain('original');
});

it('returns failure for invalid hook type', function (): void {
    $this->artisan('vitals:install-hook', ['--type' => 'post-merge'])
        ->assertFailed();
});
