<?php

declare(strict_types=1);

beforeEach(function (): void {
    $this->tmp = sys_get_temp_dir() . '/vitals-boost-' . uniqid();
    mkdir($this->tmp);
    $this->app->setBasePath($this->tmp);
});

afterEach(function (): void {
    if (property_exists($this, 'tmp') && $this->tmp !== null && is_dir($this->tmp)) {
        \Illuminate\Support\Facades\File::deleteDirectory($this->tmp);
    }
});

it('vitals:boost:install publishes both AI artefacts', function (): void {
    $this->artisan('vitals:boost:install', ['--force' => true])
        ->assertSuccessful();

    expect(file_exists($this->tmp . '/.ai/guidelines/vitals.blade.php'))->toBeTrue()
        ->and(file_exists($this->tmp . '/.claude/skills/laravel-vitals/SKILL.md'))->toBeTrue();
});

it('vitals:boost:diff reports identical when files match', function (): void {
    $this->artisan('vitals:boost:install', ['--force' => true])->assertSuccessful();

    $this->artisan('vitals:boost:diff')
        ->expectsOutputToContain('identical')
        ->assertSuccessful();
});

it('vitals:boost:diff reports differ when content drifts', function (): void {
    $this->artisan('vitals:boost:install', ['--force' => true])->assertSuccessful();

    file_put_contents($this->tmp . '/.ai/guidelines/vitals.blade.php', 'modified by user');

    $this->artisan('vitals:boost:diff')
        ->expectsOutputToContain('differ')
        ->assertSuccessful();
});
