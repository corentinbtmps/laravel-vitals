<?php

declare(strict_types=1);

use LaravelVitals\Enums\Period;

it('has the six expected cases', function (): void {
    expect(Period::cases())->toHaveCount(6)
        ->and(Period::H24->value)->toBe('24h')
        ->and(Period::D7->value)->toBe('7d')
        ->and(Period::D30->value)->toBe('30d')
        ->and(Period::D90->value)->toBe('90d')
        ->and(Period::Y1->value)->toBe('1y')
        ->and(Period::All->value)->toBe('all');
});

it('cutoff returns null for All', function (): void {
    expect(Period::All->cutoff())->toBeNull();
});

it('cutoff returns a Carbon instance for time-bounded periods', function (): void {
    expect(Period::H24->cutoff())->not->toBeNull()
        ->and(Period::D7->cutoff())->not->toBeNull()
        ->and(Period::D30->cutoff())->not->toBeNull()
        ->and(Period::D90->cutoff())->not->toBeNull()
        ->and(Period::Y1->cutoff())->not->toBeNull();
});

it('cutoff returns progressively earlier dates', function (): void {
    $h24 = Period::H24->cutoff();
    $d7  = Period::D7->cutoff();
    $d30 = Period::D30->cutoff();
    $d90 = Period::D90->cutoff();
    $y1  = Period::Y1->cutoff();

    expect($d7->isBefore($h24))->toBeTrue()
        ->and($d30->isBefore($d7))->toBeTrue()
        ->and($d90->isBefore($d30))->toBeTrue()
        ->and($y1->isBefore($d90))->toBeTrue();
});

it('buttonLabel returns short string for time-bounded periods', function (): void {
    expect(Period::H24->buttonLabel())->toBe('24h')
        ->and(Period::D7->buttonLabel())->toBe('7d');
});

it('ordered returns all six periods', function (): void {
    expect(Period::ordered())->toHaveCount(6);
});

it('label returns non-empty translated strings', function (): void {
    foreach (Period::cases() as $period) {
        expect($period->label())->not->toBeEmpty();
    }
});

it('label returns French strings with the fr locale', function (): void {
    \App::setLocale('fr');

    expect(Period::D7->label())->toBe('7 derniers jours')
        ->and(Period::All->label())->toBe('Tout');

    \App::setLocale('en');
});
