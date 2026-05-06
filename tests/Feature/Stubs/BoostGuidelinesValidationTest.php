<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('the Boost guidelines stub compiles as Blade without error', function (): void {
    $path = __DIR__ . '/../../../stubs/ai-guidelines/vitals.blade.php';
    expect(file_exists($path))->toBeTrue();

    $contents = file_get_contents($path);

    $rendered = Blade::render($contents);

    expect($rendered)->toBeString()
        ->and($rendered)->toContain('Laravel Vitals');
});
