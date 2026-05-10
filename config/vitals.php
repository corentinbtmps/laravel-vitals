<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Lighthouse driver
    |--------------------------------------------------------------------------
    | One of: 'auto', 'local', 'playwright', 'pagespeed'.
    */
    'driver' => env('VITALS_DRIVER', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Driver configuration
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'local' => [
            'node_binary'       => env('VITALS_NODE_BINARY', 'node'),
            'lighthouse_binary' => env('VITALS_LIGHTHOUSE_BINARY', 'lighthouse'),
            'chrome_flags'      => ['--headless', '--no-sandbox'],
            'timeout_seconds'   => 120,
        ],
        'pagespeed' => [
            'api_key'  => env('VITALS_PAGESPEED_API_KEY'),
            'endpoint' => 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed',
        ],
        'playwright' => [
            'node_binary'     => env('VITALS_NODE_BINARY', 'node'),
            'timeout_seconds' => 120,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database connection used by Vitals models
    |--------------------------------------------------------------------------
    | null = the default Laravel connection.
    */
    'database' => env('VITALS_DB_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem disk where raw Lighthouse JSON reports are stored
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => env('VITALS_DISK', 'local'),
        'path' => 'vitals',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention policy (used by Prunable)
    |--------------------------------------------------------------------------
    */
    'retention' => [
        'days' => (int) env('VITALS_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backend telemetry capture
    |--------------------------------------------------------------------------
    */
    'telemetry' => [
        'auto_register'         => true,
        'always_capture'        => false,
        'sample_rate'           => 0.05,
        'n_plus_one_threshold'  => 10,
        'slow_query_threshold_ms' => 50,
        'max_queries'           => 10_000,
        'top_slow_queries'      => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Static analyzer configuration
    |--------------------------------------------------------------------------
    */
    'analyzers' => [
        'scan_paths' => [
            'resources',
            'public',
            'config',
            'routes',
            'composer.json',
            'vite.config.js',
            'vite.config.ts',
            'vite.config.mjs',
        ],
        'custom' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance budgets
    |--------------------------------------------------------------------------
    */
    'budgets' => [
        'lcp_ms'              => ['warning' => 2500, 'critical' => 4000],
        'cls'                 => ['warning' => 0.1,  'critical' => 0.25],
        'inp_ms'              => ['warning' => 200,  'critical' => 500],
        'tbt_ms'              => ['warning' => 200,  'critical' => 600],
        'score_performance'   => ['warning' => 90,   'critical' => 70],
        'score_accessibility' => ['warning' => 95,   'critical' => 85],
        'per_url'             => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI toggles
    |--------------------------------------------------------------------------
    | charts: 'auto' | 'apex' | 'flux'
    */
    'ui' => [
        'charts'              => 'auto',
        'editor_url_template' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'enabled'    => true,
        'path'       => 'vitals',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Declared URLs (label => path) for v1
    |--------------------------------------------------------------------------
    */
    'urls' => [
        // 'home' => '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Public status page
    |--------------------------------------------------------------------------
    | Set enabled => true to expose /vitals/status as a public status page.
    | Configure the appearance to match your brand.
    */
    'status' => [
        'enabled'     => env('VITALS_STATUS_ENABLED', false),
        'title'       => env('VITALS_STATUS_TITLE', null),   // Falls back to config('app.name')
        'description' => env('VITALS_STATUS_DESCRIPTION', null),
        'logo_url'    => env('VITALS_STATUS_LOGO_URL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit concurrency lock TTL
    |--------------------------------------------------------------------------
    | Maximum number of seconds a single audit is allowed to run.
    | Used as the lock TTL for the vitals:audit concurrency lock.
    */
    'audit_timeout_seconds' => (int) env('VITALS_AUDIT_TIMEOUT_SECONDS', 300),

    /*
    |--------------------------------------------------------------------------
    | Real User Monitoring (RUM)
    |--------------------------------------------------------------------------
    | RUM collects Core Web Vitals from real visitors via the web-vitals library.
    | Add @vitalsRum to your main layout's <head> to enable collection.
    | Privacy: no IP addresses, cookies, or fingerprinting — only metric values,
    | URL paths, device type, connection hint, and user-agent string are stored.
    */
    'rum' => [
        'enabled'        => env('VITALS_RUM_ENABLED', true),
        'sample_rate'    => (float) env('VITALS_RUM_SAMPLE_RATE', 1.0),
        'retention_days' => (int) env('VITALS_RUM_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'enabled'  => env('VITALS_NOTIFICATIONS_ENABLED', true),
        'channels' => ['mail'],
        'mail'     => [
            'to' => env('VITALS_NOTIFICATIONS_MAIL_TO'),
        ],
        'slack' => [
            'webhook_url' => env('VITALS_NOTIFICATIONS_SLACK_WEBHOOK'),
        ],
        'triggers' => [
            'audit_completed'  => false,
            'budget_violation' => true,
            'regression'       => ['threshold_percent' => 10],
            'weekly_digest'    => true,
        ],
    ],

];
