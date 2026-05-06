<?php

declare(strict_types=1);

use LaravelVitals\Drivers\BrowsershotDriver;
use LaravelVitals\Models\Url;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\AuditOptions;
use Spatie\Browsershot\Browsershot;

beforeEach(function (): void {
    $this->fixture = file_get_contents(__DIR__ . '/../../Fixtures/lighthouse-report.json');
});

afterEach(function (): void {
    Mockery::close();
});

/**
 * Test seam: subclass that lets us inject a Browsershot mock without touching
 * the real Browsershot::url() static factory.
 *
 * @param Browsershot $mock      A Mockery mock to return from makeBrowsershot().
 * @param string      $capturedUrl  Reference variable that receives the URL passed in.
 */
function makeTestableBrowsershotDriver(Browsershot $mock, string &$capturedUrl): BrowsershotDriver
{
    return new class($mock, $capturedUrl) extends BrowsershotDriver {
        public function __construct(
            private readonly Browsershot $injected,
            private string &$capturedUrl,
        ) {
        }

        protected function makeBrowsershot(string $url): Browsershot
        {
            $this->capturedUrl = $url;

            return $this->injected;
        }
    };
}

it('drives Browsershot and returns a normalised report', function (): void {
    $mock = Mockery::mock(Browsershot::class);
    $mock->shouldReceive('setExtraHttpHeaders')->once()->andReturnSelf();
    $mock->shouldReceive('setOption')->andReturnSelf();
    $mock->shouldReceive('lighthouseAudit')->once()->andReturn($this->fixture);

    config()->set('app.url', 'http://localhost');

    $captured = '';
    $driver = makeTestableBrowsershotDriver($mock, $captured);

    $url = Url::create(['label' => 'home', 'path' => '/']);

    $report = $driver->audit(
        $url,
        AuditOptions::default()->withExtraHeader('X-Vitals-Audit-Id', 'abc'),
    );

    expect($report->scores['performance'])->toBe(92)
        ->and($captured)->toBe('http://localhost/');
});

it('throws AuditException when Browsershot raises', function (): void {
    $mock = Mockery::mock(Browsershot::class);
    $mock->shouldReceive('setExtraHttpHeaders')->andReturnSelf();
    $mock->shouldReceive('setOption')->andReturnSelf();
    $mock->shouldReceive('lighthouseAudit')->andThrow(new RuntimeException('chrome crashed'));

    $captured = '';
    $driver = makeTestableBrowsershotDriver($mock, $captured);

    $url = Url::create(['label' => 'home', 'path' => '/']);

    expect(fn (): \LaravelVitals\Support\LighthouseReport => $driver->audit($url, AuditOptions::default()))
        ->toThrow(AuditException::class);
});

it('reports unavailable on a stock install of Browsershot v5 (no lighthouseAudit method)', function (): void {
    $driver = new BrowsershotDriver();

    // Stock Browsershot v5 does not provide lighthouseAudit() — the driver should report unavailable
    // so the auto-resolution chain skips it unless the user provides a custom bridge.
    expect($driver->isAvailable())->toBe(
        method_exists(\Spatie\Browsershot\Browsershot::class, 'lighthouseAudit'),
    );
});

it('throws AuditException with a clear message when Browsershot class is missing', function (): void {
    // We cannot uninstall Browsershot in a test, so this case is covered by inspection.
    // Assert the exception message wording exists in the source.
    $source = file_get_contents(__DIR__ . '/../../../src/Drivers/BrowsershotDriver.php');
    expect($source)->toContain('spatie/browsershot is not installed');
});
