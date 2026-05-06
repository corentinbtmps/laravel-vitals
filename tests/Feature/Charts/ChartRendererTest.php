<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use LaravelVitals\Charts\ApexChartsRenderer;
use LaravelVitals\Contracts\ChartRenderer;

it('renders a line chart with ApexCharts', function (): void {
    $renderer = new ApexChartsRenderer();

    expect($renderer)->toBeInstanceOf(ChartRenderer::class);

    $html = $renderer->render('line', [
        'series' => [['name' => 'LCP', 'data' => [1500, 1700, 1600]]],
        'categories' => ['Mon', 'Tue', 'Wed'],
    ]);

    expect($html)->toBeInstanceOf(HtmlString::class)
        ->and((string) $html)->toContain('apexcharts')
        ->and((string) $html)->toContain('LCP');
});

it('renders a gauge chart', function (): void {
    $html = (new ApexChartsRenderer())->render('gauge', ['value' => 95]);

    expect((string) $html)->toContain('95');
});

it('throws on an unknown chart type', function (): void {
    expect(fn () => (new ApexChartsRenderer())->render('imaginary', []))
        ->toThrow(\InvalidArgumentException::class);
});
