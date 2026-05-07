<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Recommendation;
use LaravelVitals\Models\Url;

it('persists a Recommendation with json columns cast to arrays', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'local',
        'device' => 'mobile',
        'status' => 'completed',
    ]);

    $reco = Recommendation::create([
        'audit_id'           => $audit->id,
        'source'             => 'lighthouse',
        'audit_key'          => 'unused-javascript',
        'category'           => 'performance',
        'severity'           => 'warning',
        'title_key'          => 'vitals::vitals.recommendations.unused-javascript.title',
        'description_key'    => 'vitals::vitals.recommendations.unused-javascript.description',
        'translation_params' => ['size' => '180 KB'],
        'metrics'            => ['savings_kb' => 180],
        'code_references'    => [
            ['file' => 'resources/views/welcome.blade.php', 'line_start' => 12, 'line_end' => 12, 'snippet' => '<script src="...">', 'hint' => 'Use @vite()'],
        ],
    ]);

    $fresh = $reco->fresh();

    expect($fresh->translation_params)->toBe(['size' => '180 KB'])
        ->and($fresh->metrics)->toBe(['savings_kb' => 180])
        ->and($fresh->code_references[0]['file'])->toBe('resources/views/welcome.blade.php');
});
