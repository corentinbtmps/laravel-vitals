<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\AuditCompare;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders the compare page with two audits side by side', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    $auditA = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => 70,
        'lcp_ms'            => 3500.0,
        'completed_at'      => now()->subDay(),
    ]);

    $auditB = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => 85,
        'lcp_ms'            => 2200.0,
        'completed_at'      => now(),
    ]);

    Livewire::test(AuditCompare::class, ['a' => $auditA->id, 'b' => $auditB->id])
        ->assertOk()
        ->assertSeeText('home')
        ->assertSeeText('70')
        ->assertSeeText('85');
});

it('shows resolved and new recommendations', function (): void {
    $url = Url::create(['label' => 'product', 'path' => '/products']);

    $auditA = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
        'completed_at' => now()->subDay(),
    ]);

    Recommendation::create([
        'audit_id'        => $auditA->id,
        'source'          => 'lighthouse',
        'audit_key'       => 'unused-javascript',
        'category'        => 'performance',
        'severity'        => 'warning',
        'title_key'       => 'vitals::vitals.recommendations.unused-javascript.title',
        'description_key' => 'vitals::vitals.recommendations.unused-javascript.description',
        'code_references' => [],
    ]);

    $auditB = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'stub',
        'device' => 'mobile',
        'status' => 'completed',
        'completed_at' => now(),
    ]);

    Recommendation::create([
        'audit_id'        => $auditB->id,
        'source'          => 'lighthouse',
        'audit_key'       => 'render-blocking-resources',
        'category'        => 'performance',
        'severity'        => 'warning',
        'title_key'       => 'vitals::vitals.recommendations.render-blocking-resources.title',
        'description_key' => 'vitals::vitals.recommendations.render-blocking-resources.description',
        'code_references' => [],
    ]);

    $component = Livewire::test(AuditCompare::class, ['a' => $auditA->id, 'b' => $auditB->id]);

    $component->assertSeeText('Reduce unused JavaScript');
    $component->assertSeeText('Eliminate render-blocking resources');
});

it('shows positive delta badge when score improved', function (): void {
    $url = Url::create(['label' => 'blog', 'path' => '/blog']);

    $auditA = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => 60,
        'completed_at'      => now()->subDay(),
    ]);

    $auditB = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => 90,
        'completed_at'      => now(),
    ]);

    Livewire::test(AuditCompare::class, ['a' => $auditA->id, 'b' => $auditB->id])
        ->assertOk()
        ->assertSee('▲');
});

it('shows CWV metrics for both audits', function (): void {
    $url = Url::create(['label' => 'dash', 'path' => '/dashboard']);

    $auditA = Audit::create([
        'id'          => Str::uuid()->toString(),
        'url_id'      => $url->id,
        'driver'      => 'stub',
        'device'      => 'mobile',
        'status'      => 'completed',
        'lcp_ms'      => 4000.0,
        'cls'         => 0.25,
        'inp_ms'      => 400.0,
        'ttfb_ms'     => 1200.0,
        'completed_at' => now()->subDay(),
    ]);

    $auditB = Audit::create([
        'id'          => Str::uuid()->toString(),
        'url_id'      => $url->id,
        'driver'      => 'stub',
        'device'      => 'mobile',
        'status'      => 'completed',
        'lcp_ms'      => 2000.0,
        'cls'         => 0.05,
        'inp_ms'      => 150.0,
        'ttfb_ms'     => 600.0,
        'completed_at' => now(),
    ]);

    Livewire::test(AuditCompare::class, ['a' => $auditA->id, 'b' => $auditB->id])
        ->assertOk()
        ->assertSeeText('LCP')
        ->assertSeeText('CLS');
});

it('returns 404 when an audit id does not exist', function (): void {
    Livewire::test(AuditCompare::class, ['a' => Str::uuid()->toString(), 'b' => Str::uuid()->toString()])
        ->assertNotFound();
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
