<?php

declare(strict_types=1);

namespace LaravelVitals\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton row representing the package's installation state.
 *
 * Used by the spatie/laravel-onboard integration (when installed) to track
 * which onboarding steps have been completed and whether the user has
 * dismissed the onboarding flow.
 *
 * @property int $id
 * @property \Carbon\Carbon|null $onboarding_dismissed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class VitalsInstallation extends Model
{
    /** @var array<string, string> */
    protected $casts = [
        'onboarding_dismissed_at' => 'datetime',
    ];

    /** @var list<string> */
    protected $guarded = [];

    public function getTable(): string
    {
        return 'vitals_installations';
    }

    /**
     * Returns the singleton installation row, creating it on first access.
     */
    public static function singleton(): self
    {
        /** @var self */
        return static::firstOrCreate([], []);
    }

    public function dismissOnboarding(): void
    {
        $this->onboarding_dismissed_at = now();
        $this->save();
    }

    public function onboardingDismissed(): bool
    {
        return $this->onboarding_dismissed_at !== null;
    }
}
