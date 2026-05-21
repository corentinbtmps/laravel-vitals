<?php

declare(strict_types=1);

namespace LaravelVitals\Seo;

use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\Enums\SeoCheckStatus;

/**
 * Immutable value object returned by each SeoCheck::run() call.
 */
final readonly class SeoCheckResult
{
    public function __construct(
        public string $key,
        public SeoCheckCategory $category,
        public SeoCheckStatus $status,
        public string $messageKey,
        public ?string $actual = null,
        public ?string $expected = null,
        public ?string $hintKey = null,
        public ?string $docUrl = null,
        public int $weight = 5,
        /** @var array<int, array<string, string>>|null */
        public ?array $detailItems = null,
    ) {}

    public static function pass(
        string $key,
        SeoCheckCategory $category,
        string $messageKey,
        int $weight = 5,
        ?string $actual = null,
    ): self {
        return new self(
            key: $key,
            category: $category,
            status: SeoCheckStatus::Pass,
            messageKey: $messageKey,
            actual: $actual,
            weight: $weight,
        );
    }

    public static function fail(
        string $key,
        SeoCheckCategory $category,
        string $messageKey,
        int $weight = 5,
        ?string $actual = null,
        ?string $expected = null,
        ?string $hintKey = null,
        ?string $docUrl = null,
        ?array $detailItems = null,
    ): self {
        return new self(
            key: $key,
            category: $category,
            status: SeoCheckStatus::Fail,
            messageKey: $messageKey,
            actual: $actual,
            expected: $expected,
            hintKey: $hintKey,
            docUrl: $docUrl,
            weight: $weight,
            detailItems: $detailItems,
        );
    }

    public static function warning(
        string $key,
        SeoCheckCategory $category,
        string $messageKey,
        int $weight = 5,
        ?string $actual = null,
        ?string $expected = null,
        ?string $hintKey = null,
        ?string $docUrl = null,
        ?array $detailItems = null,
    ): self {
        return new self(
            key: $key,
            category: $category,
            status: SeoCheckStatus::Warning,
            messageKey: $messageKey,
            actual: $actual,
            expected: $expected,
            hintKey: $hintKey,
            docUrl: $docUrl,
            weight: $weight,
            detailItems: $detailItems,
        );
    }
}
