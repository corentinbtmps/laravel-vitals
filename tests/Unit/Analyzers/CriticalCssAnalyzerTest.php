<?php

declare(strict_types=1);

use LaravelVitals\Analyzers\CriticalCssAnalyzer;
use LaravelVitals\Recommendations\AppContext;

it('supports render-blocking-resources audit key', function (): void {
    $analyzer = new CriticalCssAnalyzer();

    expect($analyzer->supports('render-blocking-resources'))->toBeTrue();
    expect($analyzer->supports('unused-javascript'))->toBeFalse();
});

it('returns empty collection when no blade views exist', function (): void {
    $analyzer = new CriticalCssAnalyzer();
    $ctx = new AppContext(basePath: '/tmp/nonexistent-app-' . uniqid(), auditedPath: '/', assetUrls: [], configSnapshot: []);

    $result = $analyzer->analyze('render-blocking-resources', [], $ctx);

    expect($result->count())->toBe(0);
});

it('detects above-fold classes from hero elements in blade files', function (): void {
    $dir = sys_get_temp_dir() . '/vitals-css-test-' . uniqid();
    mkdir($dir . '/resources/views', 0777, true);

    file_put_contents(
        $dir . '/resources/views/welcome.blade.php',
        '<div class="hero bg-white text-black py-12"><h1>Hello</h1></div>'
    );

    $analyzer = new CriticalCssAnalyzer();
    $ctx = new AppContext(basePath: $dir, auditedPath: '/', assetUrls: [], configSnapshot: []);

    $result = $analyzer->analyze('render-blocking-resources', [], $ctx);

    expect($result->count())->toBeGreaterThan(0);

    // Cleanup
    unlink($dir . '/resources/views/welcome.blade.php');
    rmdir($dir . '/resources/views');
    rmdir($dir . '/resources');
    rmdir($dir);
});

it('ignores non-above-fold elements', function (): void {
    $dir = sys_get_temp_dir() . '/vitals-css-test2-' . uniqid();
    mkdir($dir . '/resources/views', 0777, true);

    file_put_contents(
        $dir . '/resources/views/footer.blade.php',
        '<div class="footer bg-gray-900"><p>Copyright</p></div>'
    );

    $analyzer = new CriticalCssAnalyzer();
    $ctx = new AppContext(basePath: $dir, auditedPath: '/', assetUrls: [], configSnapshot: []);

    $result = $analyzer->analyze('render-blocking-resources', [], $ctx);

    expect($result->count())->toBe(0);

    // Cleanup
    unlink($dir . '/resources/views/footer.blade.php');
    rmdir($dir . '/resources/views');
    rmdir($dir . '/resources');
    rmdir($dir);
});
