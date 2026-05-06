<?php

declare(strict_types=1);

namespace LaravelVitals\Contracts;

use Illuminate\Support\HtmlString;

interface ChartRenderer
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    public function render(string $type, array $data, array $options = []): HtmlString;
}
