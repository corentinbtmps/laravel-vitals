<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * Immutable options passed to a LighthouseDriver per audit run.
 */
final readonly class AuditOptions
{
    /**
     * @param array<int, string> $categories
     * @param array<string, string> $extraHeaders
     */
    public function __construct(
        public string $device,
        public array $categories,
        public array $extraHeaders,
        public int $timeoutSeconds,
    ) {
    }

    public static function default(): self
    {
        return new self(
            device: 'mobile',
            categories: ['performance', 'accessibility', 'best_practices', 'seo'],
            extraHeaders: [],
            timeoutSeconds: 120,
        );
    }

    public function withDevice(string $device): self
    {
        return new self($device, $this->categories, $this->extraHeaders, $this->timeoutSeconds);
    }

    public function withExtraHeader(string $name, string $value): self
    {
        return new self(
            $this->device,
            $this->categories,
            [...$this->extraHeaders, $name => $value],
            $this->timeoutSeconds,
        );
    }
}
