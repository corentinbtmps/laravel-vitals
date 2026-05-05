<?php

declare(strict_types=1);

it('responds 200 on the dashboard root in the local environment', function (): void {
    $response = $this->get('/vitals');
    $response->assertOk();
    $response->assertSee('Laravel Vitals');
});
