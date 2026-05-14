<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

use LaravelVitals\Enums\Severity;

/**
 * Single source of truth for severity → Tailwind class mappings.
 *
 * @deprecated Use \LaravelVitals\Enums\Severity instead.
 * This class now delegates to the Severity enum.
 * It is kept for backward compatibility but will be removed in v1.0.0.
 *
 * All strings are static literals so Tailwind 4's content scanner detects
 * them without any @source inline() workarounds.
 */
final class SeverityClasses
{
    /**
     * Tailwind classes for a recommendation severity card container.
     * Includes border + background in both light and dark modes.
     *
     * @return list<string>
     */
    public static function container(string $severity): array
    {
        return Severity::fromString($severity)->containerClasses();
    }

    /**
     * Tailwind classes for the CWV status card container.
     * Uses /60 opacity border + /40 background (audit-detail style).
     *
     * @return list<string>
     */
    public static function cwvContainer(string $severity): array
    {
        return match ($severity) {
            'critical' => ['border-accent-200', 'dark:border-accent-900/40', 'bg-accent-50/40', 'dark:bg-accent-900/10'],
            'warning'  => ['border-amber-200',  'dark:border-amber-900/40',  'bg-amber-50/40',  'dark:bg-amber-900/10'],
            default    => ['border-emerald-200', 'dark:border-emerald-900/40', 'bg-emerald-50/40', 'dark:bg-emerald-900/10'],
        };
    }

    /**
     * Background class for a small circular severity dot indicator.
     */
    public static function dotBackground(string $severity): string
    {
        return Severity::fromString($severity)->dotBackground();
    }

    /**
     * Flux badge color for a severity level.
     * Flux only supports standard Tailwind color names (not custom aliases).
     */
    public static function fluxBadgeColor(string $severity): string
    {
        return Severity::fromString($severity)->fluxBadgeColor();
    }

    /**
     * Flux callout variant for a severity level.
     */
    public static function fluxCalloutVariant(string $severity): string
    {
        return Severity::fromString($severity)->fluxCalloutVariant();
    }

    /**
     * Flux icon name for a severity level.
     */
    public static function fluxCalloutIcon(string $severity): string
    {
        return Severity::fromString($severity)->fluxCalloutIcon();
    }

    /**
     * Icon text color class for a severity level.
     */
    public static function iconTextColor(string $severity): string
    {
        return Severity::fromString($severity)->iconTextColor();
    }
}
