<?php

declare(strict_types=1);

use LaravelVitals\Support\Health;

it('grades scores correctly', function (): void {
    expect(Health::grade(95))->toBe('A')
        ->and(Health::grade(85))->toBe('B')
        ->and(Health::grade(75))->toBe('C')
        ->and(Health::grade(65))->toBe('D')
        ->and(Health::grade(50))->toBe('F')
        ->and(Health::grade(null))->toBe('?');
});

it('maps scores to tailwind colors', function (): void {
    expect(Health::colorForScore(95))->toBe('emerald')
        ->and(Health::colorForScore(80))->toBe('amber')
        ->and(Health::colorForScore(50))->toBe('accent')
        ->and(Health::colorForScore(null))->toBe('ink');
});

it('classifies LCP per Google thresholds', function (): void {
    expect(Health::cwvStatus('lcp_ms', 2000.0))->toBe('good')
        ->and(Health::cwvStatus('lcp_ms', 3500.0))->toBe('needs_improvement')
        ->and(Health::cwvStatus('lcp_ms', 5000.0))->toBe('poor')
        ->and(Health::cwvStatus('lcp_ms', null))->toBe('unknown');
});

it('classifies CLS and INP', function (): void {
    expect(Health::cwvStatus('cls', 0.05))->toBe('good')
        ->and(Health::cwvStatus('cls', 0.2))->toBe('needs_improvement')
        ->and(Health::cwvStatus('cls', 0.4))->toBe('poor')
        ->and(Health::cwvStatus('inp_ms', 150.0))->toBe('good')
        ->and(Health::cwvStatus('inp_ms', 700.0))->toBe('poor');
});
