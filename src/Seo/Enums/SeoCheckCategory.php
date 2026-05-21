<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Enums;

/**
 * Category grouping for SEO checks — maps to the four display sections on /vitals/audits/{id}/seo.
 */
enum SeoCheckCategory: string
{
    case Configuration = 'configuration';
    case Content       = 'content';
    case Meta          = 'meta';
    case Performance   = 'performance';

    public function label(): string
    {
        return __('vitals::vitals.seo.categories.' . $this->value);
    }
}
