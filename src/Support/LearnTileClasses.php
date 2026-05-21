<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * Static Tailwind class sets for the Learn page category tiles.
 *
 * The tile colors are fixed per category (accent/emerald/sky/violet) and
 * are used in 4+ places per tile. All strings are static literals so Tailwind
 * 4's content scanner detects them without @source inline() workarounds.
 */
final class LearnTileClasses
{
    /**
     * Persistent tile background tint (matches audit-detail CWV card style).
     *
     * @return list<string>
     */
    public static function tileBg(string $color): array
    {
        return match ($color) {
            'emerald' => ['bg-emerald-50/40', 'dark:bg-emerald-900/10'],
            'sky'     => ['bg-sky-50/40',     'dark:bg-sky-900/10'],
            'violet'  => ['bg-violet-50/40',  'dark:bg-violet-900/10'],
            default   => ['bg-accent-50/40',  'dark:bg-accent-900/10'],
        };
    }

    /**
     * Persistent tile border (matches audit-detail CWV card style).
     *
     * @return list<string>
     */
    public static function tileBorder(string $color): array
    {
        return match ($color) {
            'emerald' => ['border-emerald-200', 'dark:border-emerald-900/40'],
            'sky'     => ['border-sky-200',     'dark:border-sky-900/40'],
            'violet'  => ['border-violet-200',  'dark:border-violet-900/40'],
            default   => ['border-accent-200',  'dark:border-accent-900/40'],
        };
    }

    /**
     * Tint that intensifies on hover.
     *
     * @return list<string>
     */
    public static function hoverBg(string $color): array
    {
        return match ($color) {
            'emerald' => ['hover:bg-emerald-100/50', 'dark:hover:bg-emerald-900/20'],
            'sky'     => ['hover:bg-sky-100/50',     'dark:hover:bg-sky-900/20'],
            'violet'  => ['hover:bg-violet-100/50',  'dark:hover:bg-violet-900/20'],
            default   => ['hover:bg-accent-100/50',  'dark:hover:bg-accent-900/20'],
        };
    }

    /**
     * Returns the icon background classes for a tile's icon container.
     *
     * @return list<string>
     */
    public static function iconBg(string $color): array
    {
        return match ($color) {
            'emerald' => ['bg-emerald-100', 'dark:bg-emerald-900/30'],
            'sky'     => ['bg-sky-100',     'dark:bg-sky-900/30'],
            'violet'  => ['bg-violet-100',  'dark:bg-violet-900/30'],
            default   => ['bg-accent-100',  'dark:bg-accent-900/30'],
        };
    }

    /**
     * Returns the icon text color classes.
     *
     * @return list<string>
     */
    public static function iconText(string $color): array
    {
        return match ($color) {
            'emerald' => ['text-emerald-600', 'dark:text-emerald-400'],
            'sky'     => ['text-sky-600',     'dark:text-sky-400'],
            'violet'  => ['text-violet-600',  'dark:text-violet-400'],
            default   => ['text-accent-600',  'dark:text-accent-400'],
        };
    }

    /**
     * Returns the active count text color classes.
     *
     * @return list<string>
     */
    public static function countText(string $color): array
    {
        return match ($color) {
            'emerald' => ['text-emerald-600', 'dark:text-emerald-400'],
            'sky'     => ['text-sky-600',     'dark:text-sky-400'],
            'violet'  => ['text-violet-600',  'dark:text-violet-400'],
            default   => ['text-accent-600',  'dark:text-accent-400'],
        };
    }

    /**
     * Returns the group-hover text color classes for the tile heading.
     *
     * @return list<string>
     */
    public static function groupHoverText(string $color): array
    {
        return match ($color) {
            'emerald' => ['group-hover:text-emerald-700', 'dark:group-hover:text-emerald-300'],
            'sky'     => ['group-hover:text-sky-700',     'dark:group-hover:text-sky-300'],
            'violet'  => ['group-hover:text-violet-700',  'dark:group-hover:text-violet-300'],
            default   => ['group-hover:text-accent-700',  'dark:group-hover:text-accent-300'],
        };
    }
}
