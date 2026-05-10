<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use LaravelVitals\Models\Url;

it('exits with code 75 when the same url is already being audited', function (): void {
    $url = Url::create(['label' => 'home-ratelimit', 'path' => '/']);

    // Manually acquire the lock to simulate a concurrent audit.
    $lock = Cache::lock("vitals:audit:{$url->id}", 60);
    $lock->get();

    try {
        $this->artisan('vitals:audit', ['label' => 'home-ratelimit'])
            ->assertExitCode(75);
    } finally {
        $lock->release();
    }
});

it('bypasses the lock when --force is passed', function (): void {
    $url = Url::create(['label' => 'home-force', 'path' => '/']);

    // Manually hold the lock.
    $lock = Cache::lock("vitals:audit:{$url->id}", 60);
    $lock->get();

    try {
        // With --force the command should not return 75.
        // It may fail for other reasons (no driver) but not because of the lock.
        $exitCode = \Artisan::call('vitals:audit', ['label' => 'home-force', '--force' => true]);
        expect($exitCode)->not->toBe(75);
    } finally {
        $lock->release();
    }
});

it('releases the lock after an audit attempt (even if it fails)', function (): void {
    $url = Url::create(['label' => 'home-lock-release', 'path' => '/']);

    // Run an audit — it may fail due to no real driver but the lock must be released.
    $this->artisan('vitals:audit', ['label' => 'home-lock-release']);

    // The lock should be free now — we can acquire it.
    $lock = Cache::lock("vitals:audit:{$url->id}", 60);
    $acquired = $lock->get();
    expect($acquired)->toBeTrue();
    $lock->release();
});
