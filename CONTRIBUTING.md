# Contributing to Laravel Vitals

Thanks for your interest! This package is in active alpha — contributions are welcome.

## Development setup

```bash
git clone https://github.com/corentinbtmps/laravel-vitals.git
cd laravel-vitals
composer install
npm install
npm run build
vendor/bin/pest
```

## Workflow

- Fork the repo, create a branch, open a PR against `main`.
- Branch naming: `feat/short-description` or `fix/short-description`.
- Run `vendor/bin/pest` and `vendor/bin/phpstan analyse` before pushing.
- Match existing code style: PHP 8.2+ strict types, PSR-12, Larastan level 8.
- New features need tests. Bug fixes ideally include a regression test.
- Keep PRs focused — one concern per PR.

## Design conventions

- All UI strings i18n'd via `__()` keys. Translations: EN/FR/DE/ES.
- Use design tokens: `ink-*` for neutrals, `accent-*` for the primary brand colour,
  `paper`/`canvas` for surfaces. Do not use `zinc-*` or raw `rose-*` in views.
- Flux Free components only. No Flux Pro.
- Cards use `rounded-2xl border border-ink-200/60 dark:border-ink-800/60 bg-paper dark:bg-ink-900`.
- Numbers use `tabular-nums`.
- Tables that overflow at small viewports get `overflow-x-auto` with bleed padding.

## Adding a new recommendation

1. Add a constant to `RecommendationRegistry` with `audit_key`, `category`, `severity`, and translation keys.
2. Add the English translation under `lang/en/vitals.php` in the `recommendations` block, then FR/DE/ES.
3. Add `RecommendationDocs` entry with `why`, `docs`, `good`/`bad` code examples.
4. Write a test in `tests/Feature/Recommendations/` covering the emit condition.

## How to add a new RUM metric

The `web-vitals@4` library supports LCP, INP, CLS, TTFB, and FCP out of the box. If you want
to add an additional custom metric (e.g. a User Timing mark), follow these steps:

1. **JS bundle** (`resources/js/rum.js`): import or create a new reporter. Custom metrics
   should call the same `send(metric)` function with a compatible payload shape.

2. **Ingest validation** (`src/Http/Controllers/RumController.php`): add your new metric name
   to the `'metric'` validation rule's `in:` list.

3. **Migration**: the `vitals_rum_events` table is designed to hold any metric name up to 8
   characters. If your metric key is longer, edit the migration (pre-1.0, source edit) and
   update the column width.

4. **Livewire page** (`src/Livewire/Pages/Rum.php`): add your metric to the `$metrics` array
   in `render()` and add a threshold entry to `$metricThresholds` in the view.

5. **Translations**: add the new metric name/description keys to all four `lang/` files (EN, FR, DE, ES).

6. **Tests**: add a test case in `tests/Feature/Http/RumControllerTest.php` covering
   ingestion of the new metric, and a Livewire test verifying the card renders.

7. **Rebuild**: `npm run build` to include any JS changes in `dist/vitals-rum.js`.

> Note: The `ALLOWED` list in `AssetController` does not need updating — asset routing uses
> the file name, and `vitals-rum.js` is already whitelisted.

## Reporting issues

[Open an issue](https://github.com/corentinbtmps/laravel-vitals/issues/new/choose)
using the relevant template (bug report, feature request, or question).

## Code of Conduct

Be kind. Technical disagreements are welcome — personal attacks are not.

## License

By contributing, you agree your contributions will be licensed under the project's MIT license.
