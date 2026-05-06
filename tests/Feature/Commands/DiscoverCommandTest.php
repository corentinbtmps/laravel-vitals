<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

it('lists GET routes without parameters when --routes is set', function (): void {
    Route::get('/about', fn (): string => 'about')->name('about');
    Route::get('/users/{id}', fn (): string => 'show')->name('users.show');
    Route::post('/contact', fn (): string => 'sent')->name('contact');

    $this->artisan('vitals:discover', ['--routes' => true])
        ->expectsOutputToContain('/about')
        ->doesntExpectOutputToContain('/users/{id}')
        ->doesntExpectOutputToContain('/contact')
        ->assertSuccessful();
});

it('lists URLs from a sitemap', function (): void {
    Http::fake([
        '*sitemap.xml' => Http::response(
            '<?xml version="1.0"?><urlset><url><loc>https://example.test/</loc></url><url><loc>https://example.test/blog</loc></url></urlset>',
            200,
        ),
    ]);

    $this->artisan('vitals:discover', ['--sitemap' => 'https://example.test/sitemap.xml'])
        ->expectsOutputToContain('blog')
        ->expectsOutputToContain('https://example.test/')
        ->assertSuccessful();
});

it('errors when neither --routes nor --sitemap is provided', function (): void {
    $this->artisan('vitals:discover')->assertFailed();
});
