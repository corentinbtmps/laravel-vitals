<?php

declare(strict_types=1);

namespace LaravelVitals\Enums;

/**
 * Severity levels for Vitals recommendations.
 *
 * All Tailwind class strings are static literals so the content scanner detects
 * them without any @source inline() workarounds.
 */
enum Severity: string
{
    case Critical = 'critical';
    case Warning  = 'warning';
    case Info     = 'info';

    /**
     * Tailwind classes for a recommendation severity card container.
     * Includes border + background in both light and dark modes.
     *
     * @return list<string>
     */
    public function containerClasses(): array
    {
        return match ($this) {
            self::Critical => ['border-accent-200', 'dark:border-accent-900/40', 'bg-accent-50/30', 'dark:bg-accent-900/5'],
            self::Warning  => ['border-amber-200',  'dark:border-amber-900/40',  'bg-amber-50/30',  'dark:bg-amber-900/5'],
            self::Info     => ['border-sky-200',    'dark:border-sky-900/40',    'bg-sky-50/30',    'dark:bg-sky-900/5'],
        };
    }

    /**
     * Background class for a small circular severity dot indicator.
     */
    public function dotBackground(): string
    {
        return match ($this) {
            self::Critical => 'bg-accent-500',
            self::Warning  => 'bg-amber-500',
            self::Info     => 'bg-sky-500',
        };
    }

    /**
     * Flux badge color for a severity level.
     * Flux only supports standard Tailwind color names (not custom aliases).
     */
    public function fluxBadgeColor(): string
    {
        return match ($this) {
            self::Critical => 'rose',
            self::Warning  => 'amber',
            self::Info     => 'sky',
        };
    }

    /**
     * Flux callout variant for a severity level.
     */
    public function fluxCalloutVariant(): string
    {
        return match ($this) {
            self::Critical => 'danger',
            self::Warning  => 'warning',
            self::Info     => 'secondary',
        };
    }

    /**
     * Flux icon name for a severity level.
     */
    public function fluxCalloutIcon(): string
    {
        return match ($this) {
            self::Critical => 'exclamation-circle',
            self::Warning  => 'exclamation-triangle',
            self::Info     => 'information-circle',
        };
    }

    /**
     * Icon text color class for a severity level.
     */
    public function iconTextColor(): string
    {
        return match ($this) {
            self::Critical => 'text-accent-500',
            self::Warning  => 'text-amber-500',
            self::Info     => 'text-sky-500',
        };
    }

    /**
     * Translated human label for display.
     */
    public function label(): string
    {
        return __('vitals::vitals.severity.' . $this->value);
    }

    /**
     * Construct from a raw string, falling back to Info for unknown values.
     */
    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::Info;
    }
}
