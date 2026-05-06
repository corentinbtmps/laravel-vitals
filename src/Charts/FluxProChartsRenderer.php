<?php

declare(strict_types=1);

namespace LaravelVitals\Charts;

use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use LaravelVitals\Contracts\ChartRenderer;

final class FluxProChartsRenderer implements ChartRenderer
{
    private const SUPPORTED = ['line', 'bar', 'gauge'];

    public function render(string $type, array $data, array $options = []): HtmlString
    {
        if (! in_array($type, self::SUPPORTED, true)) {
            throw new InvalidArgumentException("Unsupported chart type [$type].");
        }

        $view = view("vitals::charts.flux.$type", [
            'data'    => $data,
            'options' => $options,
        ]);

        return new HtmlString($view->render());
    }
}
