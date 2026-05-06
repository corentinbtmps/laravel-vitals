<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\AuditDetail;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('renders audit details with scores, metrics, and recommendations', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    $audit = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => 80,
        'lcp_ms'            => 1500.0,
    ]);

    Recommendation::create([
        'audit_id'         => $audit->id,
        'source'           => 'lighthouse',
        'audit_key'        => 'unused-javascript',
        'category'         => 'performance',
        'severity'         => 'warning',
        'title_key'        => 'vitals::recommendations.unused-javascript.title',
        'description_key'  => 'vitals::recommendations.unused-javascript.description',
        'code_references'  => [
            ['file' => 'resources/views/welcome.blade.php', 'line_start' => 12, 'line_end' => 12, 'snippet' => '<script src="..."></script>', 'hint' => 'Use @vite()'],
        ],
    ]);

    Livewire::test(AuditDetail::class, ['audit' => $audit->id])
        ->assertOk()
        ->assertSeeText('home')
        ->assertSeeText('80')
        ->assertSeeText('Reduce unused JavaScript')
        ->assertSee('welcome.blade.php');
});
