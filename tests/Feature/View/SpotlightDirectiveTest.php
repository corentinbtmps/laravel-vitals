<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders the spotlight directive markup', function (): void {
    $rendered = Blade::render('@vitalsSpotlight');

    expect($rendered)->toContain('vitals-spotlight');
});
