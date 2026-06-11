# Changelog

All notable changes to `corentinbtmps/laravel-vitals` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- **Playwright driver reported itself available with only a Node binary present.** `isAvailable()` now also verifies that the `playwright` and `playwright-lighthouse` npm packages resolve, so the `auto` chain no longer stops on Playwright and then dies at runtime with `ERR_MODULE_NOT_FOUND`. The Node runner also now prints an actionable install message instead of a raw module-resolution stack trace.
- **`php artisan view:cache` crashed with `Unable to locate a class or view for component [flux::chart.bar]`** when Flux Pro was not installed. The Flux Pro chart views referenced Pro-only components that the view cache compiles unconditionally; they have been removed.

### Changed

- **Charts now always render with ApexCharts.** Flux Pro chart support (`FluxProChartsRenderer`, the `charts/flux/*` views, the `livewire/flux-pro` suggestion, and the `vitals.ui.charts` config key) has been removed. Flux Free remains the dashboard UI.
- Driver-resolution failures now surface as actionable errors. `vitals:doctor`, the audit commands, and the `auto`/explicit resolution errors all spell out the exact command needed to enable each driver (e.g. `npm install --save-dev playwright playwright-lighthouse && npx playwright install chromium`).

### Upgrade notes

- **Audits silently doing nothing, or failing with a Node/Lighthouse error?** Run `php artisan vitals:doctor` and follow the install hint for your chosen driver. A Node binary alone does **not** make the Playwright driver work — it also needs `npm install --save-dev playwright playwright-lighthouse` followed by `npx playwright install chromium`. See [Driver installation](README.md#driver-installation).
- If you previously set `vitals.ui.charts` in your published config, the key is now ignored and can be removed; ApexCharts is always used.

## [v1.0.0] - 2026-05-22

First stable release. A Laravel-native, mono-project performance audit dashboard built on Livewire 4 + Flux Free 2 + Tailwind 4, with full support for Laravel 11 / 12 / 13.

### Audit drivers

- **Lighthouse audits** with four interchangeable drivers (`local`, `playwright`, `pagespeed`, `stub`). Each driver returns Performance / Accessibility / Best Practices / SEO scores plus raw metric values (LCP, INP, CLS, TTFB, FCP, TBT, Speed Index).
- **Backend telemetry** — query count, query time, N+1 suspicion, peak memory, views rendered, jobs dispatched, events fired, cache hits/misses. Sampled via a signed `X-Vitals-Audit-Id` header so production traffic is never affected.
- **Code analysis** — static scanners attach exact `file:line` references to Lighthouse findings by reading Blade views, the Vite config, and `composer.json`.

### Surfaces

- **`/vitals` (Overview)** — health snapshot across all monitored URLs with sparkline trends.
- **`/vitals/urls` + `/vitals/urls/{id}`** — per-URL detail with audit history, average scores, period filter, metric toggle (Score / LCP / INP / CLS / TTFB), and quick-links to SEO / Queries / RUM.
- **`/vitals/audits/{id}`** — full audit detail with score gauges, Core Web Vitals cards, front-end ↔ back-end breakdown, severity-tinted recommendation cards, resource breakdown, third-party impact, main-thread timing, and slowest requests.
- **`/vitals/audits/{id}/seo`** — per-audit SEO deep view, checks grouped by category.
- **`/vitals/issues`** — Top issues (insights / regressions / worsening URLs) + All recommendations (severity-tinted card list).
- **`/vitals/issues/{auditKey}`** — "Where this happens" deep view grouped by URL with code references and N+1 SQL patterns.
- **`/vitals/seo`** — cross-URL SEO overview with per-URL score table, top failing checks, category filter.
- **`/vitals/rum`** — Real User Monitoring: Core Web Vitals from real visitors, per-URL breakdown, INP attribution (element + event type).
- **`/vitals/queries`** — per-route query baseline with N+1 surfacing. Click a route to expand a panel with affected URLs, recent audits, and repeated SQL patterns.
- **`/vitals/learn`** — knowledge base of every detectable issue, browsable by category (Performance / Accessibility / Best Practices / SEO), severity-tinted cards with docs and Good/Bad code snippets.
- **`/vitals/budgets`** — per-metric budget thresholds with violation history.
- **Spotlight (Cmd/Ctrl+K)** — global search across audits, URLs, recommendations, learn entries.

### SEO checks subsystem

22 checks aligned with Google's 2026 best practices — every one a documented ranking signal, no Yoast-style heuristics:

- **Configuration (3)** — `noindex`, `nofollow`, `robots-txt-indexable`.
- **Content (5)** — `h1`, `https-links`, `image-alt`, `broken-links` (sampled), `broken-images`.
- **Meta (7)** — `meta-description`, `title-length`, `og-image`, `html-lang`, `canonical`, `structured-data` (JSON-LD), `invalid-head-elements`.
- **Performance (7)** — `ttfb` (≤ 600ms), `status-code`, `html-size`, `image-size`, `js-size`, `css-size`, `compression`.

The `Audit::vitals_seo_score` accessor blends Lighthouse SEO (50%) with weighted custom-check pass rate (50%), producing a stricter 0–100 score.

### RUM (Real User Monitoring)

- `@vitalsRum` Blade directive emits a lightweight (~11 KB gzip 4 KB) snippet using `web-vitals` v4 with attribution data.
- Privacy-respecting: no IP storage, no fingerprinting.
- INP attribution: extracts the slowest interaction's target selector + event type so you can fix the right element.

### Developer affordances

- **Open in editor** — file:line links jump straight into the IDE. Built-in presets for 11 editors (VSCode, Cursor, PHPStorm, IntelliJ IDEA, Sublime Text, Atom, TextMate, MacVim, Emacs, Nova, Zed). Custom templates via `VITALS_EDITOR_URL_TEMPLATE`.
- **JSON API v1** — `/vitals/api/v1/{audits,urls,recommendations}` with pagination, date filtering, and `viewVitals` Gate-protected access. Stable surface for Datadog / Sentry / custom dashboards.
- **Demo seeder** — `php artisan vitals:demo` populates synthetic data so the dashboard is explorable before any real audits run.

### Notifications

- Email + Slack channels for Regression Detected and Budget Violated events.
- Slack messages thread per URL (subsequent regressions reply to the original post).

### i18n

Full UI in EN, FR, DE, ES — ~1100 translation strings across pages, components, recommendations, SEO check titles/descriptions/hints, and tooltips.

### Theme

- OKLCH-native palette: rose `accent` (brand), tinted-neutral `ink` (replaces zinc), `paper` + `canvas` surfaces.
- Self-hosted Geist Sans + Geist Mono via `@fontsource-variable`.
- Light + dark mode driven by a `.dark` class on `<html>`, persisted via the dark-mode toggle in the navbar.
- Flux semantic accent tokens (`--color-accent`, `--color-accent-content`, `--color-accent-foreground`) wired to the rose scale so `flux:link` / `flux:button variant="primary"` use the brand color.

### Dependencies

Hard requirements: PHP 8.2+, Laravel 11/12/13, Livewire 4, Flux Free 2, `spatie/laravel-onboard`, `spatie/laravel-searchable`, `spatie/laravel-package-tools`, `symfony/dom-crawler`.

Optional: `web-vitals` (npm, bundled), ApexCharts (bundled), Playwright (driver fallback).

### Configuration surface

Single `config/vitals.php` covers: audit drivers, retention, RUM settings, notifications, budgets, SEO thresholds + `disabled_checks`, editor presets, UI tweaks. ~30 env-overridable knobs total, with sensible defaults for every key.

### Tests

550+ Pest tests (unit + feature + integration) covering audit pipelines, SEO checks, Livewire components, JSON API, notifications, telemetry capture, and budget evaluation. PHPStan level max, no baseline.
