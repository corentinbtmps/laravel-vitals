<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders the search button after the spacer in the dashboard layout', function (): void {
    $response = $this->get(route('vitals.dashboard'));
    $response->assertOk();

    $html = $response->getContent();

    // Find positions: flux:spacer renders as some markup; we look for the
    // Spotlight trigger button (identifiable by aria-label = spotlight key)
    // and flux:spacer component. Both are rendered server-side as HTML.
    // The Spotlight trigger button contains 'vitals-spotlight' dispatch.
    $spacerPos  = strpos($html, 'flux:spacer') ?: strpos($html, 'flex-1');
    $searchPos  = strpos($html, 'vitals-spotlight');

    expect($spacerPos)->not->toBeFalse()
        ->and($searchPos)->not->toBeFalse()
        ->and($searchPos)->toBeGreaterThan($spacerPos);
});
