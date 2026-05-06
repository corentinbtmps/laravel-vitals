<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

use RuntimeException;
use Throwable;

/**
 * Thrown by a LighthouseDriver when an audit cannot complete. Carries the
 * audit id (when known) so the orchestrator can update the persisted row.
 */
final class AuditException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $auditId = null,
        public readonly string $driver = 'unknown',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
