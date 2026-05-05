<?php

declare(strict_types=1);

use LaravelVitals\Support\CodeReference;

it('serialises to and from array', function (): void {
    $ref = new CodeReference(
        file: 'resources/views/welcome.blade.php',
        lineStart: 12,
        lineEnd: 14,
        snippet: '<script src="/foo.js">',
        hint: 'Use @vite()',
    );

    $array = $ref->toArray();

    expect($array)->toBe([
        'file'       => 'resources/views/welcome.blade.php',
        'line_start' => 12,
        'line_end'   => 14,
        'snippet'    => '<script src="/foo.js">',
        'hint'       => 'Use @vite()',
    ])->and(CodeReference::fromArray($array))->toEqual($ref);
});
