<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('returns 200 json with status ok when database is reachable', function (): void {
    $response = $this->getJson(route('vitals.health'));

    $response->assertStatus(200);
    $response->assertJsonStructure(['status', 'timestamp', 'checks', 'version']);
    $response->assertJsonPath('status', 'ok');
    $response->assertJsonPath('checks.database', 'ok');
    $response->assertJsonPath('checks.telemetry_buffer', 'ok');
});

it('includes the package version in the response', function (): void {
    $response = $this->getJson(route('vitals.health'));

    $response->assertJsonPath('version', '1.0.0-alpha.53');
});

it('is publicly accessible without authentication', function (): void {
    // The health endpoint must respond even without the Authorize gate passing.
    config()->set('vitals.dashboard.enabled', true);

    $response = $this->getJson(route('vitals.health'));

    // Must not be 401 or 403 — public endpoint.
    $this->assertNotContains($response->status(), [401, 403]);
});

it('includes a timestamp in ISO 8601 format', function (): void {
    $response = $this->getJson(route('vitals.health'));

    $timestamp = $response->json('timestamp');
    expect($timestamp)->toBeString();
    // ISO 8601 contains a 'T' between date and time.
    expect($timestamp)->toContain('T');
});
