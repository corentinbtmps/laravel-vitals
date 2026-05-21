# Contributing to Laravel Vitals

Thanks for your interest! The package is in active alpha — contributions are welcome and reviewed promptly.

## Development setup

```bash
git clone https://github.com/corentinbtmps/laravel-vitals.git
cd laravel-vitals
composer install
npm install
npm run build
```

**Run the test suite:**
```bash
vendor/bin/pest
```

**Run static analysis:**
```bash
vendor/bin/phpstan analyse --no-progress
```

**Run both (same as CI):**
```bash
composer lint   # PHPStan + Rector dry-run
composer test   # Pest
```

Both must pass before submitting a PR.

## Workflow

1. Fork the repo, create a branch off `main`.
2. Naming: `feat/short-description` or `fix/short-description`.
3. Open a PR against `main`. Keep it focused — one concern per PR.
4. Include tests. New features require feature tests. Bug fixes ideally include a regression test that would have caught the bug.
5. All CI checks must be green before merge.

## Code style

- PHP 8.2+, strict types everywhere (`declare(strict_types=1);`), PSR-12 formatting.
- Larastan level 8 — no suppression comments without a clear explanation.
- Use `final` on all classes that are not designed for extension.
- No `array_*` functions when a collection method exists.

## UI and design conventions

- All user-visible strings go through `__()` translation keys. Add translations to all four language files: `lang/en/vitals.php`, `lang/fr/vitals.php`, `lang/de/vitals.php`, `lang/es/vitals.php`.
- Design tokens to use in Blade views:
  - **Neutrals:** `ink-*` (e.g. `text-ink-500`, `border-ink-200/60`). Never use `zinc-*`.
  - **Brand color:** `accent-*`. Never use raw `rose-*`.
  - **Surfaces:** `bg-paper` (light cards), `bg-canvas` (page background), `dark:bg-ink-900` (dark mode).
- Cards: `rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900`.
- Numbers: always add `tabular-nums` so digits align in tables.
- Tables that might overflow on mobile: wrap in `overflow-x-auto` with bleed padding.
- Use Flux Free components only. Flux Pro components are not available to all users.

## How to add a new recommendation

Recommendations appear in the `/vitals/recommendations` and `/vitals/learn` pages and are surfaced as code-level hints after each audit.

1. **Register it** — add the key to `src/Recommendations/RecommendationRegistry.php`. Each entry needs an `audit_key`, `source` (lighthouse/config/backend/static), `category` (performance/accessibility/best_practices/seo), and `severity` (info/warning/critical).

2. **Add translations** — add the recommendation title and description under the `recommendations` key in all four `lang/*/vitals.php` files.

3. **Add documentation** — add an entry to `src/Recommendations/RecommendationDocs.php` with `why` (one sentence), `docs` (a web.dev or Laravel docs URL), and optionally `good`/`bad` code examples.

4. **Add a code analyzer** (optional) — if the recommendation can point to a specific file and line in the host app, implement `LaravelVitals\Contracts\CodeAnalyzer` and register it in `config/vitals.php` under `analyzers.custom`.

5. **Write a test** — add a test in `tests/Feature/Recommendations/` that asserts the recommendation is emitted when the triggering condition is present.

## How to add a new RUM metric

The `web-vitals@4` library supports LCP, INP, CLS, TTFB, and FCP. To add a custom metric:

1. **JS bundle** (`resources/js/rum.js`): import or write a reporter that calls the same `send(metric)` function with a compatible payload shape (`{name, value, rating, attribution}`).

2. **Ingest validation** (`src/Http/Controllers/RumController.php`): add the new metric name to the `'metric'` validation rule's `in:` list.

3. **Migration**: the `vitals_rum_events.metric` column accepts up to 8 characters. If your key is longer, edit the source migration (pre-1.0, source edits are permitted per our migration policy) and update the column width.

4. **Livewire page** (`src/Livewire/Pages/Rum.php`): add your metric name to the `$metrics` array in `render()`.

5. **Translations**: add the metric name and description to all four `lang/*/vitals.php` files.

6. **Tests**: add ingestion coverage to `tests/Feature/Http/RumControllerTest.php` and a Livewire rendering test to `tests/Feature/Livewire/Pages/RumTest.php`.

7. **Rebuild assets**: `npm run build`.

## How to add a custom analyzer

