<?php

declare(strict_types=1);

namespace LaravelVitals\Charts;

use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use LaravelVitals\Contracts\ChartRenderer;

final class ApexChartsRenderer implements ChartRenderer
{
    private const SUPPORTED = ['line', 'bar', 'gauge'];

    public function render(string $type, array $data, array $options = []): HtmlString
    {
        if (! in_array($type, self::SUPPORTED, true)) {
            throw new InvalidArgumentException("Unsupported chart type [$type].");
        }

        $view = view("vitals::charts.apex.$type", [
            'id'      => 'vitals-chart-' . bin2hex(random_bytes(6)),
            'data'    => $data,
            'options' => $options,
        ]);

        return new HtmlString($view->render());
    }
}
