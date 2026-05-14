<?php

declare(strict_types=1);

namespace LaravelVitals\Budgets;

use LaravelVitals\Enums\Severity;

final readonly class BudgetViolations
{
    /**
     * @param array<int, array{metric: string, severity: string, threshold: float|int, actual: float|int|null}> $items
     */
    public function __construct(
        private array $items,
    ) {
    }

    /**
     * @return array<int, array{metric: string, severity: string, threshold: float|int, actual: float|int|null}>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function worstSeverity(): ?Severity
    {
        $worst = null;
        foreach ($this->items as $item) {
            if ($item['severity'] === Severity::Critical->value) {
                return Severity::Critical;
            }
            $worst = Severity::Warning;
        }
        return $worst;
    }
}
