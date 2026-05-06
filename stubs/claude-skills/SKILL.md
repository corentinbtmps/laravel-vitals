---
name: laravel-vitals
description: Use when working with the laravel-vitals package - running audits, interpreting Lighthouse results, fixing performance recommendations, configuring URLs, performance budgets, or backend telemetry capture. Triggers on "audit", "lighthouse", "performance", "core web vitals", "vitals dashboard", or commands starting with "vitals:".
---

# Laravel Vitals

This skill helps you work effectively with the `humantocomputer/laravel-vitals` package.

## When to invoke

- The user mentions performance audits, Lighthouse, Core Web Vitals, or PageSpeed.
- The user runs any `vitals:*` artisan command.
- The user asks to interpret or act on a recommendation from `vitals_audit_recommendations`.
- The user configures URLs (`config/vitals.php`), performance budgets, or backend telemetry.

## How to investigate a failed audit

1. Read the audit row first:
   ```php
   $audit = \LaravelVitals\Models\Audit::with(['url', 'telemetry'])->find($auditId);
   ```
2. Check `$audit->status`. If `failed`, read `$audit->error` — it contains the driver's exception message.
3. Check the raw Lighthouse JSON saved on disk:
   ```php
   $raw = \Illuminate\Support\Facades\Storage::disk(config('vitals.storage.disk'))->get($audit->report_path);
   ```
4. If using the `local` driver, ensure `node` + `lighthouse` are installed and on `$PATH`.
5. If using `pagespeed`, ensure `VITALS_PAGESPEED_API_KEY` is set and the URL is publicly reachable.

## How to act on a recommendation

1. Each `Recommendation` has `code_references[]` — these are real `file:line` pointers in the host app.
2. **Read those files with the Read tool BEFORE proposing changes.** Do not guess. The references include a `snippet` and a `hint`.
3. The `audit_key` tells you which type of issue:
   - `unused-javascript` / `unused-css-rules`: bundle code splitting, lazy-load.
   - `render-blocking-resources`: defer or move to async, or inline critical CSS.
   - `modern-image-formats`: convert to WebP/AVIF.
   - `offscreen-images`: add `loading="lazy"`.
   - `n-plus-one-detected`: add `with()` to the relevant Eloquent query.
   - `config-cache-disabled` / `route-cache-disabled`: add `php artisan config:cache` / `route:cache` to the deploy script.
   - `debug-on-prod`: set `APP_DEBUG=false` in `.env` of production.

## Backend telemetry quirks

- The `pagespeed` driver CANNOT capture backend telemetry (the X-Vitals-Audit-Id header isn't injectable through Google's API).
- N+1 detection is heuristic: if the same SQL pattern repeats above the configured threshold, it flags. Verify with `slow_queries` JSON before refactoring.
- `always_capture` mode in `vitals.telemetry` opts every web request into telemetry sampling. It has a non-zero overhead — recommend leaving it off unless the user wants Pulse-like continuous monitoring.

## Common gotchas

- `vitals:audit` runs sync by default for a single URL. For batches use `--all`. Add `--sync` to force inline execution.
- Migration filenames in this package end with `.php` (not `.php.stub`) because Spatie's `discoversMigrations()->runsMigrations()` requires plain `.php`.
- `Vitals::authorize(fn ($user) => ...)` controls dashboard access. By default the gate allows only `local` environment.
- The `playwright` driver is the recommended choice when `node` and the `playwright-lighthouse` npm package are available — more stable than the legacy local Lighthouse CLI.
- `vitals:doctor` is the first command to run on a fresh install — it verifies drivers, storage, notifications and telemetry sources.
- `vitals:demo` seeds 4 fictional URLs with 14 days of audit history; cleanup with `vitals:purge --demo`.

## Running and reading audits

```bash
# audit a single URL
php artisan vitals:audit home --sync

# audit all enabled URLs in batch
php artisan vitals:audit --all

# CI usage with budgets
php artisan vitals:audit --all --sync --fail-on-budget --format=junit > vitals-results.xml
```

```php
// Latest audit for a URL
\LaravelVitals\Models\Audit::with(['recommendations', 'telemetry'])
    ->whereRelation('url', 'label', 'home')
    ->latest('completed_at')
    ->first();
```
