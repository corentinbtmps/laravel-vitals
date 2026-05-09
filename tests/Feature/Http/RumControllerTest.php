<?php

declare(strict_types=1);

use LaravelVitals\Models\RumEvent;

// The ingest endpoint uses sendBeacon — no CSRF token available in browsers.
// We disable CSRF checking in tests to simulate production beacon requests.
beforeEach(function (): void {
    config()->set('vitals.rum.enabled', true);
    config()->set('vitals.rum.sample_rate', 1.0);
});

function validRumPayload(array $overrides = []): array
{
    return array_merge([
        'url'             => '/home',
        'metric'          => 'LCP',
        'value'           => 1234.5,
        'rating'          => 'good',
        'navigation_type' => 'navigate',
        'attribution'     => null,
        'device'          => 'desktop',
        'user_agent'      => 'Mozilla/5.0 Test',
        'connection'      => '4g',
        'timestamp'       => (int) (now()->getPreciseTimestamp(3)),
    ], $overrides);
}

it('persists a valid RUM beacon and returns 204', function (): void {
    $this->postJson(route('vitals.rum.ingest'), validRumPayload())
        ->assertNoContent();

    expect(RumEvent::query()->count())->toBe(1);
    $event = RumEvent::query()->first();
    expect($event->url)->toBe('/home');
    expect($event->metric)->toBe('LCP');
    expect($event->device)->toBe('desktop');
    expect($event->rating)->toBe('good');
});

it('rejects an unknown metric', function (): void {
    $this->postJson(route('vitals.rum.ingest'), validRumPayload(['metric' => 'FID']))
        ->assertUnprocessable();
    expect(RumEvent::query()->count())->toBe(0);
});

it('rejects an unknown device', function (): void {
    $this->postJson(route('vitals.rum.ingest'), validRumPayload(['device' => 'tablet']))
        ->assertUnprocessable();
    expect(RumEvent::query()->count())->toBe(0);
});

it('rejects missing url', function (): void {
    $payload = validRumPayload();
    unset($payload['url']);
    $this->postJson(route('vitals.rum.ingest'), $payload)
        ->assertUnprocessable();
});

it('persists all five metric types', function (): void {
    foreach (['LCP', 'INP', 'CLS', 'TTFB', 'FCP'] as $metric) {
        $this->postJson(route('vitals.rum.ingest'), validRumPayload(['metric' => $metric]))
            ->assertNoContent();
    }
    expect(RumEvent::query()->count())->toBe(5);
});

it('returns 204 without persisting when rum.enabled is false', function (): void {
    config()->set('vitals.rum.enabled', false);

    $this->postJson(route('vitals.rum.ingest'), validRumPayload())
        ->assertNoContent();

    expect(RumEvent::query()->count())->toBe(0);
});

it('stores attribution json for INP', function (): void {
    $this->postJson(route('vitals.rum.ingest'), validRumPayload([
        'metric'      => 'INP',
        'value'       => 320.0,
        'rating'      => 'poor',
        'attribution' => ['interactionTarget' => 'button.cta', 'interactionType' => 'click'],
    ]))->assertNoContent();

    $event = RumEvent::query()->first();
    expect($event->attribution)->toBeArray();
    expect($event->attribution['interactionTarget'])->toBe('button.cta');
});

it('stores nullable optional fields', function (): void {
    $this->postJson(route('vitals.rum.ingest'), validRumPayload([
        'rating'          => null,
        'navigation_type' => null,
        'user_agent'      => null,
        'connection'      => null,
        'attribution'     => null,
    ]))->assertNoContent();

    $event = RumEvent::query()->first();
    expect($event->rating)->toBeNull();
    expect($event->connection)->toBeNull();
});
