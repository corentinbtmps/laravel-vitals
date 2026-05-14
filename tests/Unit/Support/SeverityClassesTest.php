<?php

declare(strict_types=1);

use LaravelVitals\Enums\Severity;

it('containerClasses returns accent classes for critical severity', function (): void {
    $classes = Severity::Critical->containerClasses();

    expect($classes)->toContain('border-accent-200')
        ->and($classes)->toContain('bg-accent-50/30')
        ->and($classes)->toContain('dark:border-accent-900/40')
        ->and($classes)->toContain('dark:bg-accent-900/5');
});

it('containerClasses returns amber classes for warning severity', function (): void {
    $classes = Severity::Warning->containerClasses();

    expect($classes)->toContain('border-amber-200')
        ->and($classes)->toContain('bg-amber-50/30');
});

it('containerClasses returns sky classes for info severity', function (): void {
    $classes = Severity::Info->containerClasses();

    expect($classes)->toContain('border-sky-200')
        ->and($classes)->toContain('bg-sky-50/30');
});

it('fromString falls back to Info for unknown values', function (): void {
    $result = Severity::fromString('unknown');

    expect($result)->toBe(Severity::Info)
        ->and($result->containerClasses())->toContain('border-sky-200');
});

it('dotBackground returns correct class for each severity', function (): void {
    expect(Severity::Critical->dotBackground())->toBe('bg-accent-500')
        ->and(Severity::Warning->dotBackground())->toBe('bg-amber-500')
        ->and(Severity::Info->dotBackground())->toBe('bg-sky-500');
});

it('fluxBadgeColor returns correct flux color for each severity', function (): void {
    expect(Severity::Critical->fluxBadgeColor())->toBe('rose')
        ->and(Severity::Warning->fluxBadgeColor())->toBe('amber')
        ->and(Severity::Info->fluxBadgeColor())->toBe('sky');
});

it('fluxCalloutVariant returns correct variant for each severity', function (): void {
    expect(Severity::Critical->fluxCalloutVariant())->toBe('danger')
        ->and(Severity::Warning->fluxCalloutVariant())->toBe('warning')
        ->and(Severity::Info->fluxCalloutVariant())->toBe('secondary');
});

it('fluxCalloutIcon returns correct icon for each severity', function (): void {
    expect(Severity::Critical->fluxCalloutIcon())->toBe('exclamation-circle')
        ->and(Severity::Warning->fluxCalloutIcon())->toBe('exclamation-triangle')
        ->and(Severity::Info->fluxCalloutIcon())->toBe('information-circle');
});

it('iconTextColor returns correct text color class for each severity', function (): void {
    expect(Severity::Critical->iconTextColor())->toBe('text-accent-500')
        ->and(Severity::Warning->iconTextColor())->toBe('text-amber-500')
        ->and(Severity::Info->iconTextColor())->toBe('text-sky-500');
});
