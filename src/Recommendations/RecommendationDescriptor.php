<?php

declare(strict_types=1);

namespace LaravelVitals\Recommendations;

use LaravelVitals\Enums\Severity;

final readonly class RecommendationDescriptor
{
    public function __construct(
        public string $auditKey,
        public string $source,
        public string $category,
        public Severity $severity,
        public string $titleKey,
        public string $descriptionKey,
    ) {
    }
}
