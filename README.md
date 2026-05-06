<p align="center">
  <img src="resources/svg/logo.svg" alt="Laravel Vitals" width="280">
</p>

# Laravel Vitals

Lighthouse-powered performance audits for Laravel apps, with backend telemetry
correlation and host-application source code pointers for every recommendation.

## What it does

- Runs Lighthouse against URLs of your Laravel app via three drivers (`local`, `playwright`, `pagespeed`)
- Captures backend telemetry during the audit (queries, memory, views, jobs, cache, N+1)
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

- `php artisan vitals:doctor` — verify drivers, storage, notifications, telemetry sources are correctly wired
- `php artisan vitals:demo` — seed 4 fictional URLs with 14 days of audit history (perfect for screenshots and onboarding)
- `php artisan vitals:purge --demo` — remove demo data only
- `php artisan vitals:purge` — remove ALL vitals data (asks for confirmation)
- `php artisan vitals:check-regressions` — alert on score drops vs 7-day baseline
- `php artisan vitals:digest:send` — send weekly digest summary

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
