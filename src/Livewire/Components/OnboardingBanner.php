<?php

declare(strict_types=1);

namespace LaravelVitals\Livewire\Components;

use Illuminate\Contracts\View\View;
use LaravelVitals\Models\VitalsInstallation;
use Livewire\Component;
use Spatie\Onboard\OnboardingStep;

final class OnboardingBanner extends Component
{
    public function dismiss(): void
    {
        VitalsInstallation::singleton()->dismissOnboarding();
    }

    public function render(): View
    {
        $installation = VitalsInstallation::singleton();

        if ($installation->onboardingDismissed()) {
            return view('vitals::livewire.components.onboarding-banner-empty');
        }

        $manager = $installation->onboarding();

        if ($manager->finished()) {
            return view('vitals::livewire.components.onboarding-banner-empty');
        }

        $steps = $manager->steps();
        $completed = $steps->filter(fn (OnboardingStep $s): bool => $s->complete())->count();

        return view('vitals::livewire.components.onboarding-banner', [
            'steps'      => $steps,
            'percentage' => $manager->percentageCompleted(),
            'completed'  => $completed,
            'total'      => $steps->count(),
            'nextStep'   => $manager->nextUnfinishedStep(),
        ]);
    }
}
