<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Components\OnboardingBanner;
use LaravelVitals\Models\VitalsInstallation;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders empty when onboarding is dismissed', function (): void {
    $installation = VitalsInstallation::singleton();
    $installation->dismissOnboarding();

    Livewire::test(OnboardingBanner::class)
        ->assertDontSee('Get started');
});

it('renders empty when spatie/laravel-onboard is not installed', function (): void {
    // spatie/laravel-onboard is not in dev deps so the Facade class won't exist
    Livewire::test(OnboardingBanner::class)
        ->assertDontSee('Get started');
});

it('persists the dismissed-at timestamp', function (): void {
    $installation = VitalsInstallation::singleton();
    expect($installation->onboarding_dismissed_at)->toBeNull();

    Livewire::test(OnboardingBanner::class)
        ->call('dismiss');

    expect($installation->fresh()->onboarding_dismissed_at)->not->toBeNull();
});
