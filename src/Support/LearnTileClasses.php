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
     * Returns all Tailwind classes for a tile's hover border.
     */
    public static function hoverBorder(string $color): string
    {
        return match ($color) {
            'emerald' => 'hover:border-emerald-500/40',
            'sky'     => 'hover:border-sky-500/40',
            'violet'  => 'hover:border-violet-500/40',
            default   => 'hover:border-accent-500/40',
        };
    }

    /**
     * Returns all Tailwind classes for a tile's hover background.
     *
     * @return list<string>
     */
    public static function hoverBg(string $color): array
    {
        return match ($color) {
            'emerald' => ['hover:bg-emerald-50/30', 'dark:hover:bg-emerald-900/10'],
            'sky'     => ['hover:bg-sky-50/30',     'dark:hover:bg-sky-900/10'],
            'violet'  => ['hover:bg-violet-50/30',  'dark:hover:bg-violet-900/10'],
            default   => ['hover:bg-accent-50/30',  'dark:hover:bg-accent-900/10'],
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
