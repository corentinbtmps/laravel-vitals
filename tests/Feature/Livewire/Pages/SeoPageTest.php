<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\Seo;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders the SEO overview page', function (): void {
    Livewire::test(Seo::class)
        ->assertOk()
        ->assertSeeText('SEO');
});

it('shows correct period selector options', function (): void {
    Livewire::test(Seo::class)
        ->assertOk()
        ->assertSee('value="7d"', escape: false);
});

it('can switch period', function (): void {
    Livewire::test(Seo::class)
        ->set('period', '30d')
        ->assertOk();
});

it('displays per-URL SEO scores table when audits exist', function (): void {
    $url = Url::create(['label' => 'test-seo', 'path' => '/']);
    $audit = Audit::create([
        'id'           => Str::uuid()->toString(),
        'url_id'       => $url->id,
        'driver'       => 'stub',
        'device'       => 'mobile',
        'status'       => 'completed',
        'score_seo'    => 85,
        'completed_at' => now(),
    ]);

    Livewire::test(Seo::class)
        ->assertOk()
        ->assertSeeText('test-seo');
});

it('shows top failing checks for seo source recommendations', function (): void {
    $url = Url::create(['label' => 'test-seo-2', 'path' => '/page2']);
    $audit = Audit::create([
        'id'           => Str::uuid()->toString(),
        'url_id'       => $url->id,
        'driver'       => 'stub',
        'device'       => 'mobile',
        'status'       => 'completed',
        'score_seo'    => 60,
        'completed_at' => now(),
    ]);

    Recommendation::create([
        'audit_id'        => $audit->id,
        'source'          => 'seo',
        'audit_key'       => 'seo-canonical',
        'category'        => 'seo',
        'severity'        => 'critical',
        'title_key'       => 'vitals::vitals.seo.checks.canonical.title',
        'description_key' => 'vitals::vitals.seo.checks.canonical.description',
        'code_references' => [],
    ]);

    Livewire::test(Seo::class)
        ->assertOk()
        ->assertSeeText('seo-canonical');
});

it('can filter top failing checks by category', function (): void {
    Livewire::test(Seo::class)
        ->set('category', 'meta')
        ->assertOk();
});
