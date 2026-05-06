<?php

declare(strict_types=1);

namespace LaravelVitals\Http\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the package's pre-built dashboard assets directly from the
 * package's dist/ directory. Avoids requiring users to publish assets
 * after every upgrade.
 */
final class AssetController extends Controller
{
    /**
     * Whitelist of files we are willing to serve.
     *
     * @var array<string, string>
     */
    private const ALLOWED = [
        'dashboard.css' => 'text/css; charset=utf-8',
        'dashboard.js'  => 'application/javascript; charset=utf-8',
        'favicon.svg'   => 'image/svg+xml',
        'favicon.ico'   => 'image/x-icon',
    ];

    public function __invoke(string $file): Response
    {
        if (! isset(self::ALLOWED[$file])) {
            abort(404);
        }

        $path = dirname(__DIR__, 3) . '/dist/' . $file;

        if (! is_file($path)) {
            abort(404);
        }

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', self::ALLOWED[$file]);
        // Cache aggressively — file is keyed by package version on the consumer side via a query string in the layout.
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

        return $response;
    }
}
