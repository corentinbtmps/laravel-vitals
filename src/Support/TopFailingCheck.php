<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

use LaravelVitals\Enums\Severity;

/**
 * One aggregated row in the SEO overview's "top failing checks" list: a unique
 * check (audit_key/title_key/severity) with how many URLs it failed on and a
 * sample audit to link to.
 */
final readonly class TopFailingCheck
{
    public function __construct(
        public string $audit_key,
        public string $title_key,
        public Severity $severity,
        public int $occurrences,
        public string $sample_audit_id,
    ) {
    }
}
