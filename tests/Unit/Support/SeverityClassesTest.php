<?php

declare(strict_types=1);

use LaravelVitals\Support\SeverityClasses;

it('container returns accent classes for critical severity', function (): void {
    $classes = SeverityClasses::container('critical');

    expect($classes)->toContain('border-accent-200')
        ->and($classes)->toContain('bg-accent-50/30')
        ->and($classes)->toContain('dark:border-accent-900/40')
        ->and($classes)->toContain('dark:bg-accent-900/5');
});

it('container returns amber classes for warning severity', function (): void {
    $classes = SeverityClasses::container('warning');

    expect($classes)->toContain('border-amber-200')
        ->and($classes)->toContain('bg-amber-50/30');
});

it('container returns sky classes for info (default) severity', function (): void {
    $classes = SeverityClasses::container('info');

    expect($classes)->toContain('border-sky-200')
        ->and($classes)->toContain('bg-sky-50/30');
});

it('container returns sky classes for any unknown severity', function (): void {
    $classes = SeverityClasses::container('unknown');

    expect($classes)->toContain('border-sky-200');
});

it('dotBackground returns correct class for each severity', function (): void {
    expect(SeverityClasses::dotBackground('critical'))->toBe('bg-accent-500')
        ->and(SeverityClasses::dotBackground('warning'))->toBe('bg-amber-500')
        ->and(SeverityClasses::dotBackground('info'))->toBe('bg-sky-500')
        ->and(SeverityClasses::dotBackground('other'))->toBe('bg-sky-500');
});

it('fluxBadgeColor returns correct flux color for each severity', function (): void {
    expect(SeverityClasses::fluxBadgeColor('critical'))->toBe('rose')
        ->and(SeverityClasses::fluxBadgeColor('warning'))->toBe('amber')
        ->and(SeverityClasses::fluxBadgeColor('info'))->toBe('sky')
        ->and(SeverityClasses::fluxBadgeColor('other'))->toBe('sky');
});

it('fluxCalloutVariant returns correct variant for each severity', function (): void {
    expect(SeverityClasses::fluxCalloutVariant('critical'))->toBe('danger')
        ->and(SeverityClasses::fluxCalloutVariant('warning'))->toBe('warning')
        ->and(SeverityClasses::fluxCalloutVariant('info'))->toBe('secondary')
        ->and(SeverityClasses::fluxCalloutVariant('other'))->toBe('secondary');
});

it('fluxCalloutIcon returns correct icon for each severity', function (): void {
    expect(SeverityClasses::fluxCalloutIcon('critical'))->toBe('exclamation-circle')
        ->and(SeverityClasses::fluxCalloutIcon('warning'))->toBe('exclamation-triangle')
        ->and(SeverityClasses::fluxCalloutIcon('info'))->toBe('information-circle')
        ->and(SeverityClasses::fluxCalloutIcon('other'))->toBe('information-circle');
});

it('iconTextColor returns correct text color class for each severity', function (): void {
    expect(SeverityClasses::iconTextColor('critical'))->toBe('text-accent-500')
        ->and(SeverityClasses::iconTextColor('warning'))->toBe('text-amber-500')
        ->and(SeverityClasses::iconTextColor('info'))->toBe('text-sky-500')
        ->and(SeverityClasses::iconTextColor('other'))->toBe('text-sky-500');
});

it('cwvContainer returns emerald classes for good status', function (): void {
    $classes = SeverityClasses::cwvContainer('good');

    expect($classes)->toContain('border-emerald-200')
        ->and($classes)->toContain('bg-emerald-50/40');
});

it('cwvContainer returns accent classes for critical status', function (): void {
    $classes = SeverityClasses::cwvContainer('critical');

    expect($classes)->toContain('border-accent-200')
        ->and($classes)->toContain('bg-accent-50/40');
});
