<?php

declare(strict_types=1);

namespace LaravelVitals\Commands\Output;

use DOMDocument;
use LaravelVitals\Budgets\BudgetViolations;
use LaravelVitals\Models\Audit;

final class JUnitFormatter
{
    /**
     * @param array<int, array{audit: Audit, violations: BudgetViolations}> $rows
     */
    public static function format(array $rows): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $suites = $doc->createElement('testsuites');
        $suites->setAttribute('name', 'laravel-vitals');
        $doc->appendChild($suites);

        foreach ($rows as $row) {
            $audit = $row['audit'];
            $violations = $row['violations'];

            $suite = $doc->createElement('testsuite');
            $suite->setAttribute('name', $audit->url?->label ?? 'unknown');
            $suite->setAttribute('tests', (string) max(count($violations->all()), 1));
            $suite->setAttribute('failures', (string) count($violations->all()));

            if ($violations->isEmpty()) {
                $tc = $doc->createElement('testcase');
                $tc->setAttribute('name', 'all-budgets-pass');
                $tc->setAttribute('classname', $audit->url?->label ?? 'unknown');
                $suite->appendChild($tc);
            } else {
                foreach ($violations->all() as $v) {
                    $tc = $doc->createElement('testcase');
                    $tc->setAttribute('name', $v['metric']);
                    $tc->setAttribute('classname', $audit->url?->label ?? 'unknown');

                    $failure = $doc->createElement('failure');
                    $failure->setAttribute('type', $v['severity']);
                    $failure->setAttribute('message', sprintf(
                        '%s actual %s exceeds %s threshold %s',
                        $v['metric'],
                        (string) $v['actual'],
                        $v['severity'],
                        (string) $v['threshold'],
                    ));
                    $tc->appendChild($failure);

                    $suite->appendChild($tc);
                }
            }

            $suites->appendChild($suite);
        }

        return (string) $doc->saveXML();
    }
}
