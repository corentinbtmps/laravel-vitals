<?php

declare(strict_types=1);

namespace LaravelVitals\Storage;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Persists raw Lighthouse JSON reports to a Laravel filesystem disk.
 *
 * Disk and path are configured via config('vitals.storage'):
 *   - disk: any disk registered in config/filesystems.php
 *   - path: subdirectory inside the disk
 *
 * Files are named "{path}/{audit_id}.json".
 */
final class ReportRepository
{
    public function store(string $auditId, string $rawJson): string
    {
        $path = $this->pathFor($auditId);

        $this->disk()->put($path, $rawJson);

        return $path;
    }

    public function read(string $path): string
    {
        if (! $this->disk()->exists($path)) {
            throw new RuntimeException("Vitals report not found at [$path].");
        }

        $contents = $this->disk()->get($path);

        if ($contents === null) {
            throw new RuntimeException("Vitals report at [$path] is unreadable.");
        }

        return $contents;
    }

    private function pathFor(string $auditId): string
    {
        $sub = (string) config('vitals.storage.path', 'vitals');

        return trim($sub, '/') . '/' . $auditId . '.json';
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('vitals.storage.disk', 'local'));
    }
}
