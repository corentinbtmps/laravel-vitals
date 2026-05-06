<?php

declare(strict_types=1);

namespace LaravelVitals\Budgets;

final class BudgetViolations
{
    /**
     * @param array<int, array{metric: string, severity: string, threshold: float|int, actual: float|int|null}> $items
     */
    public function __construct(
        private readonly array $items,
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

    public function worstSeverity(): ?string
    {
        $worst = null;
        foreach ($this->items as $item) {
            if ($item['severity'] === 'critical') {
                return 'critical';
            }
            $worst = 'warning';
        }
        return $worst;
    }
}
