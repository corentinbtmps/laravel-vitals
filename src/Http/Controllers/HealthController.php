<?php

declare(strict_types=1);

namespace LaravelVitals\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use LaravelVitals\Drivers\LighthouseDriverManager;
use LaravelVitals\Models\BackendTelemetry;

/**
 * Public health endpoint — no auth required.
 *
 * Returns a JSON payload suitable for uptime monitors (Better Uptime, Uptime Robot,
 * Pingdom, etc.). HTTP 200 when everything is ok or skip, HTTP 503 on any error.
 */
final class HealthController
{
    public function __invoke(): JsonResponse
    {
        $checks = [];
        $hasError = false;

        // 1. Database connectivity
        try {
            DB::connection(config('vitals.database'))->getPdo();
            $checks['database'] = 'ok';
        } catch (\Throwable) {
            $checks['database'] = 'error';
            $hasError = true;
        }

        // 2. Driver availability
        $checks['drivers'] = $this->checkDrivers();
        foreach ($checks['drivers'] as $status) {
            if ($status === 'error') {
                $hasError = true;
            }
        }

        // 3. Queue connectivity (optional — skip if queue not configured)
        $checks['queue'] = $this->checkQueue();
        if ($checks['queue'] === 'error') {
            $hasError = true;
        }

        // 4. Telemetry buffer (recent write to vitals_backend_telemetry)
        $checks['telemetry_buffer'] = $this->checkTelemetryBuffer();
        if ($checks['telemetry_buffer'] === 'error') {
            $hasError = true;
        }

        $status = $hasError ? 'error' : 'ok';
        $httpCode = $hasError ? 503 : 200;

        return response()->json([
            'status'    => $status,
            'timestamp' => now()->toIso8601String(),
            'checks'    => $checks,
            'version'   => '1.0.0-alpha.53',
        ], $httpCode);
    }

    /**
     * @return array<string, string>
     */
    private function checkDrivers(): array
    {
        $result = [];

        /** @var LighthouseDriverManager $manager */
        $manager = app(LighthouseDriverManager::class);

        foreach (['local', 'pagespeed', 'playwright'] as $name) {
            try {
                $driver = $manager->resolveByName($name);
                $result[$name] = $driver->isAvailable() ? 'ok' : 'skip';
            } catch (\Throwable) {
                $result[$name] = 'skip';
            }
        }

        return $result;
    }

    private function checkQueue(): string
    {
        $connection = config('queue.default', 'sync');

        if ($connection === 'sync') {
            return 'skip';
        }

        try {
            // Attempt to connect; just verify the connection resolves.
            app('queue')->connection($connection);
            return 'ok';
        } catch (\Throwable) {
            return 'warn';
        }
    }

    private function checkTelemetryBuffer(): string
    {
        try {
            // A count query is enough to confirm the table is reachable.
            BackendTelemetry::query()->count();
            return 'ok';
        } catch (\Throwable) {
            return 'error';
        }
    }
}