Analyzers are the static code analysis step that runs after every Lighthouse audit. Each analyzer inspects your codebase — Blade views, `composer.json`, config files — and attaches file/line references to Lighthouse recommendations. There are currently nine built-in analyzers. Adding a tenth follows the same pattern.

**Step 1: implement `CodeAnalyzer`**

```php
// src/Analyzers/YourAnalyzer.php
final class YourAnalyzer implements \LaravelVitals\Contracts\CodeAnalyzer
{
    public function supports(string $auditKey): bool
    {
        // Return true for the Lighthouse audit keys this analyzer handles.
        return in_array($auditKey, ['some-lighthouse-audit-id'], true);
    }

    public function analyze(string $auditKey, array $auditData, AppContext $ctx): CodeReferenceCollection
    {
        // $auditData is the raw Lighthouse audit object (items, details, score, etc.)
        // $ctx->basePath is the root of the host application.
        // Return a collection of CodeReference objects.
        return new CodeReferenceCollection([
            new CodeReference(
                file: 'app/Http/Middleware/SomeMiddleware.php',
                lineStart: 14,
                lineEnd: 14,
                snippet: '// your offending code here',
                hint:    'Explanation of what to do differently.',
            ),
        ]);
    }
}
```

**Step 2: register it**

Open `src/VitalsServiceProvider.php` and add your analyzer to the `$analyzers` array inside the `RecommendationBuilder` singleton binding:

```php
$app->make(\LaravelVitals\Analyzers\YourAnalyzer::class),
```

Alternatively, add it to your host app's `config/vitals.php`:

```php
'analyzers' => [
    'custom' => [
        \App\Vitals\Analyzers\YourAnalyzer::class,
    ],
],
```

**Step 3: add a recommendation descriptor**

In `src/Recommendations/RecommendationDocs.php`, add an entry so the dashboard can surface documentation links, good/bad code examples, and action labels for your new recommendation key.

**Step 4: tests**

Create `tests/Unit/Analyzers/YourAnalyzerTest.php`. At minimum, test that `supports()` returns the right values and that `analyze()` returns `CodeReference` objects when it finds a pattern.

**Step 5: translations**

Add `vitals.recommendations.your-audit-key.title` and `vitals.recommendations.your-audit-key.description` to all four `lang/*/vitals.php` files.

---

## How to add a new SEO check

All SEO checks live in `src/Seo/Checks/{Category}/`. Follow these 5 steps:

**1. Create the check class** in the appropriate category folder:
```php
// src/Seo/Checks/Meta/MyNewCheck.php
final class MyNewCheck implements SeoCheck
{
    public function key(): string { return 'my-new-check'; }
    public function category(): SeoCheckCategory { return SeoCheckCategory::Meta; }
    public function weight(): int { return 7; }   // 1–10 importance

    public function run(SeoCheckContext $context): SeoCheckResult
    {
        // Use $context->crawler for DOM queries (Symfony DomCrawler)
        // Use $context->response for HTTP headers
        // Use $context->report for Lighthouse metrics
        // Return SeoCheckResult::pass/fail/warning(...)
    }
}
```

**2. Register the check** in `src/Seo/SeoCheckRegistry::all()` — add it to the appropriate category group.

**3. Add a `docUrl`** pointing to a real Google developer docs URL (developers.google.com/search/docs/...).

**4. Add translation keys** in all 4 locale files (`lang/{en,fr,de,es}/vitals.php`):
```php
'seo' => [
    'checks' => [
        'my-new-check' => [
            'title'       => 'Human-readable check name',
            'description' => 'What this check verifies.',
            'hint'        => 'How to fix it.',
        ],
    ],
],
```

**5. Write tests** in `tests/Unit/Seo/Checks/{Category}/MyNewCheckTest.php` covering: pass, fail, warning, edge cases (empty page, missing elements). Use `SeoTestHelper::makeContext()` to build a context without HTTP.

Verify: `vendor/bin/pest tests/Unit/Seo/` passes, `vendor/bin/phpstan analyse src/Seo/` returns no errors.

---

## Reporting issues

[Open an issue](https://github.com/corentinbtmps/laravel-vitals/issues/new/choose) using the relevant template (bug report, feature request, or question). Include your Laravel version, PHP version, Vitals version, and driver type.

## Code of Conduct

Be kind. Technical disagreements are welcome and encouraged — personal attacks are not.

## License

By contributing, you agree your contributions will be licensed under the project's MIT license.
