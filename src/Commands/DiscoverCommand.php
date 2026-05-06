<?php

declare(strict_types=1);

namespace LaravelVitals\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * `php artisan vitals:discover [--routes] [--sitemap=URL]`
 *
 * Lists URL candidates the user can add to config('vitals.urls').
 */
final class DiscoverCommand extends Command
{
    /** @var string */
    protected $signature = 'vitals:discover
        {--routes : Inspect Route::getRoutes() for parameter-less GET routes}
        {--sitemap= : URL of a sitemap.xml to fetch and list <loc> entries from}';

    /** @var string */
    protected $description = 'List candidate URLs from registered routes or a sitemap.';

    public function handle(Router $router): int
    {
        if (! $this->option('routes') && ! $this->option('sitemap')) {
            $this->error('Pass --routes or --sitemap=URL.');
            return self::FAILURE;
        }

        if ($this->option('routes')) {
            $this->fromRoutes($router);
        }

        if ($sitemap = $this->option('sitemap')) {
            $this->fromSitemap((string) $sitemap);
        }

        $this->newLine();
        $this->info('Add chosen entries to config/vitals.php under the "urls" key:');
        $this->line("    'urls' => [");
        $this->line("        'home' => '/',");
        $this->line("    ],");

        return self::SUCCESS;
    }

    private function fromRoutes(Router $router): void
    {
        $candidates = [];

        foreach ($router->getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }
            $uri = '/' . ltrim($route->uri(), '/');
            if (str_contains($uri, '{')) {
                continue;
            }
            // Skip framework / package routes.
            if (str_starts_with($uri, '/_') || str_starts_with($uri, '/livewire')) {
                continue;
            }
            $candidates[] = [$route->getName() ?? '', $uri];
        }

        $this->info('Routes:');
        $this->table(['Name', 'Path'], $candidates);
    }

    private function fromSitemap(string $url): void
    {
        try {
            $response = Http::timeout(15)->get($url);
        } catch (Throwable $e) {
            $this->error("Failed to fetch sitemap [$url]: {$e->getMessage()}");
            return;
        }

        if (! $response->successful()) {
            $this->error("Sitemap [$url] returned HTTP {$response->status()}.");
            return;
        }

        $xml = @simplexml_load_string($response->body());
        if ($xml === false) {
            $this->error("Sitemap [$url] is not valid XML.");
            return;
        }

        $rows = [];
        foreach ($xml->url ?? [] as $entry) {
            $loc = (string) ($entry->loc ?? '');
            if ($loc !== '') {
                $rows[] = [$loc];
            }
        }

        $this->info("Sitemap entries from [$url]:");
        $this->table(['URL'], $rows);
    }
}
