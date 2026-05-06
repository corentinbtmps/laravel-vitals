<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Facades\Vitals;
use LaravelVitals\Livewire\Pages\UrlDetail;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(fn () => Vitals::authorize(fn (): true => true));

it('shows audit history for a single URL', function (): void {
    $url = Url::create(['label' => 'home', 'path' => '/']);
    Audit::create(['id' => Str::uuid()->toString(), 'url_id' => $url->id, 'driver' => 'stub', 'device' => 'mobile', 'status' => 'completed', 'completed_at' => now(), 'score_performance' => 92]);
    Audit::create(['id' => Str::uuid()->toString(), 'url_id' => $url->id, 'driver' => 'stub', 'device' => 'mobile', 'status' => 'completed', 'completed_at' => now()->subDay(), 'score_performance' => 88]);

    Livewire::test(UrlDetail::class, ['url' => $url->id])
        ->assertOk()
        ->assertSeeText('home')
        ->assertSeeText('92')
        ->assertSeeText('88');
});
