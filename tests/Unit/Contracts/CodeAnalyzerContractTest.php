<?php

declare(strict_types=1);

use LaravelVitals\Contracts\CodeAnalyzer;

it('declares the CodeAnalyzer contract', function (): void {
    $reflection = new ReflectionClass(CodeAnalyzer::class);

    expect($reflection->isInterface())->toBeTrue()
        ->and($reflection->hasMethod('supports'))->toBeTrue()
        ->and($reflection->hasMethod('analyze'))->toBeTrue();

    $supports = $reflection->getMethod('supports');
    expect($supports->getReturnType()?->__toString())->toBe('bool');

    $analyze = $reflection->getMethod('analyze');
    expect($analyze->getReturnType()?->__toString())
        ->toBe(\LaravelVitals\Support\CodeReferenceCollection::class);
});
