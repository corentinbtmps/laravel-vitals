<?php

declare(strict_types=1);

it('renders a CodeReference with file, line, and snippet', function (): void {
    $ref = [
        'file'       => 'resources/views/welcome.blade.php',
        'line_start' => 12,
        'line_end'   => 12,
        'snippet'    => '<script src="/foo.js">',
        'hint'       => 'Use @vite()',
    ];

    $html = view('vitals::components.code-reference', ['ref' => $ref])->render();

    expect($html)
        ->toContain('welcome.blade.php')
        ->and($html)->toContain(':12')
        ->and($html)->toContain('foo.js')
        ->and($html)->toContain('@vite');
});

it('omits the hint block when no hint is provided', function (): void {
    $ref = [
        'file'       => 'app.blade.php',
        'line_start' => 5,
        'line_end'   => 5,
        'snippet'    => '<img src=...>',
        'hint'       => null,
    ];

    $html = view('vitals::components.code-reference', ['ref' => $ref])->render();

    expect($html)
        ->toContain('app.blade.php')
        ->and($html)->not->toContain('Hint:');
});
