<?php

declare(strict_types=1);

return [
    'empty' => [
        'overview_no_urls' => [
            'title' => 'Ajoutez votre première URL pour commencer la surveillance',
            'body'  => 'Laravel Vitals suit les scores Lighthouse et la télémétrie backend pour les URLs que vous configurez. Commencez par ajouter les URLs à surveiller.',
            'cta'   => 'Configurer les URLs',
            'docs'  => 'Lire la documentation',
        ],
        'overview_no_audits' => [
            'title' => 'Aucune analyse pour l\'instant',
            'body'  => 'Lancez votre première analyse pour alimenter le tableau de bord. Les analyses peuvent s\'exécuter via artisan, une tâche planifiée ou votre pipeline CI.',
            'cta'   => 'Ouvrir les URLs',
            'docs'  => 'Lire la documentation',
        ],
        'urls_no_urls' => [
            'title' => 'Aucune URL configurée',
            'body'  => 'Configurez les URLs dans config/vitals.php sous la clé urls, ou exécutez le seeder de démonstration pour des données d\'exemple.',
            'docs'  => 'Lire la documentation',
        ],
        'recos_no_recos' => [
            'title' => 'Aucune recommandation pour l\'instant',
            'body'  => 'Lancez une analyse pour faire apparaître des opportunités d\'optimisation. Chaque recommandation renvoie au fichier et à la ligne exacts de votre application.',
            'cta'   => 'Parcourir les problèmes connus',
            'docs'  => 'Lire la documentation',
        ],
        'insights_no_history' => [
            'title' => 'Historique insuffisant',
            'body'  => 'Les insights comparent les analyses dans le temps. Lancez au moins 2 analyses par URL pour voir les tendances et les régressions.',
            'docs'  => 'Lire la documentation',
        ],
        'budgets_no_budgets' => [
            'title' => 'Aucun budget défini',
            'body'  => 'Les budgets de performance font échouer votre CI lorsque les scores descendent en dessous d\'un seuil. Définissez-les dans config/vitals.php sous budgets.',
            'docs'  => 'Lire la documentation',
        ],
    ],
    'onboarding' => [
        'banner_title'    => 'Premiers pas avec Laravel Vitals',
        'banner_subtitle' => ':count étape(s) sur :total effectuée(s)',
        'dismiss'         => 'Ignorer l\'assistant',
        'dismiss_confirm' => 'Masquer définitivement cette bannière. Toutes les fonctionnalités restent accessibles via la navigation.',
        'steps' => [
            'urls' => [
                'title' => 'Configurer votre première URL',
                'cta'   => 'Ajouter une URL',
            ],
            'audit' => [
                'title' => 'Lancer votre première analyse',
                'cta'   => 'Lancer l\'analyse',
            ],
            'notifications' => [
                'title' => 'Configurer les notifications (optionnel)',
                'cta'   => 'Configurer',
            ],
            'budgets' => [
                'title' => 'Définir des budgets de performance',
                'cta'   => 'Définir les budgets',
            ],
        ],
    ],
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
