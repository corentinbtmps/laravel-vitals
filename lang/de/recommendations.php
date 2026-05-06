<?php

declare(strict_types=1);

return [
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
];
