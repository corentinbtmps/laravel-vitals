<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'placeholder' => [
            'body' => 'The dashboard is being assembled. Real Livewire pages land in plan 5.',
        ],
    ],
    'commands' => [],
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
