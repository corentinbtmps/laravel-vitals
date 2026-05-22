{{-- Laravel Vitals - Boost guidelines (auto-detected by Laravel Boost) --}}

# Laravel Vitals

This package combines Lighthouse audits with backend telemetry. When working
with the laravel-vitals package, follow these guidelines.

## What it does

`corentinbtmps/laravel-vitals` runs Lighthouse against URLs of the host
Laravel app and persists scores, Core Web Vitals, backend telemetry (queries,
memory, views, jobs), and recommendations enriched with file:line pointers
into the host source code.

## Running an audit

- `php artisan vitals:audit {label}` — audit one declared URL
- `php artisan vitals:audit --all --sync` — audit every URL inline
- `php artisan vitals:audit --all --fail-on-budget --format=junit > vitals.xml` — CI usage
- `php artisan vitals:discover --routes` — list parameter-less GET routes as URL candidates
- `php artisan vitals:doctor` — diagnostic check (drivers, storage, notifications, sources)
- `php artisan vitals:demo` — seed fictional data for screenshots and exploration
- `php artisan vitals:check-regressions` — alert on score regressions vs baseline
- `php artisan vitals:digest:send` — send weekly digest summary
- `php artisan vitals:purge --demo` — remove demo data

Configured URLs live in `config('vitals.urls')` as a `label => path` map.

## Reading audit data

@verbatim
```php
use LaravelVitals\Models\Audit;

$latest = Audit::with(['url', 'recommendations', 'telemetry'])
    ->whereRelation('url', 'label', 'home')
    ->latest('completed_at')
    ->first();

foreach ($latest->recommendations as $reco) {
    foreach ($reco->code_references as $ref) {
        // $ref = ['file' => '...', 'line_start' => N, 'snippet' => '...', 'hint' => '...']
    }
}
```
@endverbatim

The dashboard lives at `/vitals` (configurable via `vitals.dashboard.path`).

## Writing performance-aligned code

- Prefer `@verbatim @vite(['resources/js/app.js']) @endverbatim` over raw `<script src>` tags so assets are bundled and versioned by Vite.
- Add `loading="lazy"` to `<img>` tags below the fold.
- Always paginate or chunk Eloquent queries that may return more than 100 rows.
- Use `with(...)` to eager-load relations and avoid N+1 patterns.
- Use `Bus::batch([...])->dispatch()` for heavy job lists; prefer `redis` / `database` queue connections in production over `sync`.
- In production deploys, run:
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`
- Set `APP_DEBUG=false` in production. Debug mode leaks stack traces and slows requests.

## Backend telemetry capture

The package auto-registers a `CaptureVitalsTelemetry` middleware on the `web`
group. It does a sub-microsecond fast-path return when no `X-Vitals-Audit-Id`
header is present and `vitals.telemetry.always_capture` is `false`.

When `RunAuditJob` runs Lighthouse, it injects a signed header into the
Lighthouse navigation; the middleware validates it via HMAC and records
queries / memory / views / cache events / jobs for that single request.
The telemetry row links to `vitals_audits` via `audit_id`.

## Performance budgets

`config('vitals.budgets')` defines warning + critical thresholds for `lcp_ms`,
`cls`, `inp_ms`, `tbt_ms`, and `score_*`. Per-URL overrides via the `per_url`
key. Run `php artisan vitals:audit --fail-on-budget` to exit non-zero on
violations (1 = warning, 2 = critical).

## Drivers

`config('vitals.driver')` selects the Lighthouse runner: `auto` (default,
tries local → playwright → pagespeed), `local` (requires `node` + `lighthouse`
CLI), `playwright` (requires `node` in PATH; runs Lighthouse via Playwright),
or `pagespeed` (requires `VITALS_PAGESPEED_API_KEY`; URL must be public; no
backend telemetry).

## Custom analyzers

Plug in your own analyzer by adding its class to `config('vitals.analyzers.custom')`.
The class must implement `LaravelVitals\Contracts\CodeAnalyzer`.
