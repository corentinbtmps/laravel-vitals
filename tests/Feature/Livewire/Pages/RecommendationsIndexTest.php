<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\RecommendationsIndex;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('shows empty state when no recommendations exist', function (): void {
    Livewire::test(RecommendationsIndex::class)
        ->assertOk()
        ->assertSee('No recommendations yet');
});

it('aggregates recommendations across audits', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    $audit = Audit::create(['id' => Str::uuid()->toString(), 'url_id' => $url->id, 'driver' => 'stub', 'device' => 'mobile', 'status' => 'completed']);

    Recommendation::create([
        'audit_id' => $audit->id, 'source' => 'lighthouse', 'audit_key' => 'unused-javascript',
        'category' => 'performance', 'severity' => 'warning',
        'title_key' => 'vitals::recommendations.unused-javascript.title',
        'description_key' => 'vitals::recommendations.unused-javascript.description',
    ]);
    Recommendation::create([
        'audit_id' => $audit->id, 'source' => 'lighthouse', 'audit_key' => 'unused-javascript',
        'category' => 'performance', 'severity' => 'warning',
        'title_key' => 'vitals::recommendations.unused-javascript.title',
        'description_key' => 'vitals::recommendations.unused-javascript.description',
    ]);

    Livewire::test(RecommendationsIndex::class)
        ->assertOk()
        ->assertSeeText('Reduce unused JavaScript')
        ->assertSeeText('2');
});
