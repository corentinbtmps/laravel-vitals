<?php

declare(strict_types=1);

use LaravelVitals\Contracts\LighthouseDriver;

it('declares the LighthouseDriver contract with audit and isAvailable methods', function (): void {
    $reflection = new ReflectionClass(LighthouseDriver::class);

    expect($reflection->isInterface())->toBeTrue()
        ->and($reflection->hasMethod('audit'))->toBeTrue()
        ->and($reflection->hasMethod('isAvailable'))->toBeTrue();

    $audit = $reflection->getMethod('audit');
    expect($audit->getNumberOfParameters())->toBe(2)
        ->and($audit->getReturnType()?->__toString())->toBe(\LaravelVitals\Support\LighthouseReport::class);

    $isAvailable = $reflection->getMethod('isAvailable');
    expect($isAvailable->getReturnType()?->__toString())->toBe('bool');
});
