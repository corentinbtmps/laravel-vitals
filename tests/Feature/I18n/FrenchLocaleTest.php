<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Enums\Severity;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\Insights;
use LaravelVitals\Livewire\Pages\Issues;
use LaravelVitals\Livewire\Pages\Overview;
use LaravelVitals\Livewire\Pages\UrlsList;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(function (): void {
    Vitals::authorize(fn (): true => true);
    \App::setLocale('fr');
});

afterEach(function (): void {
    \App::setLocale('en');
});

it('renders the overview page subtitle in French when locale=fr', function (): void {
    Livewire::test(Overview::class)
        ->assertSee('Aperçu rapide')
        ->assertDontSee('Quick health snapshot');
});

it('renders the overview page empty state in French when locale=fr', function (): void {
    Livewire::test(Overview::class)
        ->assertSee('Ajoutez votre première URL')
        ->assertDontSee('Add your first URL');
});

it('renders the overview page with French recommendations section when audits exist', function (): void {
    $url = Url::create(['label' => 'Accueil', 'path' => '/']);

    $audit = Audit::create([
        'id'                   => Str::uuid()->toString(),
        'url_id'               => $url->id,
        'driver'               => 'stub',
        'device'               => 'mobile',
        'status'               => 'completed',
        'score_performance'    => 65,
        'score_accessibility'  => 80,
        'score_best_practices' => 70,
        'score_seo'            => 90,
        'completed_at'         => now(),
    ]);

    Recommendation::create([
        'audit_id'           => $audit->id,
        'source'             => 'lighthouse',
        'audit_key'          => 'unused-javascript',
        'category'           => 'performance',
        'severity'           => 'warning',
        'title_key'          => 'vitals::vitals.recommendations.unused-javascript.title',
        'description_key'    => 'vitals::vitals.recommendations.unused-javascript.description',
        'translation_params' => null,
    ]);

    Livewire::test(Overview::class)
        ->assertSee('Problèmes prioritaires')
        ->assertDontSee('Top issues to fix')
        ->assertSee('Réduire le JavaScript inutilisé')
        ->assertSee('Avertissement');
});

it('renders the URLs list page with French section headings', function (): void {
    $url = Url::create(['label' => 'Accueil', 'path' => '/', 'pinned_at' => now()]);

    Livewire::test(UrlsList::class)
        ->assertSee('Favoris')
        ->assertDontSee('Favorites')
        ->assertSee('Voir');
});

it('renders the insights empty state in French when no audits exist', function (): void {
    // No audits → shows the empty state (insights_no_history)
    Livewire::test(Insights::class)
        ->assertSee('Historique insuffisant')
        ->assertDontSee('Not enough audit history');
});

it('severity enum label returns Critique in French', function (): void {
    expect(Severity::Critical->label())->toBe('Critique')
        ->and(Severity::Warning->label())->toBe('Avertissement')
        ->and(Severity::Info->label())->toBe('Info');
});
