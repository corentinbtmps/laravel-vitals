<?php

declare(strict_types=1);

use LaravelVitals\Enums\Device;

it('has the three expected cases', function (): void {
    expect(Device::cases())->toHaveCount(3)
        ->and(Device::Mobile->value)->toBe('mobile')
        ->and(Device::Desktop->value)->toBe('desktop')
        ->and(Device::Both->value)->toBe('both');
});

it('icon returns correct icon name for each case', function (): void {
    expect(Device::Mobile->icon())->toBe('device-phone-mobile')
        ->and(Device::Desktop->icon())->toBe('computer-desktop');
});

it('expand returns a single device for Mobile and Desktop', function (): void {
    expect(Device::Mobile->expand())->toBe([Device::Mobile])
        ->and(Device::Desktop->expand())->toBe([Device::Desktop]);
});

it('expand returns both devices for Both', function (): void {
    $expanded = Device::Both->expand();

    expect($expanded)->toHaveCount(2)
        ->and($expanded)->toContain(Device::Mobile)
        ->and($expanded)->toContain(Device::Desktop);
});

it('label returns non-empty translated strings', function (): void {
    expect(Device::Mobile->label())->not->toBeEmpty()
        ->and(Device::Desktop->label())->not->toBeEmpty()
        ->and(Device::Both->label())->not->toBeEmpty();
});

it('label returns French strings with the fr locale', function (): void {
    \App::setLocale('fr');

    expect(Device::Mobile->label())->toBe('Mobile')
        ->and(Device::Desktop->label())->toBe('Bureau');

    \App::setLocale('en');
});
