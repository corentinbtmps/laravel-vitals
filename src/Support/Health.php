<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * Stateless helpers for performance status classification.
 */
final class Health
{
    /**
     * Letter grade A–F from a 0–100 score.
     */
    public static function grade(?int $score): string
    {
        if ($score === null) {
            return '?';
        }

        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default      => 'F',
        };
    }

    /**
     * Flux color name corresponding to a score range.
     * Returns one of: emerald, amber, rose, zinc.
     */
    public static function colorForScore(?int $score): string
    {
        if ($score === null) {
            return 'zinc';
        }

        return match (true) {
            $score >= 90 => 'emerald',
            $score >= 70 => 'amber',
            default      => 'rose',
        };
    }

    /**
     * Core Web Vitals threshold classification (Google's official thresholds).
     * Returns one of: good, needs_improvement, poor, unknown.
     */
    public static function cwvStatus(string $metric, ?float $value): string
    {
        if ($value === null) {
            return 'unknown';
        }

        return match ($metric) {
            'lcp_ms' => match (true) {
                $value <= 2500 => 'good',
                $value <= 4000 => 'needs_improvement',
                default        => 'poor',
            },
            'cls' => match (true) {
                $value <= 0.1  => 'good',
                $value <= 0.25 => 'needs_improvement',
                default        => 'poor',
            },
            'inp_ms' => match (true) {
                $value <= 200 => 'good',
                $value <= 500 => 'needs_improvement',
                default       => 'poor',
            },
            'ttfb_ms' => match (true) {
                $value <= 800  => 'good',
                $value <= 1800 => 'needs_improvement',
                default        => 'poor',
            },
            default => 'unknown',
        };
    }

    /**
     * Flux color for a CWV status.
     */
    public static function colorForStatus(string $status): string
    {
        return match ($status) {
            'good'              => 'emerald',
            'needs_improvement' => 'amber',
            'poor'              => 'rose',
            default             => 'zinc',
        };
    }

    /**
     * Flux icon name for a CWV status.
     */
    public static function iconForStatus(string $status): string
    {
        return match ($status) {
            'good'              => 'check-circle',
            'needs_improvement' => 'exclamation-triangle',
            'poor'              => 'exclamation-circle',
            default             => 'question-mark-circle',
        };
    }
}
