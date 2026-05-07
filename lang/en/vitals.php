<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'placeholder' => [
            'body' => 'The dashboard is being assembled. Real Livewire pages land in plan 5.',
        ],
    ],
    'commands' => [],
    'empty' => [
        'overview_no_urls' => [
            'title' => 'Add your first URL to start monitoring',
            'body'  => 'Laravel Vitals tracks Lighthouse scores and backend telemetry for the URLs you configure. Start by adding the URLs you want to monitor.',
            'cta'   => 'Configure URLs',
            'docs'  => 'Read docs',
        ],
        'overview_no_audits' => [
            'title' => 'No audits yet',
            'body'  => 'Run your first audit to populate the dashboard. Audits can run via artisan command, scheduled task, or your CI pipeline.',
            'cta'   => 'Open URLs',
            'docs'  => 'Read docs',
        ],
        'urls_no_urls' => [
            'title' => 'No URLs configured',
            'body'  => 'Configure URLs in config/vitals.php under the urls key, or run the demo seeder for sample data.',
            'docs'  => 'Read docs',
        ],
        'recos_no_recos' => [
            'title' => 'No recommendations yet',
            'body'  => 'Run an audit to surface optimization opportunities. Each recommendation links to the exact file and line in your app.',
            'cta'   => 'Browse known issues',
            'docs'  => 'Read docs',
        ],
        'insights_no_history' => [
            'title' => 'Not enough audit history',
            'body'  => 'Insights compare audits across time. Run at least 2 audits per URL to see trends and regressions.',
            'docs'  => 'Read docs',
        ],
        'budgets_no_budgets' => [
            'title' => 'No budgets defined',
            'body'  => 'Performance budgets fail your CI when scores drop below a threshold. Define them in config/vitals.php under budgets.',
            'docs'  => 'Read docs',
        ],
    ],
    'tooltip' => [
        'pin'          => 'Pin to favorites',
        'unpin'        => 'Unpin from favorites',
        'last_audit_at' => 'Last audited :timestamp',
        'metric_score' => 'Composite Lighthouse Performance score (0–100)',
        'metric_lcp'   => 'Largest Contentful Paint — time until the largest visible content renders. Good < 2.5s',
        'metric_inp'   => 'Interaction to Next Paint — input latency. Good < 200ms',
        'metric_cls'   => 'Cumulative Layout Shift — visual stability. Good < 0.1',
        'metric_ttfb'  => 'Time to First Byte — server response time. Good < 800ms',
        'cwv_lcp'      => 'Largest Contentful Paint — time until the largest visible content element is rendered. Good = under 2.5s.',
        'cwv_cls'      => 'Cumulative Layout Shift — how much visible content unexpectedly shifts during load. Good = under 0.1.',
        'cwv_inp'      => 'Interaction to Next Paint — latency between user input and the next paint. Good = under 200ms.',
        'cwv_ttfb'     => 'Time to First Byte — how long the server takes to respond with the first byte. Good = under 800ms.',
        'score_label'  => 'Lighthouse :label score',
    ],
];
