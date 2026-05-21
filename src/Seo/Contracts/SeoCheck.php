<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Contracts;

use LaravelVitals\Seo\Enums\SeoCheckCategory;
use LaravelVitals\Seo\SeoCheckContext;
use LaravelVitals\Seo\SeoCheckResult;

interface SeoCheck
{
    /**
     * Unique machine key — used as the audit_key suffix (e.g. 'title-length' → 'seo-title-length').
     */
    public function key(): string;

    public function category(): SeoCheckCategory;

    /**
     * Importance weight 1–10. Used for weighted pass-rate calculation in vitals_seo_score.
     */
    public function weight(): int;

    public function run(SeoCheckContext $context): SeoCheckResult;
}
