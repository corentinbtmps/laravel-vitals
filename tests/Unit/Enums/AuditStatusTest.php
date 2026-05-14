<?php

declare(strict_types=1);

use LaravelVitals\Enums\AuditStatus;

it('has the four expected cases', function (): void {
    expect(AuditStatus::cases())->toHaveCount(4)
        ->and(AuditStatus::Pending->value)->toBe('pending')
        ->and(AuditStatus::Running->value)->toBe('running')
        ->and(AuditStatus::Completed->value)->toBe('completed')
        ->and(AuditStatus::Failed->value)->toBe('failed');
});

it('isTerminal returns false for Pending and Running', function (): void {
    expect(AuditStatus::Pending->isTerminal())->toBeFalse()
        ->and(AuditStatus::Running->isTerminal())->toBeFalse();
});

it('isTerminal returns true for Completed and Failed', function (): void {
    expect(AuditStatus::Completed->isTerminal())->toBeTrue()
        ->and(AuditStatus::Failed->isTerminal())->toBeTrue();
});

it('label returns non-empty translated strings', function (): void {
    expect(AuditStatus::Pending->label())->not->toBeEmpty()
        ->and(AuditStatus::Running->label())->not->toBeEmpty()
        ->and(AuditStatus::Completed->label())->not->toBeEmpty()
        ->and(AuditStatus::Failed->label())->not->toBeEmpty();
});

it('label returns French strings with the fr locale', function (): void {
    \App::setLocale('fr');

    expect(AuditStatus::Pending->label())->toBe('En attente')
        ->and(AuditStatus::Completed->label())->toBe('Terminé')
        ->and(AuditStatus::Failed->label())->toBe('Échoué');

    \App::setLocale('en');
});
