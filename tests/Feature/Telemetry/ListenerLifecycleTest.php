<?php

declare(strict_types=1);

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use LaravelVitals\Models\Url;
use LaravelVitals\Telemetry\TelemetryRecorder;

it('does not accumulate global listeners across multiple recorder instances', function (): void {
    Url::create(['label' => 'home', 'path' => '/']);

    $before = count(Event::getListeners(QueryExecuted::class));

    for ($i = 0; $i < 5; $i++) {
        $recorder = new TelemetryRecorder();
        $recorder->start("audit-{$i}");
        app()->instance('vitals.active-recorder', $recorder);
        $recorder->snapshot(200, 'home');
        app()->forgetInstance('vitals.active-recorder');
    }

    $after = count(Event::getListeners(QueryExecuted::class));

    // Allow at most 1 net new listener (the one from service provider boot).
    expect($after - $before)->toBeLessThanOrEqual(1);
});
