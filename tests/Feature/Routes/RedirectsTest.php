<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('redirects /vitals/insights to /vitals/issues?tab=top with 301', function (): void {
    $response = $this->get(route('vitals.insights'));

    $response->assertRedirectToRoute('vitals.issues', ['tab' => 'top']);
    $response->assertStatus(301);
});

it('redirects /vitals/recommendations to /vitals/issues?tab=all with 301', function (): void {
    $response = $this->get(route('vitals.recommendations'));

    $response->assertRedirectToRoute('vitals.issues', ['tab' => 'all']);
    $response->assertStatus(301);
});

it('serves /vitals/issues directly with 200', function (): void {
    $response = $this->get(route('vitals.issues'));

    $response->assertOk();
});
