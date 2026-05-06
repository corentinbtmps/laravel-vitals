<?php

declare(strict_types=1);

return [
    'tooltip' => [
        'pin'          => 'Zu Favoriten hinzufügen',
        'unpin'        => 'Aus Favoriten entfernen',
        'last_audit_at' => 'Zuletzt analysiert: :timestamp',
        'metric_score' => 'Zusammengesetzter Lighthouse-Performance-Score (0–100)',
        'metric_lcp'   => 'Largest Contentful Paint — Zeit bis zum Rendern des größten sichtbaren Elements. Gut < 2,5 s',
        'metric_inp'   => 'Interaction to Next Paint — Eingabelatenz. Gut < 200 ms',
        'metric_cls'   => 'Cumulative Layout Shift — visuelle Stabilität. Gut < 0,1',
        'metric_ttfb'  => 'Time to First Byte — Serverantwortzeit. Gut < 800 ms',
        'cwv_lcp'      => 'Largest Contentful Paint — Zeit bis das größte sichtbare Element gerendert wird. Gut = unter 2,5 s.',
        'cwv_cls'      => 'Cumulative Layout Shift — unerwartete visuelle Verschiebungen beim Laden. Gut = unter 0,1.',
        'cwv_inp'      => 'Interaction to Next Paint — Latenz zwischen Nutzereingabe und nächstem Rendern. Gut = unter 200 ms.',
        'cwv_ttfb'     => 'Time to First Byte — Serverantwortzeit bis zum ersten Byte. Gut = unter 800 ms.',
        'score_label'  => 'Lighthouse-:label-Score',
    ],
];
