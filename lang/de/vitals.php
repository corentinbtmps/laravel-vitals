<?php

declare(strict_types=1);

return [
    'api' => [
        'not_found'  => 'Ressource nicht gefunden.',
        'no_audits'  => 'Keine abgeschlossenen Audits für diese URL gefunden.',
        'forbidden'  => 'Zugriff verweigert.',
        'validation' => 'Ungültige Abfrageparameter.',
        'error'      => 'Ein unerwarteter Fehler ist aufgetreten.',
    ],
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
    'onboarding' => [
        'banner_title'    => 'Erste Schritte mit Laravel Vitals',
        'banner_subtitle' => ':count von :total Schritten abgeschlossen',
        'dismiss'         => 'Einrichtung überspringen',
        'dismiss_confirm' => 'Dieses Banner dauerhaft ausblenden. Alle Funktionen sind weiterhin über die Navigation erreichbar.',
        'steps' => [
            'urls' => [
                'title' => 'Erste URL konfigurieren',
                'cta'   => 'URL hinzufügen',
            ],
            'audit' => [
                'title' => 'Erste Analyse durchführen',
                'cta'   => 'Analyse starten',
            ],
            'notifications' => [
                'title' => 'Benachrichtigungen einrichten (optional)',
                'cta'   => 'Konfigurieren',
            ],
            'budgets' => [
                'title' => 'Performance-Budgets festlegen',
                'cta'   => 'Budgets festlegen',
            ],
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
    'spotlight' => [
        'placeholder'            => 'URLs, Audits, Empfehlungen suchen…',
        'group_urls'             => 'URLs',
        'group_audits'           => 'Audits',
        'group_recommendations'  => 'Empfehlungen',
        'group_learn'            => 'Lernen',
        'empty'                  => 'Keine Ergebnisse',
        'hint'                   => 'Mindestens 2 Zeichen eingeben',
        'kbd_navigate'           => 'Navigieren',
        'kbd_open'               => 'Öffnen',
        'button_label'           => 'Suchen…',
    ],
    'rum' => [
        'title'                 => 'Real-User-Monitoring',
        'subtitle'              => 'Core Web Vitals von echten Besuchern — datenschutzkonform, ohne IP-Speicherung.',
        'period_24h'            => 'Letzte 24 Stunden',
        'period_7d'             => 'Letzte 7 Tage',
        'period_30d'            => 'Letzte 30 Tage',
        'period_90d'            => 'Letzte 90 Tage',
        'no_data'               => 'Keine Daten',
        'url_breakdown'         => 'Aufschlüsselung nach URL',
        'inp_attribution_title' => 'INP-Attribution — langsame Interaktionen',
        'inp_attribution_subtitle' => 'Interaktionsziele und Ereignistypen, die zu schlechtem INP beitragen, aus web-vitals-Attributionsdaten extrahiert.',
        'col_url'               => 'URL',
        'col_samples'           => 'Stichproben',
        'col_element'           => 'Element',
        'col_event_type'        => 'Ereignis',
        'empty' => [
            'title' => 'Noch keine RUM-Daten',
            'body'  => 'Fügen Sie die @vitalsRum-Direktive in den <head> Ihres Haupt-Layouts ein, um echte Core Web Vitals zu erfassen.',
        ],
    ],
    'queries' => [
        'title'                   => 'Abfrage-Baseline',
        'subtitle'                => 'avg / p75 / p95 Abfragen und Abfragezeit pro Route — sortiert nach p95.',
        'baseline_title'          => 'Abfragestatistiken pro Route',
        'baseline_subtitle'       => 'Routen, bei denen der p75-Abfragewert > 2× des Vorzeit raums ist, werden als Regressionen markiert.',
        'memory_hogs_title'       => 'Speicherintensive Routen',
        'memory_hogs_subtitle'    => 'Top 5 Routen nach p75 Spitzenspeicher.',
        'regression'              => 'Regression',
        'col_route'               => 'Route',
        'col_samples'             => 'Stichproben',
        'empty' => [
            'title' => 'Noch keine Abfragedaten',
            'body'  => 'Backend-Telemetrie muss aktiviert sein. Aktivieren Sie always_capture oder führen Sie ein Audit durch.',
        ],
    ],
    'recommendations' => [
        'unused-javascript' => [
            'title'       => 'Ungenutztes JavaScript reduzieren',
            'description' => 'Ans Browser ausgeliefertes JavaScript, das nie ausgeführt wird, verschwendet Bandbreite und Parsing-Zeit.',
        ],
        'unused-css-rules' => [
            'title'       => 'Ungenutztes CSS entfernen',
            'description' => 'Ungenutzte CSS-Bytes müssen trotzdem heruntergeladen und vom Browser geparst werden.',
        ],
        'unminified-javascript' => [
            'title'       => 'JavaScript minifizieren',
            'description' => 'Minifiziertes JavaScript reduziert die Übertragungsgröße ohne Verhaltensänderung.',
        ],
        'unminified-css' => [
            'title'       => 'CSS minifizieren',
            'description' => 'Minifiziertes CSS reduziert die Übertragungsgröße.',
        ],
        'render-blocking-resources' => [
            'title'       => 'Render-blockierende Ressourcen eliminieren',
            'description' => 'Ressourcen im head blockieren das erste Rendering bis sie geladen sind. Verzögern oder kritisches CSS inline einbinden.',
        ],
        'modern-image-formats' => [
            'title'       => 'Bilder in modernen Formaten ausliefern',
            'description' => 'WebP und AVIF komprimieren besser als JPEG/PNG.',
        ],
        'uses-responsive-images' => [
            'title'       => 'Responsive Bilder verwenden',
            'description' => 'srcset und sizes hinzufügen, damit der Browser die beste Variante pro Viewport wählt.',
        ],
        'efficient-animated-content' => [
            'title'       => 'Video für animierten Content verwenden',
            'description' => 'Animierte GIFs sind schwer. Animation als MP4/WebM kodieren und <video> verwenden.',
        ],
        'offscreen-images' => [
            'title'       => 'Offscreen-Bilder lazy-laden',
            'description' => 'loading="lazy" hinzufügen, damit Bilder unter der Falz bei Bedarf geladen werden.',
        ],
        'legacy-javascript' => [
            'title'       => 'Kein Legacy-JavaScript an moderne Browser',
            'description' => 'Polyfilled Bundles blähen Payloads für moderne Browser auf.',
        ],
        'duplicated-javascript' => [
            'title'       => 'Duplizierte Module entfernen',
            'description' => 'Mehrere Bundles enthalten dasselbe Modul. Vite Vendor-Splitting anpassen.',
        ],
        'color-contrast' => [
            'title'       => 'Farbkontrast verbessern',
            'description' => 'Der Textkontrast liegt unter dem WCAG-AA-Schwellenwert.',
        ],
        'image-alt' => [
            'title'       => 'Alt-Text zu Bildern hinzufügen',
            'description' => 'Bilder ohne Alt-Text sind für Screenreader unzugänglich.',
        ],
        'document-title' => [
            'title'       => 'Dokumenttitel hinzufügen',
            'description' => 'Seiten ohne <title> sind unzugänglich und schlecht für SEO.',
        ],
        'html-has-lang' => [
            'title'       => 'Dokumentsprache deklarieren',
            'description' => '<html lang="..."> für Hilfstechnologien hinzufügen.',
        ],
        'errors-in-console' => [
            'title'       => 'Browser-Konsolenfehler beheben',
            'description' => 'Konsolenfehler deuten auf Runtime-Bugs hin, die UX verschlechtern können.',
        ],
        'no-vulnerable-libraries' => [
            'title'       => 'Verwundbare JavaScript-Bibliotheken aktualisieren',
            'description' => 'Eine gebündelte Bibliothek hat eine bekannte Schwachstelle. Aktualisieren oder entfernen.',
        ],
        'meta-description' => [
            'title'       => 'Meta-Description hinzufügen',
            'description' => 'Suchmaschinen zeigen die Meta-Description in den Snippets an.',
        ],
        'config-cache-disabled' => [
            'title'       => 'Laravel-Konfiguration cachen',
            'description' => '`php artisan config:cache` in Production-Deploys ausführen.',
        ],
        'route-cache-disabled' => [
            'title'       => 'Routen-Tabelle cachen',
            'description' => '`php artisan route:cache` in Production-Deploys ausführen.',
        ],
        'view-cache-disabled' => [
            'title'       => 'Blade-Views vorkompilieren',
            'description' => '`php artisan view:cache` ausführen, um Laufzeit-Kompilierung zu vermeiden.',
        ],
        'debug-on-prod' => [
            'title'       => 'APP_DEBUG in Production deaktivieren',
            'description' => 'Debug-Modus leakt Stack-Traces und verlangsamt Requests.',
        ],
        'opcache-disabled' => [
            'title'       => 'OPcache aktivieren',
            'description' => 'OPcache cacht den kompilierten PHP-Bytecode und ist essentiell in Production.',
        ],
        'missing-php-version' => [
            'title'       => 'PHP-Version pinnen',
            'description' => 'Explizite php-Constraint in composer.json hinzufügen, um inkompatible Versionen abzuweisen.',
        ],
        'session-driver-file' => [
            'title'       => 'Session-Driver von "file" wechseln',
            'description' => 'Redis oder Database für Sessions auf Multi-Process-Hosts in Production verwenden.',
        ],
        'cache-driver-file' => [
            'title'       => 'Cache-Driver von "file" wechseln',
            'description' => 'Redis oder Memcached in Production verwenden.',
        ],
        'queue-driver-sync-prod' => [
            'title'       => 'Echte Queue-Verbindung konfigurieren',
            'description' => 'Sync-Queue führt Jobs im Prozess aus und blockiert Antworten. Redis/Database/SQS in Production verwenden.',
        ],
        'n-plus-one-detected' => [
            'title'       => 'N+1-Query-Pattern erkannt',
            'description' => 'Ein Query-Pattern hat sich während dieses Audits über dem konfigurierten Schwellenwert wiederholt.',
        ],
        'slow-queries-detected' => [
            'title'       => 'Langsame Datenbankabfragen erkannt',
            'description' => 'Eine oder mehrere Abfragen haben den Slow-Query-Schwellenwert während dieses Audits überschritten.',
        ],
        'slow-views' => [
            'title'       => 'Langsame Blade-Views erkannt',
            'description' => 'Eine gerenderte View hat länger als der konfigurierte Schwellenwert gedauert.',
        ],
        'real-world-perf-degraded' => [
            'title'       => 'Real-World-Performance schlechter als Synthetic',
            'description' => 'Real-Traffic-Telemetrie (Pulse / Telescope) zeigt P95-Metriken signifikant über dem Synthetic-Lighthouse-Audit. Untersuche Production-Bedingungen: Last, Third-Party-Skripte, Geografie.',
        ],
        'excessive-dom-size' => [
            'title'       => 'DOM-Größe reduzieren',
            'description' => 'Die Seite hat :count DOM-Elemente. Große DOMs verlangsamen Rendering und JS-Interaktionen. Ziel: unter 1500 Elemente.',
        ],
        'cache-policy-short' => [
            'title'       => 'Cache-Policy verbessern',
            'description' => ':count Ressource(n) haben TTL unter 30 Tagen. Langzeit-Caching beschleunigt Folgebesuche.',
        ],
        'third-party-blocking' => [
            'title'       => 'Third-Party-Skripte blockieren Main Thread',
            'description' => ':count Third-Party-Origin(s) (:entities) blockieren den Main Thread > 250ms. Verzögern oder selbst hosten wo möglich.',
        ],
        'large-payload' => [
            'title'       => 'Seitengewicht reduzieren',
            'description' => 'Gesamtseitengewicht ist :mb MB. Große Payloads verschlechtern LCP bei langsamen Verbindungen. Bilder komprimieren und JS-Bundles aufteilen.',
        ],
        'bootup-time-high' => [
            'title'       => 'JavaScript-Ausführungszeit reduzieren',
            'description' => 'Ein einzelnes Skript braucht :ms ms zur Auswertung. Code-Splitting, Lazy-Loading oder ungenutztes JS entfernen.',
        ],
        'unsized-images' => [
            'title'       => 'Bilder mit expliziter Breite und Höhe',
            'description' => 'Setze explizite width/height-Attribute auf Bilder, um Layoutplatz zu reservieren. Browser berechnen das Seitenverhältnis früher und vermeiden Layout Shifts beim Laden.',
        ],
        'font-display' => [
            'title'       => 'Text während Webfont-Ladens sichtbar',
            'description' => '`font-display: swap` verwenden, damit der Browser sofort eine Fallback-Schrift anzeigt und zur Webfont wechselt. Vermeidet unsichtbaren Text (FOIT).',
        ],
        'uses-rel-preload' => [
            'title'       => 'Kritische Ressourcen vorladen',
            'description' => 'Mit `<link rel="preload">` spät entdeckte Ressourcen (Vite-Module, Hero-Bilder, kritische Schriften) früher abrufen.',
        ],
        'uses-http2' => [
            'title'       => 'HTTP/2 (oder HTTP/3) verwenden',
            'description' => 'HTTP/2 multiplext Requests über eine einzelne Verbindung — deutlich schneller als HTTP/1.1 auf ressourcenstarken Seiten. Die meisten modernen Hosts aktivieren es standardmäßig.',
        ],
        'octane-not-running' => [
            'title'       => 'Laravel Octane für niedrigeren TTFB erwägen',
            'description' => 'Octane hält die Anwendung zwischen Requests gebootet (Swoole / FrankenPHP / RoadRunner), spart den Bootstrap-Aufwand pro Request. Typische TTFB-Einsparungen: 40-200ms.',
        ],
        'assets-not-hashed' => [
            'title'       => 'Asset-Dateinamen nicht content-hashed',
            'description' => 'Ohne Content-Hashes (`app-Df8gK3p2.js`) kann man Assets nicht aggressiv cachen — jede Änderung erfordert Invalidierung. Vite erzeugt standardmäßig hashed Filenames; verifizieren.',
        ],
    ],
];
