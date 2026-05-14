<?php

declare(strict_types=1);

namespace LaravelVitals\Budgets;

use LaravelVitals\Enums\Severity;
use LaravelVitals\Models\Audit;

final class PerfBudget
{
    /** @var array<int, string> */
    private const BELOW_BAD = [
        'score_performance', 'score_accessibility', 'score_best_practices', 'score_seo',
    ];

    public static function evaluate(Audit $audit): BudgetViolations
    {
        /** @var array<string, mixed> $config */
        $config = config('vitals.budgets', []);

        $perUrl = (array) ($config['per_url'] ?? []);
        $label = $audit->url?->label;
        $overrides = is_string($label) ? ($perUrl[$label] ?? []) : [];

        $items = [];

        foreach ($config as $metric => $thresholds) {
            if ($metric === 'per_url' || ! is_array($thresholds)) {
                continue;
            }

            if (isset($overrides[$metric]) && is_array($overrides[$metric])) {
                $thresholds = array_replace($thresholds, $overrides[$metric]);
            }

            $actual = $audit->{$metric} ?? null;
            if ($actual === null) {
                continue;
            }

            $actualNum = is_numeric($actual) ? (float) $actual : null;
            if ($actualNum === null) {
                continue;
            }

            $severity = self::severityFor($metric, $actualNum, $thresholds);
            if ($severity !== null) {
                $items[] = [
                    'metric'    => $metric,
                    'severity'  => $severity,
                    'threshold' => $severity === 'critical' ? ($thresholds['critical'] ?? 0) : ($thresholds['warning'] ?? 0),
                    'actual'    => $actualNum,
                ];
            }
        }

        return new BudgetViolations($items);
    }

    /** @param array<string, mixed> $thresholds */
    private static function severityFor(string $metric, float $actual, array $thresholds): ?string
    {
        $warning = isset($thresholds['warning']) ? (float) $thresholds['warning'] : null;
        $critical = isset($thresholds['critical']) ? (float) $thresholds['critical'] : null;

        $belowBad = in_array($metric, self::BELOW_BAD, true);

        if ($belowBad) {
            if ($critical !== null && $actual < $critical) {
                return Severity::Critical->value;
            }
            if ($warning !== null && $actual < $warning) {
                return Severity::Warning->value;
            }
        } else {
            if ($critical !== null && $actual > $critical) {
                return Severity::Critical->value;
            }
            if ($warning !== null && $actual > $warning) {
                return Severity::Warning->value;
            }
        }

        return null;
    }
}
