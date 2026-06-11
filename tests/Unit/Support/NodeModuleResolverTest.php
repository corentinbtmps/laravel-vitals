<?php

declare(strict_types=1);

use LaravelVitals\Support\NodeModuleResolver;

beforeEach(function (): void {
    $this->base = sys_get_temp_dir() . '/vitals-nmr-' . uniqid();
    mkdir($this->base . '/a/b/c', 0777, true);
    $this->start = $this->base . '/a/b/c';
});

afterEach(function (): void {
    $rm = function (string $dir) use (&$rm): void {
        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . '/' . $entry;
            is_dir($path) ? $rm($path) : unlink($path);
        }
        rmdir($dir);
    };

    if (is_dir($this->base)) {
        $rm($this->base);
    }
});

it('resolves a package installed in an ancestor node_modules', function (): void {
    mkdir($this->base . '/node_modules/playwright', 0777, true);

    expect(NodeModuleResolver::isInstalled($this->start, 'playwright'))->toBeTrue();
});

it('resolves the nearest node_modules while walking upward', function (): void {
    mkdir($this->base . '/a/b/node_modules/playwright', 0777, true);

    expect(NodeModuleResolver::isInstalled($this->start, 'playwright'))->toBeTrue();
});

it('returns false when the package is absent up to the filesystem root', function (): void {
    expect(NodeModuleResolver::isInstalled($this->start, 'definitely-not-installed-xyz'))->toBeFalse();
});

it('requires every package to be present', function (): void {
    mkdir($this->base . '/node_modules/playwright', 0777, true);

    expect(NodeModuleResolver::allInstalled($this->start, ['playwright']))->toBeTrue()
        ->and(NodeModuleResolver::allInstalled($this->start, ['playwright', 'playwright-lighthouse']))->toBeFalse();

    mkdir($this->base . '/node_modules/playwright-lighthouse', 0777, true);

    expect(NodeModuleResolver::allInstalled($this->start, ['playwright', 'playwright-lighthouse']))->toBeTrue();
});
