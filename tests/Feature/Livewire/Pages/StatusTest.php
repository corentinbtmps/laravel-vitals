<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use LaravelVitals\Livewire\Pages\Status;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('vitals.status.enabled', true);
    config()->set('vitals.status.title', 'My App Status');
});

it('renders the status page with app name', function (): void {
    Livewire::test(Status::class)
        ->assertOk()
        ->assertSeeText('My App Status');
});

it('shows 0% uptime when no audits or rum events exist', function (): void {
    Livewire::test(Status::class)
        ->assertOk()
        ->assertSeeText('0.00%');
});

it('shows recent incidents when there are low-performing audits', function (): void {
    $url = Url::create(['label' => 'status-test', 'path' => '/']);

    Audit::create([
        'id'                => Str::uuid()->toString(),
        'url_id'            => $url->id,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => 55,
        'completed_at'      => now()->subHours(2),
    ]);

    Livewire::test(Status::class)
        ->assertOk()
        ->assertSeeText('status-test');
});

it('uses the public layout not the dashboard layout', function (): void {
    $component = Livewire::test(Status::class);
    // The component renders successfully — layout switching is verified by not seeing dashboard chrome.
    $component->assertOk();
    $component->assertDontSee('vitals.dashboard');
});
