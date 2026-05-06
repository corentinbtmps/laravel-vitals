<?php

declare(strict_types=1);

use LaravelVitals\Telemetry\SamplingDecider;

it('returns false when always_capture is disabled regardless of rate', function (): void {
    config()->set('vitals.telemetry.always_capture', false);
    config()->set('vitals.telemetry.sample_rate', 1.0);

    expect(SamplingDecider::shouldCapture(randomValue: 0.0))->toBeFalse();
});

it('returns true for any randomValue when always_capture and sample_rate=1.0', function (): void {
    config()->set('vitals.telemetry.always_capture', true);
    config()->set('vitals.telemetry.sample_rate', 1.0);

    expect(SamplingDecider::shouldCapture(randomValue: 0.999))->toBeTrue();
});

it('returns true when randomValue < sample_rate', function (): void {
    config()->set('vitals.telemetry.always_capture', true);
    config()->set('vitals.telemetry.sample_rate', 0.5);

    expect(SamplingDecider::shouldCapture(randomValue: 0.49))->toBeTrue();
});

it('returns false when randomValue >= sample_rate', function (): void {
    config()->set('vitals.telemetry.always_capture', true);
    config()->set('vitals.telemetry.sample_rate', 0.5);

    expect(SamplingDecider::shouldCapture(randomValue: 0.5))->toBeFalse()
        ->and(SamplingDecider::shouldCapture(randomValue: 0.99))->toBeFalse();
});
