<?php

declare(strict_types=1);

return [
    'tooltip' => [
        'pin'          => 'Ajouter aux favoris',
        'unpin'        => 'Retirer des favoris',
        'last_audit_at' => 'Dernière analyse : :timestamp',
        'metric_score' => 'Score Lighthouse global (0–100)',
        'metric_lcp'   => 'Largest Contentful Paint — temps avant l\'affichage du plus grand élément visible. Bon < 2,5 s',
        'metric_inp'   => 'Interaction to Next Paint — latence des interactions. Bon < 200 ms',
        'metric_cls'   => 'Cumulative Layout Shift — stabilité visuelle. Bon < 0,1',
        'metric_ttfb'  => 'Time to First Byte — temps de réponse serveur. Bon < 800 ms',
        'cwv_lcp'      => 'Largest Contentful Paint — temps avant l\'affichage du plus grand élément visible. Bon = moins de 2,5 s.',
        'cwv_cls'      => 'Cumulative Layout Shift — importance des décalages visuels inattendus. Bon = moins de 0,1.',
        'cwv_inp'      => 'Interaction to Next Paint — latence entre une action et le prochain rendu. Bon = moins de 200 ms.',
        'cwv_ttfb'     => 'Time to First Byte — temps de réponse du serveur. Bon = moins de 800 ms.',
        'score_label'  => 'Score Lighthouse :label',
    ],
];
