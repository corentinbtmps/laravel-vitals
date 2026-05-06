<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->tmp = sys_get_temp_dir() . '/vitals-install-' . uniqid();
    mkdir($this->tmp);
    $this->app->setBasePath($this->tmp);
});

afterEach(function (): void {
    if (isset($this->tmp) && is_dir($this->tmp)) {
        File::deleteDirectory($this->tmp);
    }
});

it('publishes the Boost guidelines and Claude skill files when --force is set', function (): void {
    $this->artisan('vitals:install', ['--force' => true])
        ->assertSuccessful();

    expect(file_exists($this->tmp . '/.ai/guidelines/vitals.blade.php'))->toBeTrue()
        ->and(file_exists($this->tmp . '/.claude/skills/laravel-vitals/SKILL.md'))->toBeTrue();
});

it('skips Boost when --no-boost is passed', function (): void {
    $this->artisan('vitals:install', ['--force' => true, '--no-boost' => true])
        ->assertSuccessful();

    expect(file_exists($this->tmp . '/.ai/guidelines/vitals.blade.php'))->toBeFalse();
});

it('skips Claude skill when --no-claude-skill is passed', function (): void {
    $this->artisan('vitals:install', ['--force' => true, '--no-claude-skill' => true])
        ->assertSuccessful();

    expect(file_exists($this->tmp . '/.claude/skills/laravel-vitals/SKILL.md'))->toBeFalse();
});
