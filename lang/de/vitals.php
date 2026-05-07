<?php

declare(strict_types=1);

return [
    'empty' => [
        'overview_no_urls' => [
            'title' => 'Fügen Sie Ihre erste URL hinzu, um die Überwachung zu starten',
            'body'  => 'Laravel Vitals verfolgt Lighthouse-Scores und Backend-Telemetrie für die von Ihnen konfigurierten URLs. Beginnen Sie damit, die zu überwachenden URLs hinzuzufügen.',
            'cta'   => 'URLs konfigurieren',
            'docs'  => 'Dokumentation lesen',
        ],
        'overview_no_audits' => [
            'title' => 'Noch keine Analysen',
            'body'  => 'Führen Sie Ihre erste Analyse durch, um das Dashboard zu befüllen. Analysen können über artisan, geplante Aufgaben oder Ihre CI-Pipeline ausgeführt werden.',
            'cta'   => 'URLs öffnen',
            'docs'  => 'Dokumentation lesen',
        ],
        'urls_no_urls' => [
            'title' => 'Keine URLs konfiguriert',
            'body'  => 'Konfigurieren Sie URLs in config/vitals.php unter dem Schlüssel urls, oder führen Sie den Demo-Seeder für Beispieldaten aus.',
            'docs'  => 'Dokumentation lesen',
        ],
        'recos_no_recos' => [
            'title' => 'Noch keine Empfehlungen',
            'body'  => 'Führen Sie eine Analyse durch, um Optimierungsmöglichkeiten zu entdecken. Jede Empfehlung verweist auf die genaue Datei und Zeile in Ihrer Anwendung.',
            'cta'   => 'Bekannte Probleme durchsuchen',
            'docs'  => 'Dokumentation lesen',
        ],
        'insights_no_history' => [
            'title' => 'Nicht genügend Analyseverlauf',
            'body'  => 'Insights vergleichen Analysen über die Zeit. Führen Sie mindestens 2 Analysen pro URL durch, um Trends und Regressionen zu sehen.',
            'docs'  => 'Dokumentation lesen',
        ],
        'budgets_no_budgets' => [
            'title' => 'Keine Budgets definiert',
            'body'  => 'Performance-Budgets lassen Ihre CI fehlschlagen, wenn Scores unter einen Schwellenwert fallen. Definieren Sie sie in config/vitals.php unter budgets.',
            'docs'  => 'Dokumentation lesen',
        ],
    ],
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
