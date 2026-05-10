<p align="center">
  <img src="resources/svg/logo.svg" alt="Laravel Vitals" width="280">
</p>

[![CI](https://github.com/corentinbtmps/laravel-vitals/actions/workflows/ci.yml/badge.svg)](https://github.com/corentinbtmps/laravel-vitals/actions/workflows/ci.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/humantocomputer/laravel-vitals.svg)](https://packagist.org/packages/humantocomputer/laravel-vitals)
[![License](https://img.shields.io/packagist/l/humantocomputer/laravel-vitals.svg)](LICENSE.md)

# Laravel Vitals

**Performance auditing, Real User Monitoring, and actionable recommendations — all inside your Laravel app.**

Laravel Vitals runs Google Lighthouse against your own pages, captures what your server was doing at that exact moment (queries, memory, N+1 problems), and then points directly at the lines of code responsible. Everything lands in a dashboard at `/vitals` that any team member can read and act on.

---

## At a glance

- **One command to audit.** `php artisan vitals:audit home --sync` runs a Lighthouse scan, stores the score, pairs it with live backend data, and generates recommendations with file and line numbers.
- **Real user data alongside lab data.** Add one Blade directive — `@vitalsRum` — to start collecting Core Web Vitals from actual visitors. Privacy-respecting: no cookies, no IP addresses.
- **Everything self-hosted.** Your performance data stays in your own database. No SaaS, no per-seat billing, no data leaving your servers.

---

## Why Laravel Vitals

| Capability | GTMetrix | PageSpeed Insights | Laravel Vitals |
|---|---|---|---|
| Lighthouse audits (Performance, Accessibility, SEO, Best Practices) | ✓ | ✓ | **✓** |
| Backend telemetry — queries, memory, N+1, cache | ✗ | ✗ | **✓** |
| Source code references — exact file and line in your app | ✗ | ✗ | **✓** |
| Real User Monitoring | paid plan | ✗ | **✓ self-hosted** |
| Audit diff (score delta vs prior run) | paid plan | ✗ | **✓** |
| GitHub PR auto-comments with score table | ✗ | ✗ | **✓** |
| Self-hosted — your data stays yours | ✗ | ✗ | **✓** |
| No SaaS lock-in | ✗ | n/a | **✓** |
| Performance budgets with CI exit codes | ✗ | ✗ | **✓** |
| Audit comparison (before/after diff) | paid plan | ✗ | **✓** |
| Security headers audit | ✗ | ✗ | **✓** |
| Public status page | ✗ | ✗ | **✓** |

---

## How it works

Here is the full audit lifecycle from start to finish:

```
1. You declare URLs in config/vitals.php:
      'urls' => ['home' => '/', 'checkout' => '/checkout']

2. You run: php artisan vitals:audit home
   (or schedule it, or trigger it from CI)

3. Laravel Vitals signs an X-Vitals-Audit-Id header with your APP_KEY
   and spawns a Lighthouse process (Local, Playwright, or PageSpeed API)
   pointing at your app URL.

4. While Lighthouse navigates the page, your own middleware sees the
   signed header and records: query count, query time, N+1 suspicion,
   memory usage, views rendered, jobs dispatched, cache hits/misses.

5. Lighthouse returns scores (0–100) and raw metric values:
   LCP, INP, CLS, TTFB, FCP, TBT, Speed Index.

6. The static code analyzers scan your Blade views, Vite config,
   composer.json, and .env to attach file:line references to each
   Lighthouse finding.

7. Everything is stored and surfaced at /vitals:
   scores, backend telemetry, recommendations, trends, RUM data.

8. If a budget threshold is exceeded or a regression is detected,
   a Slack message or email is sent automatically.
```

---

## Features

### 1. Lighthouse audits, three ways to run them

Lighthouse is the same engine that powers Chrome DevTools and Google PageSpeed Insights. It simulates a page load under realistic mobile conditions and gives you scores from 0 to 100 across Performance, Accessibility, Best Practices, and SEO.

Laravel Vitals offers three drivers. Pick whichever suits your environment:

| Driver | What it requires | Best for |
|---|---|---|
| `local` | Node 18+ and the `lighthouse` CLI on the server | Dev machines and CI runners that have Node installed |
| `playwright` | Node 18+ plus the `playwright` and `playwright-lighthouse` npm packages | Docker-based CI where you control the full environment |
| `pagespeed` | A free Google API key (`VITALS_PAGESPEED_API_KEY`) | Auditing public-facing URLs without any Node setup |
| `auto` | Falls back through the above in order | Default — works out of the box in most environments |

The `local` and `playwright` drivers audit your app even when it is behind authentication or not publicly accessible (the package injects authentication headers). The `pagespeed` driver requires the target URL to be publicly reachable and cannot capture backend telemetry.

```bash
# Run one URL with the local driver
php artisan vitals:audit home --driver=local --device=mobile

# Run all URLs as a queued batch
php artisan vitals:audit --all

# Force synchronous, useful in CI
php artisan vitals:audit --all --sync
```

---

### 2. Backend telemetry — see what your server was doing

When Lighthouse loads your page, Laravel Vitals captures a snapshot of exactly what happened on the server side. This answers questions that Lighthouse alone cannot: "Why is our LCP slow — is it the database?" or "Did that PR add a new N+1 query?"

**What gets captured per request:**

| Signal | What it tells you |
|---|---|
| **Query count** | How many SQL queries fired to serve this page |
| **Query time (ms)** | Total time spent waiting for the database |
| **Unique queries** | Distinct SQL statements (duplicates suggest an N+1 problem) |
| **N+1 suspect flag** | Set when 10+ similar queries are detected in one request |
| **Peak memory (bytes)** | Maximum PHP memory used during the request |
| **Views rendered** | How many Blade templates were compiled and rendered |
| **Jobs dispatched** | Queued jobs triggered during the request |
| **Cache hits / misses** | Ratio showing whether your caches are effective |
| **Slow queries** | Individual queries that exceeded the threshold (default 50ms) |

Telemetry is collected in two modes:

- **Audit mode** (default): only captured when Lighthouse is running. Zero overhead for normal visitors.
- **Continuous sampling mode**: set `vitals.telemetry.always_capture = true` to sample a percentage of all requests (like Laravel Pulse). Controlled by `vitals.telemetry.sample_rate` (default 5%).

---

### 3. Source code references in your own app

This is what separates Laravel Vitals from every generic audit tool. When Lighthouse flags "render-blocking resources" or "unused JavaScript", the package doesn't just report the problem — it shows you exactly which file and line in your own codebase caused it.

**Example output on the dashboard:**

```
Recommendation: Eliminate render-blocking resources
Severity: warning
Source reference:
  resources/views/layouts/app.blade.php  line 12
  <link rel="stylesheet" href="/css/bootstrap.min.css">
  Hint: Use @vite([...]) to bundle and version this asset, or add `defer` / `async`.
```

Seven code analyzers scan your project:

| Analyzer | What it looks for |
|---|---|
| **BladeAssetAnalyzer** | CSS/JS tags in Blade files linked to Lighthouse's unused/render-blocking audit |
| **ImageAnalyzer** | `<img>` tags linked to modern-format, lazy-load, and responsive-image findings |
| **LaravelConfigAnalyzer** | Missing `php artisan config:cache`, debug mode in production, OPcache disabled |
| **ComposerAnalyzer** | Known vulnerable or outdated packages flagged by Lighthouse |
| **ViteConfigAnalyzer** | Vite configuration issues affecting asset bundling |
| **BladeViewAnalyzer** | Blade-specific patterns affecting performance |
| **EnvironmentAnalyzer** | `.env` settings that affect production performance |

---

### 4. Real User Monitoring (RUM)

Lab data (Lighthouse) tells you how a page performs under simulated conditions. Real User Monitoring tells you how it actually performs for your visitors — on their devices, with their network connections, from their locations.

**What RUM measures:** LCP (Largest Contentful Paint), INP (Interaction to Next Paint), CLS (Cumulative Layout Shift), TTFB (Time to First Byte), FCP (First Contentful Paint). These are Google's Core Web Vitals, which directly affect your Google Search ranking.

**How to enable:**

Add one directive to your main layout's `<head>`:

```blade
{{-- resources/views/layouts/app.blade.php --}}
<head>
    <meta charset="utf-8">
    <title>My App</title>
    @vitalsRum
</head>
```

That is the entire setup. Real visitors will silently send metric beacons in the background using the browser's `sendBeacon` API (so it never slows the page). Results appear at `/vitals/rum` within minutes of real traffic.

**Privacy by design.** The RUM script collects: metric name, metric value, rating (good/needs-improvement/poor), URL path, device type (mobile/desktop), connection type hint, browser user-agent string, and navigation type. It does NOT collect: IP addresses, cookies, session identifiers, or any information that identifies a specific person. This design makes GDPR compliance straightforward — there is no personally identifiable information to report, delete, or protect.

---

### 5. Memory profiling per route

The `/vitals/queries` page includes a "memory hogs" panel that shows the top 5 routes in your application ranked by p75 peak memory usage. "p75" means the 75th percentile: 75% of requests to that route used less memory than this number.

**When this matters:** A route that spikes to 256 MB on 25% of requests will eventually cause "out of memory" crashes or force you to over-provision your servers. This panel surfaces those routes before they become production incidents.

The data comes from backend telemetry. Memory is captured as `memory_get_peak_usage(true)` in bytes and stored in `vitals_backend_telemetry.peak_memory_bytes`. No additional setup is needed — it is recorded alongside query telemetry automatically.

---

### 6. Query baselines with regression detection

The `/vitals/queries` page answers a question most teams cannot answer today: "Did this recent deployment slow down the database queries on route X?"

For each route that has telemetry data, the page shows:

- **Average** query count and query time
- **p75** (75th percentile) — a realistic "typical bad" measure, more useful than the average
- **p95** (95th percentile) — the worst-case experience for 5% of requests

Routes are then compared to the previous equivalent period (if you are looking at the last 7 days, it compares to the 7 days before that). A route is flagged with a regression badge when its current p75 query count is more than **twice** what it was in the previous period.

**Example:** Route `orders.index` had a p75 of 12 queries last week. After a deployment, it has a p75 of 31 queries. The dashboard flags it immediately, and you can trace it back to the specific PR.

---

### 7. Performance budgets

A performance budget is a limit you set for a metric. When an audit result exceeds the limit, the package raises an alert. Budgets let you enforce standards automatically in CI pipelines and prevent performance regressions from shipping.

**Built-in budget metrics:**

| Metric | What it is | Default warning | Default critical |
|---|---|---|---|
| `lcp_ms` | Largest Contentful Paint (how fast the main content appears) | 2500ms | 4000ms |
| `cls` | Cumulative Layout Shift (how much the layout jumps) | 0.1 | 0.25 |
| `inp_ms` | Interaction to Next Paint (how fast the page responds to clicks) | 200ms | 500ms |
| `tbt_ms` | Total Blocking Time (main thread blocked during load) | 200ms | 600ms |
| `score_performance` | Overall Lighthouse performance score | below 90 | below 70 |
| `score_accessibility` | Overall accessibility score | below 95 | below 85 |

**In CI**, use `--fail-on-budget` to get non-zero exit codes:

```bash
php artisan vitals:audit --all --sync --fail-on-budget --format=junit > vitals-results.xml
# exit 0 = pass, exit 1 = warning violation, exit 2 = critical violation
```

JUnit XML output integrates with GitHub Actions, Jenkins, CircleCI, and any CI system that reads test reports.

**Per-URL overrides** let you set looser budgets for admin pages:

```php
'per_url' => [
    'admin-panel' => ['lcp_ms' => ['warning' => 5000, 'critical' => 8000]],
],
```

---

### 8. The dashboard at /vitals

The dashboard is a Livewire application mounted at `/vitals`. It is built with Flux components and ships pre-compiled CSS and JS — no asset compilation step required in the host app.

**Pages:**

| Page | URL | What you see |
|---|---|---|
| **Overview** | `/vitals` | Score averages, trend sparklines, active alerts, top recommendations. Filterable by 24h / 7d / 30d / 90d / 1y. |
| **URLs** | `/vitals/urls` | All monitored URLs with latest scores and a star/favourite toggle. |
| **URL Detail** | `/vitals/urls/{url}` | Audit history chart, per-run score table with delta badges, frequent issue patterns, failed audits panel. |
| **Audit Detail** | `/vitals/audits/{audit}` | Full Lighthouse result: scores, raw metrics, backend telemetry panel, source code references, third-party cost table, slow queries list. |
| **Budgets** | `/vitals/budgets` | Visual display of configured thresholds with pass/fail status. |
| **Insights** | `/vitals/insights` | Cross-URL analysis: quick wins (highest-impact fixes), worsening URLs, improving URLs, top third-party scripts by blocking time. |
| **Recommendations** | `/vitals/recommendations` | All recommendations aggregated across audits, sorted by frequency. |
| **Learn** | `/vitals/learn` | Browsable knowledge base of all ~42 known issue types, grouped by category, with links to web.dev and Laravel documentation. |
| **RUM** | `/vitals/rum` | Real user data: per-metric p75, good/needs-improvement/poor distribution, per-URL breakdown, INP attribution panel. |
| **Queries** | `/vitals/queries` | Route-level query statistics (avg, p75, p95), regression flags, memory hogs panel. |

**Access control:** By default the dashboard is available only in the `local` environment. To allow access in production, define the `viewVitals` gate in your `AppServiceProvider`:

```php
use LaravelVitals\Facades\Vitals;

public function boot(): void
{
    Vitals::authorize(fn ($user) => $user?->is_admin ?? false);
}
```

---

### 9. Recommendations and the Learn knowledge base

Every completed audit generates a list of recommendations. Each recommendation has:

- An **audit key** (e.g. `render-blocking-resources`, `n-plus-one-detected`)
- A **severity** (info, warning, or critical)
- A **category** (Performance, Accessibility, Best Practices, SEO)
- A **source code reference** — the exact file and line that triggered it
- A **hint** — one sentence describing how to fix it
- Links to the relevant **web.dev** documentation and the **Laravel docs** (version-aware)

The `/vitals/learn` page is a knowledge base covering approximately 42 known issue types. It is browsable by category. You can read it even before running your first audit to understand what the package can detect.

The package knows about: Lighthouse findings (unused JS/CSS, render-blocking resources, image format issues, legacy JavaScript, accessibility problems), Laravel-specific issues (missing `config:cache`, debug mode in production, file-based sessions in production, sync queue in production, OPcache disabled), and backend signals (N+1 queries, slow queries, third-party blocking scripts, large payloads).

---

### 10. Onboarding and Spotlight search

**Onboarding** — When you first open the dashboard with no data, each page shows an empty state with a clear explanation of what this page does and a copyable artisan command or config snippet to get started. You never face a blank dashboard without guidance.

**Spotlight / global search (Cmd+K)** — Add the `@vitalsSpotlight` directive anywhere in your main layout (typically just before `</body>`) to enable keyboard-driven search across URLs, audits, recommendations, and the Learn knowledge base. Press **Cmd+K** on macOS or **Ctrl+K** on Windows/Linux from anywhere in your app to open the palette.

```blade
{{-- resources/views/layouts/app.blade.php --}}
<body>
    {{ $slot }}
    @vitalsSpotlight
</body>
```

Arrow keys navigate the results. Enter opens. Escape closes. The Spotlight is powered by `spatie/laravel-searchable` and runs server-side searches — no client-side index to build or maintain.

---

### 11. JSON API

A read-only JSON API is mounted alongside the dashboard and protected by the same `viewVitals` gate. No separate API tokens are required. The API is intended for CI scripts, custom dashboards, and third-party integrations.

| Endpoint | What it returns |
|---|---|
| `GET /vitals/api/v1/audits` | Paginated list of completed audits |
| `GET /vitals/api/v1/audits/{id}` | Single audit with scores, metrics, and recommendations |
| `GET /vitals/api/v1/urls` | All configured URLs |
| `GET /vitals/api/v1/urls/{id}/latest` | Most recent completed audit for one URL |
| `GET /vitals/api/v1/recommendations` | All stored recommendations |

Query parameters: `?page=N&per_page=M` (default 25, max 100), `?since=2026-05-01&until=2026-05-09`.

**Example — fetch the latest audit for the home URL:**

```bash
curl -s https://yourapp.com/vitals/api/v1/urls/1/latest \
  -H "Accept: application/json"
```

```json
{
    "data": {
        "id": "uuid",
        "url": { "label": "home", "path": "/" },
        "device": "mobile",
        "score_performance": 85,
        "score_accessibility": 92,
        "lcp_ms": 2300,
        "inp_ms": 180,
        "cls": 0.05,
        "ttfb_ms": 450,
        "completed_at": "2026-05-09T10:00:00+00:00",
        "_links": {
            "self": "https://yourapp.com/vitals/api/v1/audits/uuid",
            "html": "https://yourapp.com/vitals/audits/uuid"
        }
    }
}
```

---

### 12. GitHub Action — automatic PR performance comments

When you open a pull request, this GitHub Action audits the preview deployment URL and posts a score comparison table as a PR comment. Your team sees performance impact before merging, without having to remember to run any manual checks.

```yaml
# .github/workflows/pr-perf.yml
name: PR Performance

on: [pull_request]

jobs:
  vitals:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: humantocomputer/laravel-vitals/.github/actions/vitals-pr-comment@v1.0.0-alpha.51
        with:
          preview-url: ${{ vars.PREVIEW_URL }}
          base-url: https://your-production-app.com
          github-token: ${{ secrets.GITHUB_TOKEN }}
          fail-on-regression: 'true'
```

The comment it posts looks like this:

```
## ⚡ Laravel Vitals — preview perf

| Metric       | Base | Preview | Δ       |
|---|---|---|---|
| Performance  |   92 |      89 | 🔴 -3   |
| Accessibility|   95 |      95 | →       |
| LCP          | 2.1s |    2.4s | 🔴 +300ms |
| CLS          | 0.02 |    0.02 | →       |
```

**Available inputs:**

| Input | Required | Description |
|---|---|---|
| `preview-url` | yes | The deployed preview URL to audit |
| `base-url` | no | Production URL for baseline comparison (falls back to a stored `vitals-baseline.json`) |
| `github-token` | yes | `secrets.GITHUB_TOKEN` — used to post the PR comment |
| `fail-on-regression` | no (default: `false`) | Exit with error code 1 when a score drops beyond the threshold |
| `regression-threshold` | no (default: `5`) | Minimum score drop considered a regression |
| `devices` | no (default: `mobile`) | Comma-separated: `mobile`, `desktop`, or `mobile,desktop` |

---

### 13. Pre-commit hook

The pre-commit hook runs `vitals:doctor` before every `git commit`. If any check fails (missing migration, broken asset, mis-configured notification), the commit is blocked and the error is shown. This prevents broken configurations from reaching your repository.

```bash
# Install the hook (runs before git commit)
php artisan vitals:install-hook

# Alternatively, run it before git push instead
php artisan vitals:install-hook --type=pre-push

# Remove the hook (restores any previous hook from backup)
php artisan vitals:install-hook --uninstall
```

The hook script is a simple bash one-liner (`php artisan vitals:doctor --quiet`). The command exits 0 on success and 1 on any failure — which is the signal git uses to block or allow the operation. If you had a pre-commit hook before, it is backed up automatically and restored when you uninstall.

---

### 14. Slack threads per audit

When notifications are configured with a Slack webhook, each audit creates a **Slack thread**. The initial message is posted when the audit completes. If that audit then triggers a budget violation or a regression alert, those follow-up messages are posted **as replies in the same thread** — keeping the conversation organized and searchable.

```
#perf-alerts channel
  ✅ Audit completed — home — perf 85, LCP 2300ms
    ↳ ⚠️ Budget violation — home: lcp_ms=2300 (>2500)
    ↳ 📉 Regression — home: 92 → 85 (-7.6%)
```

The Slack message timestamp (`ts`) is stored in `vitals_audits.slack_message_ts` so subsequent notifications can find the thread.

---

### 15. Notifications and integrations

Laravel Vitals sends notifications through Laravel's standard notification system. You configure which channels to use and which events trigger a notification.

**Notification events:**

| Event | When it fires | Default |
|---|---|---|
| `audit_completed` | Every time an audit finishes successfully | Off (can be noisy) |
| `budget_violation` | When an audit result exceeds a budget threshold | **On** |
| `regression` | When the performance score drops more than `threshold_percent` vs the 7-day baseline | **On** (10% threshold) |
| `weekly_digest` | A summary of all audits from the past 7 days | **On** |

**Available channels:** `mail`, `slack`, `database`.

For Slack, set a webhook URL in your `.env`:
```env
VITALS_NOTIFICATIONS_SLACK_WEBHOOK=https://hooks.slack.com/services/...
```

For email:
```env
VITALS_NOTIFICATIONS_MAIL_TO=team@yourcompany.com
```

To send the weekly digest on a schedule, add to your `routes/console.php`:
```php
Schedule::command('vitals:digest:send')->weekly();
Schedule::command('vitals:check-regressions')->daily();
```

---

### 16. Boost and Claude Code integration

Laravel Vitals can install context files that help AI coding assistants understand the package:

- **Boost guidelines** (`.ai/guidelines/vitals.blade.php`) — used by Laravel Boost and compatible AI tools. Describes the package's patterns so the AI can generate correct code when you ask it to add a recommendation, a RUM metric, or a new command.
- **Claude Code skill** (`.claude/skills/laravel-vitals/SKILL.md`) — used by Claude Code (Anthropic's CLI). Teaches the agent how to navigate the package structure, run the correct commands, and interpret audit results.

These files are installed by default when you run `php artisan vitals:install`. You can skip either with flags:

```bash
# Install only the Claude skill, skip Boost guidelines
php artisan vitals:install --no-boost

# Re-publish both to get the latest version from the package
php artisan vitals:boost:install --force

# Check whether your installed files differ from the latest package version
php artisan vitals:boost:diff
```

---

### 17. Audit comparison

When you fix a bug or deploy a change, you want to know whether performance actually improved. The audit comparison page shows two audits side by side — typically the same URL before and after a deployment.

Go to any URL's history table and click the compare icon next to any audit. The page shows:

- **Score grid**: Performance, Accessibility, Best Practices, SEO for both audits, with `▲ +5` / `▼ -3` / `→` delta badges.
- **CWV grid**: LCP, INP, CLS, TTFB formatted in milliseconds with directional indicators.
- **Recommendation diff**: Issues that were in A but not in B ("resolved") and issues that appeared in B but not in A ("new").
- **Telemetry diff**: Query count, query time, peak memory, and view render time for both audits.

You can also link directly: `/vitals/audits/{audit-id-a}/compare/{audit-id-b}`.

---

### 18. Public status page

Laravel Vitals can serve a public-facing status page at `/vitals/status`. The status page is off by default — add one line to opt in:

```php
// config/vitals.php
'status' => [
    'enabled' => true,
    'title'   => 'My App Status',
    'description' => 'Real-time performance and uptime information.',
    'logo_url' => null,  // Optional: URL to your logo
],
```

The page shows:
- **Uptime %** computed from the last 30 days of RUM events (falls back to audit data when RUM is not enabled).
- **CWV split** — good / needs improvement / poor distribution across audits in the last 7 days.
- **Recent incidents** — any audit where the performance score dropped below 70 in the last 7 days.
- **Last updated** timestamp.

The page uses a simplified layout with no dashboard chrome — suitable for sharing with stakeholders or embedding in a Notion page.

---

### 19. Self-monitoring

Laravel Vitals monitors itself. The `vitals:self-check` command checks table sizes and flags slow telemetry capture:

```bash
php artisan vitals:self-check
```

Add it to your scheduler for hourly checks:

```php
// app/Console/Kernel.php (or routes/console.php)
Schedule::command('vitals:self-check')->hourly();
```

Results are also visible in the dashboard at `/vitals/admin/self-check`. The page shows row counts per table and the 10 slowest captured requests so you can spot when Vitals itself is adding overhead.

---

### 20. Security headers audit

Every audit now runs the `SecurityHeadersAnalyzer` alongside the existing Lighthouse analyzers. It checks whether your HTTP responses include six key security headers:

- `Content-Security-Policy` — prevents cross-site scripting
- `Strict-Transport-Security` — enforces HTTPS
- `X-Frame-Options` or CSP `frame-ancestors` — prevents clickjacking
- `X-Content-Type-Options: nosniff` — prevents MIME-type attacks
- `Referrer-Policy` — controls referrer information
- `Permissions-Policy` — restricts browser feature access

Each missing or weak header generates a recommendation entry with a link to the relevant MDN or web.dev documentation. To add these headers in Laravel, create a middleware:

```php
// app/Http/Middleware/SecurityHeaders.php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    return $response;
}
```

---

## Installation

**Minimum setup — four commands:**

```bash
composer require humantocomputer/laravel-vitals
php artisan vendor:publish --tag=vitals-config
php artisan migrate
php artisan vitals:install
```

Then declare the URLs you want to audit in `config/vitals.php`:

```php
'urls' => [
    'home'     => '/',
    'login'    => '/login',
    'products' => '/products',
],
```

Run your first audit:

```bash
php artisan vitals:audit home --sync
```

Open `/vitals` in your browser.

**Requirements:**

- PHP 8.2 or higher
- Laravel 11, 12, or 13
- Livewire 4 and Flux Free 2 (installed automatically by Composer)
- For the `local` driver: Node 18+ and `npm install -g lighthouse` on the server
- For the `playwright` driver: Node 18+ and `npm install playwright playwright-lighthouse` in your project
- For the `pagespeed` driver: a free Google PageSpeed Insights API key

**Verify your setup:**

```bash
php artisan vitals:doctor
```

This command checks database tables, compiled assets, Lighthouse driver availability, storage access, and notification configuration. Exit code 0 means everything is ready.

---

## Configuration

The configuration file lives at `config/vitals.php` after you publish it. Here is every option explained:

### Driver selection

```php
'driver' => env('VITALS_DRIVER', 'auto'),
```

Controls which Lighthouse backend is used. `auto` tries `local`, then `playwright`, then `pagespeed`. Override per-run with `--driver=playwright`.

### Driver details

```php
'drivers' => [
    'local' => [
        'node_binary'       => env('VITALS_NODE_BINARY', 'node'),
        'lighthouse_binary' => env('VITALS_LIGHTHOUSE_BINARY', 'lighthouse'),
        'chrome_flags'      => ['--headless', '--no-sandbox'],
        'timeout_seconds'   => 120,
    ],
    'pagespeed' => [
        'api_key'  => env('VITALS_PAGESPEED_API_KEY'),
    ],
    'playwright' => [
        'node_binary'     => env('VITALS_NODE_BINARY', 'node'),
        'timeout_seconds' => 120,
    ],
],
```

The `chrome_flags` array is passed to Chrome when using the `local` driver. Add `--no-sandbox` for Docker/Linux environments where Chrome cannot use the sandbox.

### Database

```php
'database' => env('VITALS_DB_CONNECTION'),  // null = your default connection
```

Laravel Vitals creates six tables. By default they live in your app's main database. Set this to a different connection name if you want to isolate vitals data.

### Storage

```php
'storage' => [
    'disk' => env('VITALS_DISK', 'local'),
    'path' => 'vitals',
],
```

Raw Lighthouse JSON reports are stored here. In production you can use `s3` or any other Laravel disk.

### Retention

```php
'retention' => [
    'days' => (int) env('VITALS_RETENTION_DAYS', 90),
],
```

How long audit records and RUM events are kept before being pruned. To actually prune, schedule `model:prune` (see the Pruning section below).

### Backend telemetry

```php
'telemetry' => [
    'auto_register'           => true,    // auto-register the capturing middleware
    'always_capture'          => false,   // true = sample all requests, like Pulse
    'sample_rate'             => 0.05,    // fraction of requests to sample (5%)
    'n_plus_one_threshold'    => 10,      // queries_count / queries_unique ratio
    'slow_query_threshold_ms' => 50,      // queries slower than this are logged
    'max_queries'             => 10_000,  // safety cap on logged queries per request
    'top_slow_queries'        => 10,      // how many slow queries to store
],
```

### Analyzers

```php
'analyzers' => [
    'scan_paths' => ['resources', 'public', 'config', 'routes', 'composer.json', 'vite.config.js'],
    'custom'     => [],  // add your own CodeAnalyzer implementations here
],
```

Paths scanned for source code references. Add any directory your app keeps frontend files in.

### Budgets

```php
'budgets' => [
    'lcp_ms'              => ['warning' => 2500, 'critical' => 4000],
    'cls'                 => ['warning' => 0.1,  'critical' => 0.25],
    'inp_ms'              => ['warning' => 200,  'critical' => 500],
    'tbt_ms'              => ['warning' => 200,  'critical' => 600],
    'score_performance'   => ['warning' => 90,   'critical' => 70],
    'score_accessibility' => ['warning' => 95,   'critical' => 85],
    'per_url'             => [],  // URL-specific overrides
],
```

`warning` and `critical` are thresholds. For scores, the value is a *minimum* (below 90 = warning). For metrics (LCP, CLS etc.), the value is a *maximum* (above 2500ms = warning).

### UI

```php
'ui' => [
    'charts'              => 'auto',  // 'auto' | 'apex' | 'flux' (requires Flux Pro)
    'editor_url_template' => null,    // e.g. 'vscode://file/{file}:{line}'
],
```

Set `editor_url_template` to turn source code references into clickable links that open your editor. Supported placeholders: `{file}`, `{line}`.

### Dashboard

```php
'dashboard' => [
    'enabled'    => true,
    'path'       => 'vitals',       // the URL prefix: /vitals
    'middleware' => ['web'],        // middleware stack for dashboard routes
],
```

### Declared URLs

```php
'urls' => [
    'home'     => '/',
    'products' => '/products',
    'login'    => '/login',
],
```

Keys are labels (used in commands and the dashboard). Values are paths relative to `APP_URL`.

### Real User Monitoring

```php
'rum' => [
    'enabled'        => env('VITALS_RUM_ENABLED', true),
    'sample_rate'    => (float) env('VITALS_RUM_SAMPLE_RATE', 1.0),
    'retention_days' => (int) env('VITALS_RUM_RETENTION_DAYS', 90),
],
```

`sample_rate` is a number between 0 and 1. `1.0` means every page load sends a beacon. `0.1` means 10% of page loads. For high-traffic apps, 0.05–0.1 is usually sufficient to get statistically meaningful data.

### Notifications

```php
'notifications' => [
    'enabled'  => env('VITALS_NOTIFICATIONS_ENABLED', true),
    'channels' => ['mail'],         // 'mail', 'slack', 'database'
    'mail'     => ['to' => env('VITALS_NOTIFICATIONS_MAIL_TO')],
    'slack'    => ['webhook_url' => env('VITALS_NOTIFICATIONS_SLACK_WEBHOOK')],
    'triggers' => [
        'audit_completed'  => false,
        'budget_violation' => true,
        'regression'       => ['threshold_percent' => 10],
        'weekly_digest'    => true,
    ],
],
```

---

## Artisan commands

| Command | What it does | When to use it |
|---|---|---|
| `vitals:audit {label}` | Run a Lighthouse audit for one URL | Development, CI, or scheduled auditing |
| `vitals:audit --all` | Audit all enabled URLs (queued batch) | Nightly scheduled job |
| `vitals:audit --all --sync` | Audit all URLs synchronously | CI pipelines where you need to wait for results |
| `vitals:doctor` | Run 12 diagnostic checks and report results | After installation or when something breaks |
| `vitals:doctor --quiet` | Same, but silent on success (for git hooks and CI) | Pre-commit hook, CI preflight |
| `vitals:install` | Publish Boost guidelines and Claude Code skill | Once after installation |
| `vitals:install-hook` | Install a git pre-commit hook that runs `vitals:doctor` | Once per developer machine |
| `vitals:install-hook --type=pre-push` | Same, but for git push instead of commit | When you want to validate before push |
| `vitals:install-hook --uninstall` | Remove the hook (restores backup if present) | When you want to remove the hook |
| `vitals:discover --routes` | List candidate URLs from your Laravel routes | Discovering what to audit |
| `vitals:discover --sitemap=URL` | List candidate URLs from a sitemap.xml | Large sites with many pages |
| `vitals:check-regressions` | Compare latest audits to the 7-day baseline, alert on drops | Scheduled daily |
| `vitals:digest:send` | Send a weekly summary of recent audits | Scheduled weekly |
| `vitals:demo` | Seed fictional audit data for exploration | Onboarding, screenshots |
| `vitals:purge --demo` | Remove demo data only | After finishing with demo data |
| `vitals:purge` | Remove ALL vitals data (confirmation required) | Fresh start or database cleanup |
| `vitals:boost:install` | Re-publish Boost / Claude skill files | After a package upgrade |
| `vitals:boost:diff` | Check whether installed AI files differ from package | After a package upgrade |
| `vitals:self-check` | Check Vitals table sizes and slowest telemetry requests | Hourly via scheduler |

**Key options for `vitals:audit`:**

| Option | What it does |
|---|---|
| `--device=mobile` (default) or `--device=desktop` | Which device profile to simulate |
| `--driver=local\|playwright\|pagespeed` | Override the configured driver |
| `--sync` | Run synchronously instead of dispatching a queue job |
| `--fail-on-budget` | Exit 1 (warning) or 2 (critical) when budgets are exceeded |
| `--format=table\|json\|junit` | Output format (table is the default terminal output) |

---

## Privacy and data

**What RUM collects:**
- Metric name (LCP, INP, CLS, TTFB, FCP)
- Metric value (a number in milliseconds or a unit-less score)
- Rating (good / needs-improvement / poor — determined client-side by the web-vitals library)
- URL path (e.g. `/products/42` — not the full URL, just the path)
- Device type (mobile or desktop — inferred from screen width client-side)
- Connection type hint (4g, 3g, etc. — from the Network Information API if available)
- Navigation type (navigate, reload, back-forward)
- Browser user-agent string

**What RUM does NOT collect:**
- IP addresses (the ingest endpoint does not log or store them)
- Cookies or session identifiers
- User IDs or any account information
- Precise geolocation
- Any form of fingerprint

**Backend telemetry:** only stored during Lighthouse audit runs (unless `always_capture` is enabled). It records server-side performance metrics, not user behavior.

**Retention:** All records have configurable retention. The default is 90 days for both audits and RUM events. Older records are automatically removed by Laravel's `model:prune` command.

**GDPR posture:** Because no PII is collected in RUM beacons, this data typically does not require GDPR consent mechanisms. However, consult your legal team for your specific jurisdiction and use case.

---

## Performance impact

The package is designed to add zero overhead to normal page loads.

**Middleware:** `CaptureVitalsTelemetry` is registered on the `web` group. On every request without the `X-Vitals-Audit-Id` header (which is only present during Lighthouse runs), the middleware returns immediately. The fast-path overhead is sub-microsecond.

**RUM script:** The `vitals-rum.js` bundle is approximately 4.25 kB gzipped. It is loaded with the `defer` attribute and only sends beacons after the page has fully loaded, using `sendBeacon` which does not block page unload.

**Dashboard assets:** The dashboard CSS and JS are served by dedicated package routes (`/vitals/_assets/...`) with long-lived cache headers. They are never loaded for non-dashboard routes.

**Database writes:** Each audit run writes to `vitals_audits`, `vitals_audit_recommendations`, and `vitals_backend_telemetry`. RUM events write to `vitals_rum_events`. For busy sites, consider routing these writes through Laravel's queue to avoid adding latency to the request cycle.

---

## Upgrading

**From any alpha version before alpha.15:**
Schema migrations were consolidated. Run `php artisan vitals:purge --demo` (if you only have demo data), or export your data first, then drop the four `vitals_*` tables and re-run `php artisan migrate`.

**From alpha.50 to alpha.51:**
New `vitals_rum_events` table. Run `php artisan migrate`. No data loss.

**From alpha.51 to alpha.52:**
Documentation-only release. No schema changes. No action required.

**General upgrade steps:**
```bash
composer update humantocomputer/laravel-vitals
php artisan migrate
php artisan vitals:boost:install --force  # update AI context files
php artisan vitals:doctor                 # verify everything is healthy
```

---

## Troubleshooting

**1. "URL [home] not found in config or database"**
You need to declare URLs in `config/vitals.php` before auditing them:
```php
'urls' => ['home' => '/'],
```
Then run `php artisan vitals:audit home --sync`.

**2. Lighthouse fails with a Chrome error in Docker / Linux**
Add `--no-sandbox` to the `chrome_flags` configuration:
```php
'drivers' => ['local' => ['chrome_flags' => ['--headless', '--no-sandbox']]],
```
This is required when running Chrome as root (common in CI containers).

**3. The dashboard shows "Access Denied"**
The dashboard uses a `viewVitals` gate. In environments other than `local`, you must define this gate in your `AppServiceProvider`:
```php
Vitals::authorize(fn ($user) => $user?->is_admin ?? false);
```

**4. RUM data is not appearing**
Check that `@vitalsRum` is in your `<head>` section and that `VITALS_RUM_ENABLED=true` is in your `.env`. Open the browser's network tab and look for a POST request to `/vitals/rum/ingest`. If it is blocked, check your Content Security Policy — the ingest endpoint is on the same origin, so it should not require any CSP adjustment.

**5. "php artisan vitals:doctor" shows failing checks**
Read the output — each failing line includes a remediation hint. Common fixes:
- `Run php artisan migrate` — you have unpublished migrations
- `Run npm run build` — the `dist/` assets are missing (only affects package maintainers)
- `Add VITALS_NOTIFICATIONS_MAIL_TO` — you enabled mail notifications but didn't set a recipient

---

## Pruning old data

All package models implement Eloquent's `Prunable` trait. Add this to your scheduler to automatically delete records older than `VITALS_RETENTION_DAYS` (default: 90 days):

```php
// routes/console.php or App\Console\Kernel
$schedule->command('model:prune', [
    '--model' => [
        \LaravelVitals\Models\Audit::class,
        \LaravelVitals\Models\Recommendation::class,
        \LaravelVitals\Models\BackendTelemetry::class,
        \LaravelVitals\Models\RumEvent::class,
    ],
])->daily();
```

---

## Translations

The dashboard and all recommendation text are available in four languages. The active language follows `app()->getLocale()`.

| Language | Code |
|---|---|
| English | `en` (default) |
| French | `fr` |
| German | `de` |
| Spanish | `es` |

To customize translations, publish the language files:

```bash
php artisan vendor:publish --tag=vitals-translations
```

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for setup instructions, code conventions, and how to add a new recommendation or a new RUM metric.

---

## License and credits

Laravel Vitals is open-source software released under the [MIT license](LICENSE.md).

Built with: [Livewire](https://livewire.laravel.com), [Flux](https://fluxui.dev), [Google Lighthouse](https://developer.chrome.com/docs/lighthouse/overview/), [web-vitals](https://github.com/GoogleChrome/web-vitals), [spatie/laravel-searchable](https://github.com/spatie/laravel-searchable), [spatie/laravel-onboard](https://github.com/spatie/laravel-onboard).
