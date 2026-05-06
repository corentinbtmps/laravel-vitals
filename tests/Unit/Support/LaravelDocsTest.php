<?php

declare(strict_types=1);

use LaravelVitals\Support\LaravelDocs;

it('builds a versioned URL when the Application class is available', function (): void {
    $url = LaravelDocs::url('eloquent-relationships');

    expect($url)->toMatch('#^https://laravel\.com/docs/\d+\.x/eloquent-relationships$#');
});

it('strips a leading slash from the path', function (): void {
    expect(LaravelDocs::url('/octane'))->toEndWith('/octane');
});

it('preserves anchors in the path', function (): void {
    $url = LaravelDocs::url('configuration#configuration-caching');

    expect($url)->toContain('configuration#configuration-caching');
});
