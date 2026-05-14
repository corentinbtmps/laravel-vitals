<?php

declare(strict_types=1);

use LaravelVitals\Enums\Severity;

it('has the three expected cases', function (): void {
    expect(Severity::cases())->toHaveCount(3)
        ->and(Severity::Critical->value)->toBe('critical')
        ->and(Severity::Warning->value)->toBe('warning')
        ->and(Severity::Info->value)->toBe('info');
});

it('containerClasses returns correct classes for each case', function (): void {
    expect(Severity::Critical->containerClasses())->toContain('border-accent-200')
        ->and(Severity::Critical->containerClasses())->toContain('bg-accent-50/30')
        ->and(Severity::Warning->containerClasses())->toContain('border-amber-200')
        ->and(Severity::Warning->containerClasses())->toContain('bg-amber-50/30')
        ->and(Severity::Info->containerClasses())->toContain('border-sky-200')
        ->and(Severity::Info->containerClasses())->toContain('bg-sky-50/30');
});

it('dotBackground returns correct class for each case', function (): void {
    expect(Severity::Critical->dotBackground())->toBe('bg-accent-500')
        ->and(Severity::Warning->dotBackground())->toBe('bg-amber-500')
        ->and(Severity::Info->dotBackground())->toBe('bg-sky-500');
});

it('fluxBadgeColor returns correct color for each case', function (): void {
    expect(Severity::Critical->fluxBadgeColor())->toBe('rose')
        ->and(Severity::Warning->fluxBadgeColor())->toBe('amber')
        ->and(Severity::Info->fluxBadgeColor())->toBe('sky');
});

it('fluxCalloutVariant returns correct variant for each case', function (): void {
    expect(Severity::Critical->fluxCalloutVariant())->toBe('danger')
        ->and(Severity::Warning->fluxCalloutVariant())->toBe('warning')
        ->and(Severity::Info->fluxCalloutVariant())->toBe('secondary');
});

it('fluxCalloutIcon returns correct icon for each case', function (): void {
    expect(Severity::Critical->fluxCalloutIcon())->toBe('exclamation-circle')
        ->and(Severity::Warning->fluxCalloutIcon())->toBe('exclamation-triangle')
        ->and(Severity::Info->fluxCalloutIcon())->toBe('information-circle');
});

it('iconTextColor returns correct class for each case', function (): void {
    expect(Severity::Critical->iconTextColor())->toBe('text-accent-500')
        ->and(Severity::Warning->iconTextColor())->toBe('text-amber-500')
        ->and(Severity::Info->iconTextColor())->toBe('text-sky-500');
});

it('fromString returns correct case for known values', function (): void {
    expect(Severity::fromString('critical'))->toBe(Severity::Critical)
        ->and(Severity::fromString('warning'))->toBe(Severity::Warning)
        ->and(Severity::fromString('info'))->toBe(Severity::Info);
});

it('fromString falls back to Info for unknown values', function (): void {
    expect(Severity::fromString('unknown'))->toBe(Severity::Info)
        ->and(Severity::fromString(''))->toBe(Severity::Info);
});

it('label returns a non-empty translated string', function (): void {
    expect(Severity::Critical->label())->not->toBeEmpty()
        ->and(Severity::Warning->label())->not->toBeEmpty()
        ->and(Severity::Info->label())->not->toBeEmpty();
});

it('label returns English strings with the default locale', function (): void {
    \App::setLocale('en');

    expect(Severity::Critical->label())->toBe('Critical')
        ->and(Severity::Warning->label())->toBe('Warning')
        ->and(Severity::Info->label())->toBe('Info');
});

it('label returns French strings with the fr locale', function (): void {
    \App::setLocale('fr');

    expect(Severity::Critical->label())->toBe('Critique')
        ->and(Severity::Warning->label())->toBe('Avertissement')
        ->and(Severity::Info->label())->toBe('Info');

    \App::setLocale('en');
});
