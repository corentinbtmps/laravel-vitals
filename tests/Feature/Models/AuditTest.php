<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;

it('creates an Audit with a uuid primary key and casts', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    $audit = Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'local',
        'device'            => 'mobile',
        'status'            => 'pending',
        'score_performance' => 92,
        'lcp_ms'            => 1234.5,
    ]);

    expect($audit->fresh())
        ->id->toBeString()
        ->status->toBe('pending')
        ->score_performance->toBe(92)
        ->lcp_ms->toEqual('1234.50');
});

it('belongs to a Url and has many Recommendations and one BackendTelemetry', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);

    $audit = Audit::create([
        'id'     => Str::uuid()->toString(),
        'url_id' => $url->id,
        'driver' => 'local',
        'device' => 'mobile',
        'status' => 'pending',
    ]);

    expect($audit->url())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class)
        ->and($audit->recommendations())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($audit->telemetry())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class);
});
