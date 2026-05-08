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
    'spotlight' => [
        'placeholder'            => 'Rechercher URLs, audits, recommandations…',
        'group_urls'             => 'URLs',
        'group_audits'           => 'Audits',
        'group_recommendations'  => 'Recommandations',
        'group_learn'            => 'Apprendre',
        'empty'                  => 'Aucun résultat',
        'hint'                   => 'Saisissez au moins 2 caractères pour rechercher',
        'kbd_navigate'           => 'Naviguer',
        'kbd_open'               => 'Ouvrir',
        'button_label'           => 'Rechercher…',
    ],
    'recommendations' => [
        'unused-javascript' => [
            'title'       => 'Réduire le JavaScript inutilisé',
            'description' => 'Le JavaScript livré au navigateur mais jamais exécuté gaspille de la bande passante et du temps de parsing.',
        ],
        'unused-css-rules' => [
            'title'       => 'Supprimer le CSS inutilisé',
            'description' => 'Les octets CSS inutilisés doivent quand même être téléchargés et parsés par le navigateur.',
        ],
        'unminified-javascript' => [
            'title'       => 'Minifier le JavaScript',
            'description' => 'Le JavaScript minifié réduit la taille de transfert sans changement de comportement.',
        ],
        'unminified-css' => [
            'title'       => 'Minifier le CSS',
            'description' => 'Le CSS minifié réduit la taille de transfert.',
        ],
        'render-blocking-resources' => [
            'title'       => 'Éliminer les ressources bloquantes',
            'description' => 'Les ressources dans le head bloquent le premier rendu jusquʼà téléchargement. Différez ou inlinez le CSS critique.',
        ],
        'modern-image-formats' => [
            'title'       => 'Servir les images en formats modernes',
            'description' => 'WebP et AVIF compressent mieux que JPEG/PNG.',
        ],
        'uses-responsive-images' => [
            'title'       => 'Utiliser des images responsive',
            'description' => 'Ajoutez srcset et sizes pour que le navigateur choisisse la meilleure variante par viewport.',
        ],
        'efficient-animated-content' => [
            'title'       => 'Utiliser des vidéos pour le contenu animé',
            'description' => 'Les GIFs animés sont lourds. Encodez l\'animation en MP4/WebM et utilisez <video>.',
        ],
        'offscreen-images' => [
            'title'       => 'Lazy-load les images hors écran',
            'description' => 'Ajoutez loading="lazy" pour que les images sous la ligne de flottaison soient chargées à la demande.',
        ],
        'legacy-javascript' => [
            'title'       => 'Éviter de servir du JavaScript legacy aux navigateurs modernes',
            'description' => 'Les bundles polyfillés alourdissent les payloads des navigateurs modernes.',
        ],
        'duplicated-javascript' => [
            'title'       => 'Supprimer les modules dupliqués',
            'description' => 'Plusieurs bundles incluent le même module. Ajustez le splitting Vite.',
        ],
        'color-contrast' => [
            'title'       => 'Améliorer le contraste des couleurs',
            'description' => 'Le contraste du texte est en-dessous du seuil WCAG AA.',
        ],
        'image-alt' => [
            'title'       => 'Ajouter un alt aux images',
            'description' => 'Les images sans attribut alt sont inaccessibles aux lecteurs d\'écran.',
        ],
        'document-title' => [
            'title'       => 'Ajouter un titre au document',
            'description' => 'Les pages sans <title> sont inaccessibles et mauvaises pour le SEO.',
        ],
        'html-has-lang' => [
            'title'       => 'Déclarer la langue du document',
            'description' => 'Ajoutez <html lang="..."> pour les technologies dʼassistance.',
        ],
        'errors-in-console' => [
            'title'       => 'Corriger les erreurs console',
            'description' => 'Les erreurs console signalent des bugs runtime qui peuvent dégrader l\'UX.',
        ],
        'no-vulnerable-libraries' => [
            'title'       => 'Mettre à jour les librairies JS vulnérables',
            'description' => 'Une librairie embarquée a une vulnérabilité connue. Mettez-la à jour ou retirez-la.',
        ],
        'meta-description' => [
            'title'       => 'Ajouter une meta description',
            'description' => 'Les moteurs de recherche affichent la meta description dans les snippets.',
        ],
        'config-cache-disabled' => [
            'title'       => 'Cacher la configuration Laravel',
            'description' => 'Lancez `php artisan config:cache` lors du déploiement en production.',
        ],
        'route-cache-disabled' => [
            'title'       => 'Cacher la table de routes',
            'description' => 'Lancez `php artisan route:cache` lors du déploiement en production.',
        ],
        'view-cache-disabled' => [
            'title'       => 'Précompiler les vues Blade',
            'description' => 'Lancez `php artisan view:cache` pour éviter la compilation à lʼexécution.',
        ],
        'debug-on-prod' => [
            'title'       => 'Désactiver APP_DEBUG en production',
            'description' => 'Le mode debug fuit les stack traces et ralentit les requêtes.',
        ],
        'opcache-disabled' => [
            'title'       => 'Activer OPcache',
            'description' => 'OPcache cache le bytecode PHP compilé et est essentiel en production.',
        ],
        'missing-php-version' => [
            'title'       => 'Épingler une version PHP',
            'description' => 'Ajoutez une contrainte php explicite dans composer.json pour rejeter les versions incompatibles.',
        ],
        'session-driver-file' => [
            'title'       => 'Changer le driver session depuis "file"',
            'description' => 'Utilisez redis ou database pour les sessions sur des hôtes multi-process en production.',
        ],
        'cache-driver-file' => [
            'title'       => 'Changer le driver cache depuis "file"',
            'description' => 'Utilisez redis ou memcached en production.',
        ],
        'queue-driver-sync-prod' => [
            'title'       => 'Configurer une vraie connexion queue',
            'description' => 'La queue sync exécute les jobs en process et bloque les réponses. Utilisez redis/database/sqs en production.',
        ],
        'n-plus-one-detected' => [
            'title'       => 'Pattern N+1 détecté',
            'description' => 'Un pattern de requête s\'est répété au-dessus du seuil configuré pendant cet audit.',
        ],
        'slow-queries-detected' => [
            'title'       => 'Requêtes SQL lentes détectées',
            'description' => 'Une ou plusieurs requêtes ont dépassé le seuil de slow-query pendant cet audit.',
        ],
        'slow-views' => [
            'title'       => 'Vues Blade lentes détectées',
            'description' => 'Une vue rendue a pris plus longtemps que le seuil configuré.',
        ],
        'real-world-perf-degraded' => [
            'title'       => 'Performance réelle pire que synthetic',
            'description' => 'La télémétrie de trafic réel (Pulse / Telescope) montre des P95 significativement au-dessus de l\'audit Lighthouse synthetic. Investiguez les conditions production : charge, scripts tiers, géographie.',
        ],
        'excessive-dom-size' => [
            'title'       => 'Réduire la taille du DOM',
            'description' => 'La page a :count éléments DOM. Un DOM trop large ralentit le rendu et les interactions JS. Visez moins de 1500 éléments.',
        ],
        'cache-policy-short' => [
            'title'       => 'Améliorer la politique de cache',
            'description' => ':count resource(s) ont un TTL < 30 jours. Un cache long terme accélère les visites répétées.',
        ],
        'third-party-blocking' => [
            'title'       => 'Scripts tiers bloquant le main thread',
            'description' => ':count origine(s) tierce(s) (:entities) bloquent le main thread > 250ms. Différez ou self-hostez si possible.',
        ],
        'large-payload' => [
            'title'       => 'Réduire le poids de la page',
            'description' => 'Le poids total est :mb MB. Cela dégrade le LCP sur connexions lentes. Compressez les images et découpez les bundles JS.',
        ],
        'bootup-time-high' => [
            'title'       => 'Réduire le temps d\'exécution JavaScript',
            'description' => 'Un script prend :ms ms à évaluer. Code-splittez, lazy-loadez, ou retirez le JS inutilisé.',
        ],
        'unsized-images' => [
            'title'       => 'Images avec largeur et hauteur explicites',
            'description' => 'Définissez les attributs width/height sur les images pour réserver l\'espace de mise en page. Le navigateur calcule le ratio plus tôt et évite les sauts visuels au chargement.',
        ],
        'font-display' => [
            'title'       => 'Texte visible pendant le chargement des webfonts',
            'description' => 'Utilisez `font-display: swap` pour que le navigateur affiche immédiatement une police de secours puis bascule sur la webfont. Évite le FOIT (texte invisible).',
        ],
        'uses-rel-preload' => [
            'title'       => 'Précharger les ressources critiques',
            'description' => 'Ajoutez `<link rel="preload">` pour les ressources découvertes tardivement (modules Vite, images hero, polices critiques). Le navigateur les récupère plus tôt.',
        ],
        'uses-http2' => [
            'title'       => 'Utiliser HTTP/2 (ou HTTP/3)',
            'description' => 'HTTP/2 multiplexe les requêtes sur une seule connexion — bien plus rapide que HTTP/1.1 sur les pages riches. La plupart des hosts modernes (Forge, Vapor, Cloudflare) l\'activent par défaut.',
        ],
        'octane-not-running' => [
            'title'       => 'Considérer Laravel Octane pour un TTFB plus bas',
            'description' => 'Octane garde l\'application bootstrappée entre les requêtes (Swoole / FrankenPHP / RoadRunner), éliminant le coût du bootstrap par requête. Gains TTFB typiques : 40-200ms.',
        ],
        'assets-not-hashed' => [
            'title'       => 'Les assets ne sont pas hashés',
            'description' => 'Sans hash de contenu (`app-Df8gK3p2.js`), impossible de cacher agressivement — chaque changement nécessite une invalidation. Vite génère des noms hashés par défaut ; vérifiez votre build.',
        ],
    ],
];
