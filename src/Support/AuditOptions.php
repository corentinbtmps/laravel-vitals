<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

use LaravelVitals\Enums\Device;

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
        public Device $device,
        public array $categories,
        public array $extraHeaders,
        public int $timeoutSeconds,
        public ?string $auditId = null,
    ) {
    }

    public static function default(): self
    {
        return new self(
            device: Device::Mobile,
            categories: ['performance', 'accessibility', 'best_practices', 'seo'],
            extraHeaders: [],
            timeoutSeconds: 120,
        );
    }

    public function withDevice(Device $device): self
    {
        return new self($device, $this->categories, $this->extraHeaders, $this->timeoutSeconds, $this->auditId);
    }

    public function withExtraHeader(string $name, string $value): self
    {
        return new self(
            $this->device,
            $this->categories,
            [...$this->extraHeaders, $name => $value],
            $this->timeoutSeconds,
            $this->auditId,
        );
    }

    public function withAuditId(string $auditId): self
    {
        return new self($this->device, $this->categories, $this->extraHeaders, $this->timeoutSeconds, $auditId);
    }
}
