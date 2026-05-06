<?php

declare(strict_types=1);

namespace LaravelVitals\Recommendations;

final readonly class RecommendationDescriptor
{
    public function __construct(
        public string $auditKey,
        public string $source,
        public string $category,
        public string $severity,
        public string $titleKey,
        public string $descriptionKey,
    ) {
    }
}
