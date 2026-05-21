<?php

declare(strict_types=1);

namespace LaravelVitals\Seo\Enums;

/**
 * The result status of a single SEO check run.
 */
enum SeoCheckStatus: string
{
    case Pass    = 'pass';
    case Fail    = 'fail';
    case Warning = 'warning';
    case Info    = 'info';
}
