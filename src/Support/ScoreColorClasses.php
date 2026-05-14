<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * Tailwind class sets for score-based color coding (0–100 scale).
 *
 * All strings are static literals so Tailwind 4's content scanner detects them
 * without any @source inline() workarounds.
 *
 * Color mapping (mirrors Health::colorForScore):
 *   score >= 90  → emerald
 *   score >= 70  → amber
 *   else          → accent (rose)
 *   null          → ink (neutral)
 */
final class ScoreColorClasses
{
    /**
     * Badge background + text classes for a score pill
     * (e.g. the rounded letter-grade badge in tables).
     *
     * @return list<string>
     */
    public static function badge(?int $score): array
    {
        return match (Health::colorForScore($score)) {
            'emerald' => ['bg-emerald-50', 'dark:bg-emerald-900/30', 'text-emerald-700', 'dark:text-emerald-300'],
            'amber'   => ['bg-amber-50',   'dark:bg-amber-900/30',   'text-amber-700',   'dark:text-amber-300'],
            'accent'  => ['bg-accent-50',  'dark:bg-accent-900/30',  'text-accent-700',  'dark:text-accent-300'],
            default   => ['bg-ink-50',     'dark:bg-ink-900/30',     'text-ink-700',     'dark:text-ink-300'],
        };
    }

    /**
     * Text color class for the score value (large headline number).
     */
    public static function headlineText(?int $score): string
    {
        return match (Health::colorForScore($score)) {
            'emerald' => 'text-emerald-500',
            'amber'   => 'text-amber-500',
            'accent'  => 'text-accent-500',
            default   => 'text-ink-400',
        };
    }

    /**
     * Text + inner grade-box bg for audit history table rows.
     *
     * @return array{text: list<string>, box: list<string>}
     */
    public static function historyRow(?int $score): array
    {
        return match (Health::colorForScore($score)) {
            'emerald' => [
                'text' => ['text-emerald-700', 'dark:text-emerald-300'],
                'box'  => ['bg-emerald-100', 'dark:bg-emerald-900/40'],
            ],
            'amber'   => [
                'text' => ['text-amber-700', 'dark:text-amber-300'],
                'box'  => ['bg-amber-100', 'dark:bg-amber-900/40'],
            ],
            'accent'  => [
                'text' => ['text-accent-700', 'dark:text-accent-300'],
                'box'  => ['bg-accent-100', 'dark:bg-accent-900/40'],
            ],
            default   => [
                'text' => ['text-ink-700', 'dark:text-ink-300'],
                'box'  => ['bg-ink-100', 'dark:bg-ink-900/40'],
            ],
        };
    }

    /**
     * Small dot background class (recent audits list, self-check list).
     */
    public static function dot(?int $score): string
    {
        return match (Health::colorForScore($score)) {
            'emerald' => 'bg-emerald-400',
            'amber'   => 'bg-amber-400',
            'accent'  => 'bg-accent-400',
            default   => 'bg-ink-400',
        };
    }

    /**
     * Avatar background + text classes (rounded circle with letter grade).
     *
     * @return list<string>
     */
    public static function avatar(?int $score): array
    {
        return match (Health::colorForScore($score)) {
            'emerald' => ['bg-emerald-100', 'dark:bg-emerald-900/30', 'text-emerald-700', 'dark:text-emerald-300'],
            'amber'   => ['bg-amber-100',   'dark:bg-amber-900/30',   'text-amber-700',   'dark:text-amber-300'],
            'accent'  => ['bg-accent-100',  'dark:bg-accent-900/30',  'text-accent-700',  'dark:text-accent-300'],
            default   => ['bg-ink-100',     'dark:bg-ink-900/30',     'text-ink-700',     'dark:text-ink-300'],
        };
    }

    /**
     * Top-border accent bar color for the score breakdown cards.
     */
    public static function topBar(?int $score): string
    {
        return match (Health::colorForScore($score)) {
            'emerald' => 'bg-emerald-500',
            'amber'   => 'bg-amber-500',
            'accent'  => 'bg-accent-500',
            default   => 'bg-ink-400',
        };
    }

    /**
     * Average-scores dot color for the url-detail panel.
     */
    public static function avgDot(string $color): string
    {
        return match ($color) {
            'emerald' => 'bg-emerald-500',
            'violet'  => 'bg-violet-500',
            'sky'     => 'bg-sky-500',
            default   => 'bg-accent-500',
        };
    }
}
