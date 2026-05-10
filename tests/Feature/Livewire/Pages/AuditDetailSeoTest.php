<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\AuditSeo;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders the seo page for an audit', function (): void {
    $url = Url::create(['label' => 'seo-test', 'path' => '/']);

    $audit = Audit::create([
        'id'        => Str::uuid()->toString(),
        'url_id'    => $url->id,
        'driver'    => 'stub',
        'device'    => 'mobile',
        'status'    => 'completed',
        'score_seo' => 82,
        'completed_at' => now(),
    ]);

    Livewire::test(AuditSeo::class, ['audit' => $audit->id])
        ->assertOk()
        ->assertSeeText('82')
        ->assertSeeText('SEO');
});

it('lists seo checks including meta description and canonical', function (): void {
    $url = Url::create(['label' => 'seo-checks', 'path' => '/checks']);

    $audit = Audit::create([
        'id'           => Str::uuid()->toString(),
        'url_id'       => $url->id,
        'driver'       => 'stub',
        'device'       => 'mobile',
        'status'       => 'completed',
        'score_seo'    => 90,
        'completed_at' => now(),
    ]);

    Livewire::test(AuditSeo::class, ['audit' => $audit->id])
        ->assertOk()
        ->assertSeeText('Meta description present')
        ->assertSeeText('Canonical URL declared');
});

it('shows seo recommendations linked to the audit', function (): void {
    $url = Url::create(['label' => 'seo-recos', 'path' => '/recos']);

    $audit = Audit::create([
        'id'           => Str::uuid()->toString(),
        'url_id'       => $url->id,
        'driver'       => 'stub',
        'device'       => 'mobile',
        'status'       => 'completed',
        'score_seo'    => 65,
        'completed_at' => now(),
    ]);

    Recommendation::create([
        'audit_id'        => $audit->id,
        'source'          => 'lighthouse',
        'audit_key'       => 'meta-description',
        'category'        => 'seo',
        'severity'        => 'warning',
        'title_key'       => 'vitals::vitals.recommendations.meta-description.title',
        'description_key' => 'vitals::vitals.recommendations.meta-description.description',
        'code_references' => [],
    ]);

    Livewire::test(AuditSeo::class, ['audit' => $audit->id])
        ->assertOk()
        ->assertSeeText('Add a meta description');
});
