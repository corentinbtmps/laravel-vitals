<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * Single source of truth for severity → Tailwind class mappings.
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
        return match ($severity) {
            'critical' => ['border-accent-200', 'dark:border-accent-900/40', 'bg-accent-50/30', 'dark:bg-accent-900/5'],
            'warning'  => ['border-amber-200',  'dark:border-amber-900/40',  'bg-amber-50/30',  'dark:bg-amber-900/5'],
            default    => ['border-sky-200',    'dark:border-sky-900/40',    'bg-sky-50/30',    'dark:bg-sky-900/5'],
        };
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
        return match ($severity) {
            'critical' => 'bg-accent-500',
            'warning'  => 'bg-amber-500',
            default    => 'bg-sky-500',
        };
    }

    /**
     * Flux badge color for a severity level.
     * Flux only supports standard Tailwind color names (not custom aliases).
     */
    public static function fluxBadgeColor(string $severity): string
    {
        return match ($severity) {
            'critical' => 'rose',
            'warning'  => 'amber',
            default    => 'sky',
        };
    }

    /**
     * Flux callout variant for a severity level.
     */
    public static function fluxCalloutVariant(string $severity): string
    {
        return match ($severity) {
            'critical' => 'danger',
            'warning'  => 'warning',
            default    => 'secondary',
        };
    }

    /**
     * Flux icon name for a severity level.
     */
    public static function fluxCalloutIcon(string $severity): string
    {
        return match ($severity) {
            'critical' => 'exclamation-circle',
            'warning'  => 'exclamation-triangle',
            default    => 'information-circle',
        };
    }

    /**
     * Icon text color class for a severity level.
     */
    public static function iconTextColor(string $severity): string
    {
        return match ($severity) {
            'critical' => 'text-accent-500',
            'warning'  => 'text-amber-500',
            default    => 'text-sky-500',
        };
    }
}
