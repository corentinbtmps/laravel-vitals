<?php

declare(strict_types=1);

use LaravelVitals\Facades\Vitals;

it('forbids access when the gate denies', function (): void {
    Vitals::authorize(fn (): false => false);

    $this->get('/vitals')->assertForbidden();
});

it('allows access when the gate grants', function (): void {
    Vitals::authorize(fn (): true => true);

    $this->get('/vitals')->assertOk();
});
