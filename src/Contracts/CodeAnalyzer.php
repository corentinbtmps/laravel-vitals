<?php

declare(strict_types=1);

namespace LaravelVitals\Contracts;

use LaravelVitals\Recommendations\AppContext;
use LaravelVitals\Support\CodeReferenceCollection;

interface CodeAnalyzer
{
    public function supports(string $auditKey): bool;

    /**
     * @param array<string, mixed> $auditData
     */
    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection;
}
