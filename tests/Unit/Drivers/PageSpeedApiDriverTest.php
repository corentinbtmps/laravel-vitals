<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LaravelVitals\Drivers\PageSpeedApiDriver;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\AuditOptions;

beforeEach(function (): void {
    $this->fixture = file_get_contents(__DIR__ . '/../../Fixtures/pagespeed-response.json');

    config()->set('vitals.drivers.pagespeed', [
        'api_key'  => 'k_test',
        'endpoint' => 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed',
    ]);
    config()->set('app.url', 'https://example.test');
});

it('calls the PSI endpoint with the expected query string and parses the response', function (): void {
    Http::fake([
        'www.googleapis.com/*' => Http::response($this->fixture, 200),
    ]);

    $driver = new PageSpeedApiDriver();
    $url = Url::create(['label' => 'home', 'path' => '/']);

    $report = $driver->audit($url, AuditOptions::default());

    expect($report->scores['performance'])->toBe(92);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'url=https%3A%2F%2Fexample.test%2F')
            && str_contains($request->url(), 'strategy=mobile')
            && str_contains($request->url(), 'key=k_test');
    });
});

it('throws AuditException on non-2xx responses', function (): void {
    Http::fake([
        'www.googleapis.com/*' => Http::response(['error' => ['message' => 'over quota']], 429),
    ]);

    $driver = new PageSpeedApiDriver();
    $url = Url::create(['label' => 'home', 'path' => '/']);

    expect(fn () => $driver->audit($url, AuditOptions::default()))
        ->toThrow(AuditException::class);
});

it('reports unavailable when the API key is missing', function (): void {
    config()->set('vitals.drivers.pagespeed.api_key', null);

    expect((new PageSpeedApiDriver())->isAvailable())->toBeFalse();
});

it('reports available when the API key is set', function (): void {
    expect((new PageSpeedApiDriver())->isAvailable())->toBeTrue();
});
