<p align="center">
  <img src="resources/svg/logo.svg" alt="Laravel Vitals" width="280">
</p>

[![CI](https://github.com/corentinbtmps/laravel-vitals/actions/workflows/ci.yml/badge.svg)](https://github.com/corentinbtmps/laravel-vitals/actions/workflows/ci.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/humantocomputer/laravel-vitals.svg)](https://packagist.org/packages/humantocomputer/laravel-vitals)
[![License](https://img.shields.io/packagist/l/humantocomputer/laravel-vitals.svg)](LICENSE.md)

# Laravel Vitals

Lighthouse-powered performance audits for Laravel apps, with backend telemetry
correlation and host-application source code pointers for every recommendation.

## What it does

- Runs Lighthouse against URLs of your Laravel app via three drivers (`local`, `playwright`, `pagespeed`)
- Captures backend telemetry during the audit (queries, memory, views, jobs, cache, N+1)
- **Real User Monitoring** with privacy-respecting CWV beacons from real visitors
- **Memory profiling per route** — p75 peak memory surfaces the heaviest routes
- **Database query baselines** with regression detection (flags routes where p75 queries > 2× prior period)
- Surfaces actionable recommendations with `file:line` references in your own source code
- Ships a Livewire dashboard at `/vitals`
- Supports performance budgets with CI exit codes and JUnit XML output
- Auto-installs Laravel Boost guidelines and a Claude Code skill so AI agents understand the package

## Requirements

- PHP 8.2+
- Laravel 11, 12, or 13
- Livewire 3 + Flux Free 2 (auto-installed)
- For the local Lighthouse driver: Node 18+ and the `lighthouse` npm package on `$PATH`
- For the Playwright driver: Node 18+ + the `playwright` and `playwright-lighthouse` npm packages
- For the PageSpeed driver: a Google PSI API key (`VITALS_PAGESPEED_API_KEY`)

## Installation

```bash
composer require humantocomputer/laravel-vitals
php artisan vendor:publish --tag=vitals-config
php artisan migrate
php artisan vitals:install
```

The last command publishes Boost guidelines (`.ai/guidelines/vitals.blade.php`)
and the Claude Code skill (`.claude/skills/laravel-vitals/SKILL.md`).

> **Upgrading from alpha < 0.15**: schema migrations were consolidated into the original `create_*_table` files. Run `php artisan vitals:purge` (with `--demo` if you only seeded demo data), then drop the four `vitals_*` tables and re-run `php artisan migrate`. Production data should be exported first.

## Asset compilation

The dashboard ships pre-built CSS and JS in `dist/`. The package serves them automatically via routes — **users don't need to publish anything**.

For maintainers regenerating the assets:

```bash
npm install
npm run build
```

Power users wanting to serve assets via the webserver (slightly faster, recommended for high-traffic prod) can publish them:

```bash
php artisan vendor:publish --tag=vitals-assets --force
```

Then override the layout to use `asset('vendor/vitals/dashboard.css')` instead of `route('vitals.assets', ...)`.

## Real User Monitoring

RUM collects Core Web Vitals (LCP, INP, CLS, TTFB, FCP) from real visitors using Google's
[web-vitals](https://github.com/GoogleChrome/web-vitals) library. Unlike Lighthouse (lab data),
RUM reflects actual user conditions — varying connections, devices, and geography.

**Privacy by design:** No IP addresses are stored. No cookies. No client-side fingerprinting
beyond the browser UA string. Only metric values, URL paths, device type, connection hint, and
user-agent are persisted in `vitals_rum_events` (90 day retention, prunable).

### Setup

1. After upgrading, run migrations:

```bash
composer update
php artisan migrate
```

2. Add `@vitalsRum` to your main layout's `<head>` (or just before `</body>`):

```blade
{{-- resources/views/layouts/app.blade.php --}}
<head>
    ...
    @vitalsRum
</head>
```

3. View results at `/vitals/rum`. Per-URL distributions and p75 trends appear as soon as
   real users hit your pages.

### Configuration

```env
VITALS_RUM_ENABLED=true          # toggle collection (default: true)
VITALS_RUM_SAMPLE_RATE=0.1       # sample 10% of sessions in production
VITALS_RUM_RETENTION_DAYS=90     # prune events older than N days (default: 90)
```

To prune RUM events, add to your scheduler:

```php
$schedule->command('model:prune', [
    '--model' => [\LaravelVitals\Models\RumEvent::class],
])->daily();
```

## Global search (Cmd+K)

Add the `@vitalsSpotlight` directive to your main layout to enable a global
keyboard-driven search across URLs, audits, recommendations, and the Learn
knowledge base. Press **Cmd+K** (macOS) or **Ctrl+K** (Linux/Windows) on
any page of your application to open it.

```blade
{{-- resources/views/layouts/app.blade.php --}}
<body>
    {{ $slot }}

    @vitalsSpotlight
</body>
```

Place it once, near the closing `</body>`. The modal lives in your app
shell and binds keyboard listeners globally. Arrow keys navigate, Enter opens,
Esc closes.

## Configuration

Edit `config/vitals.php`:

```php
'urls' => [
    'home'    => '/',
    'product' => '/products/42',
],

'budgets' => [
    'lcp_ms'              => ['warning' => 2500, 'critical' => 4000],
    'cls'                 => ['warning' => 0.1,  'critical' => 0.25],
    'score_performance'   => ['warning' => 90,   'critical' => 70],
    'per_url' => [
        'admin' => ['lcp_ms' => ['warning' => 4000]], // looser budget for an internal page
    ],
],
```

## Running audits

```bash
# audit one URL synchronously
php artisan vitals:audit home --sync

# audit all enabled URLs (Bus::batch)
php artisan vitals:audit --all

# CI usage with budgets
php artisan vitals:audit --all --sync --fail-on-budget --format=junit > vitals-results.xml
```

Exit codes when `--fail-on-budget` is set:

- `0` — no violation
- `1` — at least one warning violation
- `2` — at least one critical violation

## Diagnostics & demos

- `php artisan vitals:doctor` — comprehensive diagnostics: DB tables, dist assets, Geist fonts, env settings, optional integrations, drivers, storage, notifications (12 checks, exits 1 on failure)
- `php artisan vitals:doctor --quiet` — silent CI mode: exits 1 on failure, no output on success
- `php artisan vitals:install-hook` — install a pre-commit git hook that runs `vitals:doctor` and aborts the commit on failure
- `php artisan vitals:install-hook --type=pre-push` — same for pre-push
- `php artisan vitals:install-hook --uninstall` — remove the hook (restores any previous backup)
- `php artisan vitals:demo` — seed 4 fictional URLs with 14 days of audit history (perfect for screenshots and onboarding)
- `php artisan vitals:purge --demo` — remove demo data only
- `php artisan vitals:purge` — remove ALL vitals data (asks for confirmation)
- `php artisan vitals:check-regressions` — alert on score drops vs 7-day baseline
- `php artisan vitals:digest:send` — send weekly digest summary

## JSON API

A read-only JSON API is mounted under `/vitals/api/v1/` and protected by the same `viewVitals` gate as the dashboard. No separate API tokens.

| Endpoint | Description |
|---|---|
| `GET /vitals/api/v1/audits` | Paginated list of completed audits |
| `GET /vitals/api/v1/audits/{id}` | Single audit detail with recommendations |
| `GET /vitals/api/v1/urls` | Paginated list of configured URLs |
| `GET /vitals/api/v1/urls/{id}/latest` | Latest completed audit for a URL |
| `GET /vitals/api/v1/recommendations` | Paginated list of recommendations |

Supports `?page=N&per_page=M` (default 25, max 100) and date filters `?since=2026-05-01&until=2026-05-09`.

Sample response for `/vitals/api/v1/audits`:

```json
{
    "data": [
        {
            "id": "uuid",
            "url": { "id": 1, "label": "home", "path": "/" },
            "device": "mobile",
            "score_performance": 85,
            "score_accessibility": 92,
            "lcp_ms": 2300,
            "inp_ms": 180,
            "cls": 0.05,
            "ttfb_ms": 450,
            "completed_at": "2026-05-09T10:00:00+00:00",
            "_links": {
                "self": "https://app.test/vitals/api/v1/audits/uuid",
                "html": "https://app.test/vitals/audits/uuid"
            }
        }
    ],
    "meta": { "page": 1, "per_page": 25, "total": 142 }
}
```

## CI Integration

### GitHub Action — PR performance comment

The package ships a GitHub Action that audits a PR's preview URL and posts a Markdown score delta table as a PR comment.

```yaml
# .github/workflows/pr-perf.yml
name: PR Performance

on: [pull_request]

jobs:
  vitals:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: humantocomputer/laravel-vitals/.github/actions/vitals-pr-comment@v1.0.0-alpha.50
        with:
          preview-url: ${{ vars.PREVIEW_URL }}
          base-url: https://your-production-app.com
          github-token: ${{ secrets.GITHUB_TOKEN }}
          fail-on-regression: 'true'
```

The action posts a comment like:

```
## ⚡ Laravel Vitals — preview perf

| Metric | Base | Preview | Δ |
|---|---|---|---|
| Performance | 92 | 89 | 🔴 -3 |
| Accessibility | 95 | 95 | → |
| LCP | 2.10s | 2.40s | 🔴 +300ms |
```

**Inputs:**

| Input | Required | Default | Description |
|---|---|---|---|
| `preview-url` | yes | — | Deployed preview URL to audit |
| `base-url` | no | — | Production URL for diff baseline (falls back to `vitals-baseline.json`) |
| `github-token` | yes | — | `secrets.GITHUB_TOKEN` |
| `fail-on-regression` | no | `false` | Exit 1 when a score drops more than the threshold |
| `regression-threshold` | no | `5` | Minimum score drop counted as regression |
| `devices` | no | `mobile` | Comma-separated: `mobile,desktop` |

### Pre-commit hook

Gate every commit behind `vitals:doctor`:

```bash
php artisan vitals:install-hook
# or for push
php artisan vitals:install-hook --type=pre-push
```

Removes and restores any previous hook automatically. Remove with:

```bash
php artisan vitals:install-hook --uninstall
```

## Dashboard

By default the dashboard is mounted at `/vitals` and is accessible only in the
`local` environment. Override the gate in your `AppServiceProvider`:

```php
use LaravelVitals\Facades\Vitals;

public function boot(): void
{
    Vitals::authorize(fn ($user) => $user?->is_admin ?? false);
}
```

## Backend telemetry

A middleware `CaptureVitalsTelemetry` is auto-registered on the `web` group.
On the fast path (no audit header, no opt-in) it returns immediately with
sub-microsecond overhead.

When you run `vitals:audit`, the package signs an `X-Vitals-Audit-Id` header
with your `APP_KEY` and injects it into every Lighthouse navigation. The
middleware validates the HMAC and records query/cache/job activity for that
single request, persisting it to `vitals_backend_telemetry`.

To enable continuous (Pulse-like) sampling, set `vitals.telemetry.always_capture = true`.

## Pruning

All package models implement Eloquent's `Prunable`. Add to your scheduler:

```php
$schedule->command('model:prune', [
    '--model' => [
        \LaravelVitals\Models\Audit::class,
        \LaravelVitals\Models\Recommendation::class,
        \LaravelVitals\Models\BackendTelemetry::class,
    ],
])->daily();
```

Retention defaults to 90 days; configure via `VITALS_RETENTION_DAYS`.

## Translations

Translations are available out of the box in:

- English (`en`, default)
- French (`fr`)
- German (`de`)
- Spanish (`es`)

The active locale follows `app()->getLocale()`. To customize a translation, publish the package's lang files and edit them:

```bash
php artisan vendor:publish --tag=vitals-translations
```

## License

MIT.
