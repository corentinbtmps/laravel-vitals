<?php

declare(strict_types=1);

use LaravelVitals\Enums\ScoreLevel;

it('has the three expected cases', function (): void {
    expect(ScoreLevel::cases())->toHaveCount(3)
        ->and(ScoreLevel::Good->value)->toBe('good')
        ->and(ScoreLevel::NeedsImprovement->value)->toBe('needs-improvement')
        ->and(ScoreLevel::Poor->value)->toBe('poor');
});

it('fromScore returns Good for score >= 90', function (): void {
    expect(ScoreLevel::fromScore(90))->toBe(ScoreLevel::Good)
        ->and(ScoreLevel::fromScore(100))->toBe(ScoreLevel::Good)
        ->and(ScoreLevel::fromScore(95))->toBe(ScoreLevel::Good);
});

it('fromScore returns NeedsImprovement for score 50-89', function (): void {
    expect(ScoreLevel::fromScore(89))->toBe(ScoreLevel::NeedsImprovement)
        ->and(ScoreLevel::fromScore(70))->toBe(ScoreLevel::NeedsImprovement)
        ->and(ScoreLevel::fromScore(50))->toBe(ScoreLevel::NeedsImprovement);
});

it('fromScore returns Poor for score < 50', function (): void {
    expect(ScoreLevel::fromScore(49))->toBe(ScoreLevel::Poor)
        ->and(ScoreLevel::fromScore(0))->toBe(ScoreLevel::Poor);
});

it('fluxBadgeColor returns correct color for each case', function (): void {
    expect(ScoreLevel::Good->fluxBadgeColor())->toBe('emerald')
        ->and(ScoreLevel::NeedsImprovement->fluxBadgeColor())->toBe('amber')
        ->and(ScoreLevel::Poor->fluxBadgeColor())->toBe('rose');
});

it('textColor returns correct Tailwind class for each case', function (): void {
    expect(ScoreLevel::Good->textColor())->toBe('text-emerald-600')
        ->and(ScoreLevel::NeedsImprovement->textColor())->toBe('text-amber-600')
        ->and(ScoreLevel::Poor->textColor())->toBe('text-accent-600');
});

it('label returns non-empty translated strings', function (): void {
    expect(ScoreLevel::Good->label())->not->toBeEmpty()
        ->and(ScoreLevel::NeedsImprovement->label())->not->toBeEmpty()
        ->and(ScoreLevel::Poor->label())->not->toBeEmpty();
});

it('label returns French strings with the fr locale', function (): void {
    \App::setLocale('fr');

    expect(ScoreLevel::Good->label())->toBe('Bon')
        ->and(ScoreLevel::NeedsImprovement->label())->toBe('À améliorer')
        ->and(ScoreLevel::Poor->label())->toBe('Mauvais');

    \App::setLocale('en');
});
