<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Components;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\VitalsInstallation;
use Livewire\Component;

final class OnboardingBanner extends Component
{
    public function dismiss(): void
    {
        VitalsInstallation::singleton()->dismissOnboarding();
    }

    public function render(): View
    {
        $installation = VitalsInstallation::singleton();

        // Hide if dismissed
        if ($installation->onboardingDismissed()) {
            return view('vitals::livewire.components.onboarding-banner-empty');
        }

        // Hide if spatie/laravel-onboard is not installed
        if (! class_exists('Spatie\\Onboard\\Facades\\Onboard')) {
            return view('vitals::livewire.components.onboarding-banner-empty');
        }

        return $this->renderWithOnboard($installation);
    }

    /**
     * Called only after confirming spatie/laravel-onboard is installed.
     *
     * PHPStan errors for Spatie\Onboard\* are suppressed in phpstan.neon
     * because the package is an optional dependency.
     */
    private function renderWithOnboard(VitalsInstallation $installation): View
    {
        /** @phpstan-ignore-next-line */
        $manager = \Spatie\Onboard\Facades\Onboard::onboarding($installation);

        /** @phpstan-ignore-next-line */
        if ($manager->finished()) {
            return view('vitals::livewire.components.onboarding-banner-empty');
        }

        /** @phpstan-ignore-next-line */
        $steps = $manager->steps();
        /** @phpstan-ignore-next-line */
        $percentage = $manager->percentageCompleted();
        /** @phpstan-ignore-next-line */
        $finished = $manager->finishedSteps()->count();
        /** @phpstan-ignore-next-line */
        $nextStep = $manager->nextUnfinishedStep();

        return view('vitals::livewire.components.onboarding-banner', [
            'steps'      => $steps,
            'percentage' => $percentage,
            'completed'  => $finished,
            'total'      => $steps->count(),
            'nextStep'   => $nextStep,
        ]);
    }
}
