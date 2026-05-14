<?php

declare(strict_types=1);

namespace LaravelVitals\Enums;

/**
 * Core Web Vitals / Lighthouse score rating level.
 *
 * Mirrors Google's rating thresholds used in Lighthouse reports.
 */
enum ScoreLevel: string
{
    case Good           = 'good';
    case NeedsImprovement = 'needs-improvement';
    case Poor           = 'poor';

    /**
     * Construct from a Lighthouse numeric score (0–100).
     */
    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 90 => self::Good,
            $score >= 50 => self::NeedsImprovement,
            default      => self::Poor,
        };
    }

    /**
     * Translated human label.
     */
    public function label(): string
    {
        return __('vitals::vitals.score_level.' . $this->value);
    }

    /**
     * Flux badge color for this rating.
     */
    public function fluxBadgeColor(): string
    {
        return match ($this) {
            self::Good             => 'emerald',
            self::NeedsImprovement => 'amber',
            self::Poor             => 'rose',
        };
    }

    /**
     * Tailwind text color class for this rating.
     */
    public function textColor(): string
    {
        return match ($this) {
            self::Good             => 'text-emerald-600',
            self::NeedsImprovement => 'text-amber-600',
            self::Poor             => 'text-accent-600',
        };
    }
}
