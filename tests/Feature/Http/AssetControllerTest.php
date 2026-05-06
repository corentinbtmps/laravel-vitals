<?php

declare(strict_types=1);

it('serves dashboard.css from the package dist directory', function (): void {
    $response = $this->get('/vitals/_assets/dashboard.css');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/css');
});

it('serves dashboard.js from the package dist directory', function (): void {
    $response = $this->get('/vitals/_assets/dashboard.js');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('javascript');
});

it('returns 404 for unknown asset names', function (): void {
    $this->get('/vitals/_assets/imaginary.css')->assertNotFound();
});

it('does not gate asset routes behind the Authorize middleware', function (): void {
    \LaravelVitals\Facades\Vitals::authorize(fn () => false);

    // Asset routes should still work even when the dashboard gate denies.
    $this->get('/vitals/_assets/dashboard.css')->assertOk();
});
