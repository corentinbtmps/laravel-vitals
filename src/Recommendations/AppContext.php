<?php

declare(strict_types=1);

namespace LaravelVitals\Recommendations;

final readonly class AppContext
{
    /**
     * @param array<int, string> $assetUrls
     * @param array<string, mixed> $configSnapshot
     */
    public function __construct(
        public string $basePath,
        public string $auditedPath,
        public array $assetUrls,
        public array $configSnapshot,
    ) {
    }
}
