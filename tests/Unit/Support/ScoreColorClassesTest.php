<?php

declare(strict_types=1);

use LaravelVitals\Support\ScoreColorClasses;

it('badge returns emerald classes for score >= 90', function (): void {
    $classes = ScoreColorClasses::badge(95);

    expect($classes)->toContain('bg-emerald-50')
        ->and($classes)->toContain('text-emerald-700');
});

it('badge returns amber classes for score 70-89', function (): void {
    $classes = ScoreColorClasses::badge(75);

    expect($classes)->toContain('bg-amber-50')
        ->and($classes)->toContain('text-amber-700');
});

it('badge returns accent classes for score < 70', function (): void {
    $classes = ScoreColorClasses::badge(50);

    expect($classes)->toContain('bg-accent-50')
        ->and($classes)->toContain('text-accent-700');
});

it('badge returns ink classes for null score', function (): void {
    $classes = ScoreColorClasses::badge(null);

    expect($classes)->toContain('bg-ink-50')
        ->and($classes)->toContain('text-ink-700');
});

it('headlineText returns correct class for each score range', function (): void {
    expect(ScoreColorClasses::headlineText(90))->toBe('text-emerald-500')
        ->and(ScoreColorClasses::headlineText(70))->toBe('text-amber-500')
        ->and(ScoreColorClasses::headlineText(50))->toBe('text-accent-500')
        ->and(ScoreColorClasses::headlineText(null))->toBe('text-ink-400');
});

it('dot returns correct class for each score range', function (): void {
    expect(ScoreColorClasses::dot(95))->toBe('bg-emerald-400')
        ->and(ScoreColorClasses::dot(75))->toBe('bg-amber-400')
        ->and(ScoreColorClasses::dot(50))->toBe('bg-accent-400')
        ->and(ScoreColorClasses::dot(null))->toBe('bg-ink-400');
});

it('topBar returns correct class for each score range', function (): void {
    expect(ScoreColorClasses::topBar(90))->toBe('bg-emerald-500')
        ->and(ScoreColorClasses::topBar(70))->toBe('bg-amber-500')
        ->and(ScoreColorClasses::topBar(50))->toBe('bg-accent-500')
        ->and(ScoreColorClasses::topBar(null))->toBe('bg-ink-400');
});

it('avatar returns all four classes for a score', function (): void {
    $classes = ScoreColorClasses::avatar(95);

    expect($classes)->toHaveCount(4)
        ->and($classes)->toContain('bg-emerald-100')
        ->and($classes)->toContain('dark:bg-emerald-900/30')
        ->and($classes)->toContain('text-emerald-700')
        ->and($classes)->toContain('dark:text-emerald-300');
});
