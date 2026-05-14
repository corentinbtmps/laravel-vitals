<?php

declare(strict_types=1);

namespace LaravelVitals\Enums;

/**
 * Lifecycle state of a Vitals audit run.
 */
enum AuditStatus: string
{
    case Pending   = 'pending';
    case Running   = 'running';
    case Completed = 'completed';
    case Failed    = 'failed';

    /**
     * Whether the audit is in a terminal (finished) state.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Failed => true,
            default                       => false,
        };
    }

    /**
     * Translated human label.
     */
    public function label(): string
    {
        return __('vitals::vitals.audit_status.' . $this->value);
    }
}
