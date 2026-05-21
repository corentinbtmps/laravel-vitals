# Changelog

All notable changes to `humantocomputer/laravel-vitals` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v1.0.0-alpha.83] - 2026-05-22

### Fixed

- **Issues page tabs margin**: `flux:radio.group variant="segmented"` renders as `display: block flex` so the container stretches to its parent's full width when not inside a flex parent. The Issues page had the radio.group as a direct block child of `<div class="space-y-6">`, making it sprawl across the row. Wrapped in a `flex justify-between` row alongside the title + added `shrink-0` so the segmented control hugs its content.

### Changed

- **Learn page category tiles** — were tinted only on hover (neutral `bg-paper` / `border-ink-200` at rest). Now persistently tinted to match the audit-detail CWV card style (e.g. emerald: `bg-emerald-50/40 border-emerald-200`, sky/violet/accent equivalents). `LearnTileClasses::tileBg()` / `tileBorder()` added; `hoverBorder()` removed (no longer needed since border is persistent); `hoverBg()` intensifies from `50/30` to `100/50` on hover for the active feedback.



### Fixed

#### Segmented selectors actually segment now

`flux:button.group` doesn't produce the segmented look when its children mix `variant="primary"` (active) and `variant="ghost"` (inactive) — the `ghost`/`subtle` variants have no group-aware border treatments, so the buttons rendered as separate floating buttons with no shared container.

The correct Flux Free pattern for a single-select segmented control is `flux:radio.group variant="segmented"` — its data-checked CSS handles the active state and the container styling without any per-child variant juggling.

Migrated all 7 segmented controls from `flux:button.group` to `flux:radio.group`:

- Period selectors on `overview`, `url-detail`, `seo`, `rum`, `queries`
- RUM device filter (all / mobile / desktop)
- URL detail metric toggle (Score / LCP / INP / CLS / TTFB)
- SEO category tabs (all / configuration / content / meta / performance)
- Issues page tabs (Top / All)

Each now uses `wire:model.live="<property>"` instead of per-button `wire:click="setX(value)"`. The action methods on the Livewire components (`setPeriod`, `setCategory`, `setMetric`, `setDevice`, `setTab`) were removed; any side effects (e.g. `dispatchChartUpdate` in `UrlDetail`, resetting `$selectedRoute` in `Queries`, dispatching `sparklineUpdated` in `Overview`) moved to Livewire's `updated<Property>` lifecycle hooks.

Special case: RUM's `$device` is a nullable `Device` enum but the radio needs a string. Added `$deviceFilter` string property bound to the radio with `updatedDeviceFilter` translating to the enum.

### Removed

- `setPeriod`, `setCategory`, `setMetric`, `setDevice`, `setTab` Livewire actions (replaced by `wire:model.live` + `updated*` hooks).
- "Ignores invalid period values" tests on `QueriesTest` and `RumTest` — they covered defensive guards in the removed setter methods. The Flux radio UI only emits valid enum values; programmer error from PHP-side `->set('period', 'garbage')` now surfaces as a `ValueError` (preferred — silent fallback was masking bugs).



### Changed

#### Flux UI sweep — second pass

Continuing from alpha.80, replacing more raw HTML with Flux components:

- **Issues page tabs** (Top / All) raw `<button>` → `flux:button.group` + `flux:button` for visual consistency with all other selectors in the package.
- **URLs list star/pin buttons** (raw `<button>` with conditional star icon and color) → `flux:button` with `icon:variant` toggling solid/outline based on pinned state, tooltip via `:tooltip` prop.
- **Mobile drawer nav** — completely restructured from custom `<a>` items to `flux:sidebar.nav` + `flux:sidebar.group` (with `:heading` prop) + `flux:sidebar.item` (with `:current` and `icon` props). Group headings, active state, and item styling now come from Flux instead of hand-rolled CSS.
- **Mobile header buttons** (search icon, burger toggle, drawer close) → `flux:button variant="ghost"`.
- **Caller editor links** in `audit-detail` (×2) and `issue-detail` (×1) → `flux:link` with `:tooltip="__('vitals::vitals.actions.open_in_editor')"`; the `<flux:icon arrow-top-right-on-square>` external indicator is preserved inside the link content.
- **`code-reference` blade component** — the editor `<a>` wrapping `file.php:line` text → `flux:link`.
- **Subtle navigation anchors** across `urls-list`, `overview`, `url-detail`, `audit-compare`, `recommendations-index`, `insights`, `self-check`, `status` — every `<a class="hover:text-accent-… hover:underline">` pattern replaced with `<flux:link variant="subtle">` (or `variant="ghost"` for block-level multi-line link cards in `urls-list`).

### Remaining raw HTML (intentional, no Flux equivalent)

- `learn.blade.php` clickable tile cards — Flux has no "clickable content card" component.
- `dashboard.blade.php` desktop Spotlight trigger — bundles label + cross-platform `kbd` (⌘/Ctrl swap via `x-init`) which Flux's `kbd` prop can't dynamically swap.



### Fixed

#### Onboarding "Configure" button no longer dead-ends

- The notifications onboarding step previously linked to `/vitals` (the same page the banner lives on), so clicking "Configure" reloaded the dashboard with no useful effect. Notifications are config-file driven (no UI yet), so the step now:
  - drops the `->link()` call (so `$step->link` is null)
  - sets a new `config_only` attribute
  - the banner view renders a `flux:badge` ("Edit config file") instead of a dead button, while still showing the inline `config/vitals.php → …` hint code snippet

### Changed

#### Flux UI uniformization across the dashboard

Per established preference: prefer Flux components over raw HTML wherever a Flux equivalent exists. Done in this pass:

- **Period selectors** on `overview`, `url-detail`, `seo`, `rum`, `queries` migrated from custom-styled raw `<button>` to `flux:button.group` + `flux:button` (variant primary for active, ghost for inactive). All five pages now render identically.
- **RUM device filter** (all / mobile / desktop) same migration.
- **URL detail metric toggle** (Score / LCP / INP / CLS / TTFB) same migration; the per-button `flux:tooltip` wrapper collapses into the button's `:tooltip` prop.
- **SEO category tabs** (all / configuration / content / meta / performance) same migration.
- **Onboarding banner**: dismiss `<button>` → `<flux:button variant="ghost" size="xs">`; CTA `<flux:button variant="filled">` → `variant="primary"` for consistency with other primary buttons.
- **Accent-colored anchors** (raw `<a class="text-accent-600 hover:…">`) replaced with `<flux:link>` on the SEO per-URL row, SEO top-failing row, queries route-detail "Affected URLs" + "Recent audits" + repeated SQL pattern caller links, and the audit-SEO check `Read docs` external link.



### Added

#### Queries page — clickable routes + per-route drill-down + N+1 surfacing

- **Click any route** to expand a per-route detail panel below the table: affected URLs (linking to URL detail), recent audits (linking to audit detail), and repeated SQL patterns aggregated from N+1 recommendation data with file:line caller links via `EditorUrl`.
- **N+1 badge** rendered on table rows whose route has any audit with `n_plus_one_suspect=true` in the period.
- **`?route=` query param** persists the selected route via the Livewire `#[Url]` attribute so detail panel deep-links work.

#### Internal maillage — cross-page navigation

- **URL detail** gains a quick-links bar after the hero: Latest audit, SEO checks (per-audit), Database queries, Live performance (RUM).
- **SEO cross-URL table**: the URL label is now a link to URL detail (in addition to the right-side "View →" landing on audit-seo).
- **Issue detail**: when `audit_key='n-plus-one-detected'`, a banner appears at the top explaining N+1 and linking to the Database queries page.

### Changed

#### Plain-language labels everywhere — Typical / Worst case instead of p75 / p95

- Queries page table columns: `p75 queries` → **Typical queries**, `p95 queries` → **Worst case**, `p75 time` → **Typical time**, `p95 time` → **Worst time**. New `legend` row beneath the table explains the terms.
- RUM hero cards label `p75` → **Typical (75% of users)**.
- RUM per-URL and INP-attribution tables drop the `p75` suffix from column headers (`LCP p75` → `LCP`, etc.) — the Core Web Vitals metric names already imply p75 by convention.
- Queries page subtitle + baseline subtitle rewritten in plain language (no statistical terms in user copy).
- New translation key `rum.typical_value` and Queries `col_typical_queries` / `col_worst_queries` / `col_typical_time` / `col_worst_time` / `legend` keys across EN/FR/DE/ES.

#### Visual consistency — solid borders package-wide

- All `border-ink-200/60 dark:border-ink-800/60` occurrences across blade views (audit-detail, audit-compare, budgets, insights, issues, learn, overview, recommendations-index, self-check, status, url-detail, urls-list, spotlight components, dashboard layout) switched to solid `border-ink-200 dark:border-ink-800`. Eliminates the inconsistent contrast in light mode that the user reported in alpha.78 review.

### Fixed

- **Queries view** is no longer a dead-end. The previous version listed routes as static strings with no drill-down, no N+1 connection, and no way to investigate. The new per-route panel surfaces the underlying SQL patterns and the URLs that actually hit the route.



### Fixed

#### SEO / Queries views — visual polish + correct counts + useful links

- **Borders**: `/vitals/seo`, `/vitals/audits/{id}/seo`, and `/vitals/queries` now use solid `border-ink-200 dark:border-ink-800` (matching the issue-detail page from alpha.67) instead of `/60` opacity variants that were inconsistently contrasted in light mode.
- **Top failing counts (161 vs 40 bug)**: the `/vitals/seo` "Top failing checks" badge was running `COUNT(*)` over every audit row in the period, so a URL with N historical audits contributed N occurrences. Now restricts the aggregation to the **latest audit per URL** — counts align with the per-URL table above and the badge reads `× N URLs` for clarity.
- **Top failing "View" link**: was routing to `/vitals/issues/{audit_key}` ("Where in my code") which is built for Lighthouse/code findings and offered no value for SEO checks. Now lands on `/vitals/audits/{audit}/seo` of a sample affected audit — a useful destination.

### Changed

#### Navigation grouping

- Desktop navbar gains thin vertical dividers separating three clusters: **Overview** | **URLs · Issues · SEO** (audit findings) | **RUM · Queries** (live telemetry) | **Learn · Budgets** (reference).
- Mobile drawer adds uppercase group labels above each cluster (`group_audit`, `group_telemetry`, `group_reference` translation keys, EN/FR/DE/ES).


## [v1.0.0-alpha.77] - 2026-05-21

### Removed

#### Yoast-style opinion checks dropped — V1 keeps Google-validated tests only

- **Deleted 3 opt-in checks**: `ContentLengthCheck`, `KeywordInTitleCheck`, `KeywordInFirstParagraphCheck`. They were Yoast-inspired heuristics, not official Google ranking signals, and added configuration surface for marginal value.
- **Config simplified**: removed `vitals.seo.enable_opinion_checks` (and the `VITALS_SEO_OPINION_CHECKS` env var), `vitals.seo.keywords`, and the `thresholds.content_min_chars` key.
- **Interface cleanup**: `SeoCheck::isOptional()` removed from the contract and from all 22 remaining checks; `SeoCheckRegistry::enabled()` now only consults `disabled_checks`.
- **i18n**: dropped the 3 translation blocks (`content-length`, `keyword-in-title`, `keyword-in-first-paragraph`) across EN/FR/DE/ES.
- **Tests**: deleted the 3 check unit-test files; registry test updated for 22 checks. Total drops from 557 to 552.

### Changed

- **DemoSeeder**: distributes synthetic SEO check failures (`seo-meta-description`, `seo-image-alt`, `seo-h1`) so the new `/vitals/seo` page has demo data out of the box. Bad-profile audits get three SEO findings; healthy audits get one or none.

### Fixed

- **DemoSeeder**: removed two unreachable `match` arms (`modern-image-formats`, `unused-css-rules`) — they referenced audit keys never picked by the `mt_rand(2, 4)` selector. Clears two `match.alwaysFalse` PHPStan errors.

## [v1.0.0-alpha.76] - 2026-05-21

### Added

#### SEO checks subsystem — 25 Google-aligned checks + cross-URL /vitals/seo page

- **`symfony/dom-crawler` dependency** added for HTML parsing in content checks.
- **`src/Seo/` subsystem** — full SEO check architecture:
  - `SeoCheck` interface, `SeoCheckResult` + `SeoCheckContext` value objects
  - `SeoCheckStatus` and `SeoCheckCategory` PHP 8 enums
  - `SeoCheckRegistry` respecting `disabled_checks` + `enable_opinion_checks` config
  - `SeoAuditor` orchestrator — fetches HTML, parses DOM, runs checks, persists results as `Recommendation` rows with `source='seo'`
- **22 mandatory checks** (always run unless disabled):
  - Configuration (3): `noindex`, `nofollow`, `robots-txt-indexable`
  - Content (5): `h1`, `https-links`, `image-alt`, `broken-links` (samples ≤ 30 links), `broken-images`
  - Meta (7): `meta-description`, `title-length`, `og-image`, `html-lang`, `canonical`, `structured-data` (JSON-LD), `invalid-head-elements`
  - Performance (7): `ttfb` (≤ 600ms), `status-code`, `html-size`, `image-size`, `js-size`, `css-size` (opinionated threshold), `compression`
- **3 opt-in opinion checks** (gated behind `config('vitals.seo.enable_opinion_checks')`):
  - `content-length` (configurable minimum characters)
  - `keyword-in-title` (per-URL keyword via `config('vitals.seo.keywords')`)
  - `keyword-in-first-paragraph` (same keyword config)
- **Storage**: SEO results reuse `vitals_audit_recommendations` with `source='seo'`, `audit_key='seo-{check}'`. Migration updated to add `'seo'` to the source enum. Automatically surfaces in Issues page, issue-detail deep view, JSON API.
- **`Audit::vitals_seo_score` computed accessor** — blends Lighthouse SEO (50%) with custom check pass rate weighted by check importance (50%). Range 0–100, stricter than Lighthouse alone.
- **`/vitals/audits/{id}/seo` page rebuilt** with category-grouped checks (Configuration / Content / Meta / Performance), per-check actual vs expected values, hint text, and direct links to Google developer documentation.
- **New `/vitals/seo` cross-URL page** — period filter, average SEO score card, per-URL score table (grade + numeric + failing count), top failing checks aggregation with category filter tabs.
- **Navigation item**: "SEO" added between Issues and RUM in desktop navbar and mobile drawer.
- **Config** `vitals.seo` section with all thresholds, `enabled`, `disabled_checks`, `enable_opinion_checks`, `keywords` per URL.
- **76 new tests**: unit tests for all checks + registry, feature tests for SeoAuditor and both Livewire pages. Total: 481 → 557.
- **i18n**: ~120 new translation strings across EN/FR/DE/ES covering all 25 check titles, descriptions, hints, and page labels.

### Changed

- `RunAuditJob::handle()` gains a `SeoAuditor` parameter (injected by the container; dispatched jobs are unaffected).
- `vitals_audit_recommendations.source` enum extended to include `'seo'`.

## [v1.0.0-alpha.73] - 2026-05-15

### Added

#### Open in editor — jump from any file:line reference into the IDE

- **`EditorUrl` helper** (`src/Support/EditorUrl.php`) with built-in URL templates for 11 editors: VSCode, Cursor, PHPStorm, IntelliJ IDEA, Sublime Text, Atom, TextMate, MacVim, Emacs, Nova, Zed. Same preset list as Spatie Ignition / Symfony VarDumper.
- **New config keys** in `vitals.ui`:
  - `editor` — select a preset via `VITALS_EDITOR=vscode` (or any of the 11 supported names).
  - `editor_url_template` — custom URL scheme via `VITALS_EDITOR_URL_TEMPLATE`; overrides the preset for full flexibility.
  - Existing users with a hand-written `VITALS_EDITOR_URL_TEMPLATE` continue to work without any change.
- **"Open in editor" links** added to every file:line surface in the dashboard:
  - `code-reference` component — recommendation source pointers (previously used raw config; now delegates to `EditorUrl::for()`).
  - N+1 caller attribution in the front-end ↔ back-end correlation panel on `audit-detail`.
  - N+1 repeated queries in the recommendations section on `audit-detail`.
  - N+1 repeated queries on `issue-detail` per-occurrence panel.
  - When no editor is configured the file:line text remains visible as plain `<code>` — no regression.
- **i18n**: `actions.open_in_editor` key added in EN ("Open in editor"), FR ("Ouvrir dans l'éditeur"), DE ("Im Editor öffnen"), ES ("Abrir en el editor").
- **10 new unit tests** covering all 11 presets, custom template override, null/empty config, absolute vs relative path handling, and default line number.
- **README** updated with a new "Open in editor" section under Configuration documenting both env vars and all supported preset names.

## [v1.0.0-alpha.63] - 2026-05-15

### Added

#### Job 1 — Deep "Where in my code" navigation

- **New page `/vitals/issues/{auditKey}`** (`IssueDetail` Livewire component). One click from the Issues → All Recommendations list lands on a page showing every `(url, file, line)` occurrence grouped by URL, with code references and a "View audit" button per occurrence.
- **New route** `GET /vitals/issues/{auditKey}` → `vitals.issue.detail`.
- **Issues page** (All Recommendations tab): recommendation title now links to the new page instead of the Learn anchor. Action button changes from `book-open` to `map-pin`.
- **Audit-detail** recommendation cards gain a "View all occurrences" link in the top-right corner of each reco card.
- **Learn page** entries: each entry now shows an "X active in your app" badge (when > 0 occurrences) that links to the issue detail page.
- **Spotlight/Cmd+K** recommendation results now point to `/vitals/issues/{audit_key}` instead of the Learn anchor.
- Breadcrumb on IssueDetail: `Issues › {Recommendation title}`.

#### Job 2 — N+1 query attribution

- **`queries_log` JSON column** added to `vitals_backend_telemetry` (folded into source migration `2026_05_05_000004`). Stores up to 200 normalized query patterns per request with `sql`, `bindings_count`, `time_ms`, `caller_file`, and `caller_line`.
- **`TelemetryRecorder`** now captures a `queries_log` buffer alongside the existing `QueryAccumulator`. Caller resolution uses `debug_backtrace()` and skips all `/vendor/` and `/laravel-vitals/src/` frames, pointing to the first frame in the host application.
- **SQL normalization** replaces numeric literals with `?` for pattern grouping; capped at 500 chars.
- **`BackendTelemetrySnapshot`** gains a `queriesLog` property; `PersistTelemetryJob` persists it.
- **`BackendTelemetry` model** gains `queries_log` property and `array` cast.
- **`RecommendationBuilder`**: N+1 recommendation now includes `top_patterns` in `translation_params` — top 3 most-repeated normalized SQL patterns with occurrence count and caller file:line.
- **Audit-detail view**: N+1 callout and the N+1 recommendation card both render the "Repeated queries" panel showing the top patterns with SQL, occurrence count, and caller location.
- **IssueDetail page**: `/vitals/issues/n-plus-one-detected` also renders the patterns per occurrence.

#### Job 3 — Grade consistency

- **`Audit` model** gains two computed accessors: `global_grade` (letter from avg of 4 scores) and `performance_grade` (letter from `score_performance` alone). Both return `null` when scores are unavailable.
- **URLs list** (`urls-list.blade.php`): two new columns added before URL — `Global` (overall grade letter badge) and `Perf` (performance grade letter badge), both colored by `Health::colorForScore`.
- **Audit-detail hero** (`audit-detail.blade.php`): performance grade is now shown side by side with the global grade in the top-right area. Each of the 4 per-axis score cards gains its own grade letter in the top-right corner.

### Changed

- `RecommendationSearchAspect` now wraps results as `SearchableItem` objects with URLs pointing to the issue detail page.
- `Learn` component computes per-key active counts for the badge.

### Tests

- Added `tests/Feature/Models/AuditGradesTest.php` — 8 tests covering `global_grade` and `performance_grade` accessors.
- Added `tests/Feature/Telemetry/QueriesLogTest.php` — 6 tests covering capture, cap enforcement, normalization, caller resolution, and reset behaviour.
- **Test count: 436 → 450** (+14).

### Translations

New keys added to EN/FR/DE/ES:
- `tables.global`, `tables.perf_grade`
- `audit_detail.repeated_queries`
- `learn_page.active_in_app`
- `issue_detail.*` (6 keys)

## [v1.0.0-alpha.55] - 2026-05-14

### Changed

- **README rewritten as a clean v1.0 document.** Removed the "Upgrading" section entirely (the package has not shipped a stable release, so there is nothing to upgrade from), removed all "since alpha.X" and "in newer releases" annotations, and rewrote every feature section in present tense. Every route, command, and config key verified against the live source. Word count: ~5,700 (within the 4,500–6,000 target).
- **Logo and navbar spacing.** Logo link gains `mr-8 xl:mr-10` so it breathes away from the nav items at both breakpoints. Navbar gains `gap-1` so items no longer touch at the standard gap.

## [v1.0.0-alpha.54] - 2026-05-14

### Changed

#### Nav simplification — 8 → 7 top-level items

- **Merged Insights + Recommendations into a single Issues page** at `/vitals/issues` with two tabs: "Top issues" (cross-URL quick wins, worsening/improving URLs, third-party costs) and "All recommendations" (aggregated across audits, sorted by frequency). Saves one nav slot without losing any data.
- Old routes `/vitals/insights` and `/vitals/recommendations` now 301-redirect to `/vitals/issues?tab=top` and `/vitals/issues?tab=all` respectively. Route names `vitals.insights` and `vitals.recommendations` are preserved so any `route('vitals.insights')` call in host apps keeps working.
- New nav order: **Overview · URLs · Issues · RUM · Queries · Learn · Budgets** (was 8 items).
- **Search button moved right of the spacer** — it now appears right-aligned in the header alongside the dark mode toggle, matching Linear/Notion convention. Was previously left-of-spacer.

#### Centralized page subtitles

- Added `vitals::vitals.pages.X.title` and `vitals::vitals.pages.X.subtitle` translation keys for all 7 top-level pages (EN/FR/DE/ES).
- Overview, URLs, Learn, and Budgets pages now read their title/subtitle from the centralized keys.
- Added `vitals::vitals.nav.issues` translation key (EN/FR/DE/ES) for the new Issues nav item.

#### Overview de-duplication

- "Active alerts" section now shows a "View all issues →" link when 3+ alerts are present, pointing to `/vitals/issues?tab=top`.
- "Top recommendations" section now shows a "View all recommendations →" link pointing to `/vitals/issues?tab=all`.

### Added

- New Livewire page `Issues` (`LaravelVitals\Livewire\Pages\Issues`) with `#[Url]`-backed tab state.
- New route `GET /vitals/issues` → `vitals.issues`.
- New tests: `IssuesTest` (5), `RedirectsTest` (3), `SearchButtonPositionTest` (1) — 9 new tests total.

## [v1.0.0-alpha.53] - 2026-05-10

### Added

#### Audit comparison (feature 1)
- New Livewire page `AuditCompare` at `/vitals/audits/{a}/compare/{b}` — side-by-side score grid (4 metrics × 2 audits) with `▲ +N` / `▼ -N` / `→` delta badges.
- CWV grid (LCP/INP/CLS/TTFB) with ms formatting and directional indicators.
- Recommendation diff: "Resolved in B" (was in A, not in B) and "New in B" (was not in A, is in B).
- Telemetry diff table: queries, query time, peak memory, view render time.
- "Compare with previous" icon button added to every row in the UrlDetail audit history table.

#### Request trace waterfall (feature 2)
- Audit-detail page gains a "Request trace" panel when `events_log` data is present.
- Inline SVG waterfall — each event is a colored bar positioned by start time, length = duration. No JS library.
- Color coding: query=accent, view=violet, cache=emerald, job=amber.
- New `events_log` JSON column added to `vitals_backend_telemetry` source migration.

#### Public health endpoint (feature 3)
- `GET /vitals/health` — public JSON endpoint, no auth gate. Returns `status`, `timestamp`, `checks`, and `version`.
- Checks: `database`, `drivers` (per driver: ok/warn/skip), `queue`, `telemetry_buffer`.
- HTTP 200 for all ok/skip, HTTP 503 for any error.

#### PageSpeed API cost tracking (feature 4)
- New `api_call_count` (INT) and `api_call_cost` (DECIMAL 10,4) columns on `vitals_audits` source migration.
- `PageSpeedApiDriver` increments `api_call_count` on each successful API call.
- New "API usage this month" panel on the Overview page (budget bar showing calls / 25,000 monthly limit).

#### Rate limiting on vitals:audit (feature 5)
- `vitals:audit` acquires a `Cache::lock("vitals:audit:{url_id}", ttl)` before running.
- If another process is already auditing the same URL, the command exits with code 75 (EX_TEMPFAIL) and a clear message.
- New `--force` flag bypasses the lock (used in tests and CI override scenarios).
- Lock TTL configurable via `config('vitals.audit_timeout_seconds', 300)`.

#### Self-monitoring (feature 6)
- New `vitals:self-check` command — checks table sizes and slowest 10 telemetry requests.
- Exits with FAILURE when any table exceeds threshold or slowest request > 2000ms.
- New admin panel at `/vitals/admin/self-check` (Livewire page, behind Authorize gate).

#### Public status page (feature 7)
- New `Status` Livewire page at `/vitals/status` — uses `vitals::layouts.public` minimal layout.
- Opt-in via `config('vitals.status.enabled', false)`.
- Shows: app name, uptime % (last 30 days), CWV split (good/needs-improvement/poor), recent incidents, last updated.
- Brand-customizable via `config('vitals.status.title/description/logo_url')`.
- New `vitals::layouts.public` Blade layout created.

#### Trends overlay — compare 2 periods (feature 8)
- `Overview::previousMetricTrends()` computes sparkline data for the previous period.
- Both datasets passed to the view as `metricTrends` (current) and `previousMetricTrends` (previous).

#### Daily summary card on Overview (feature 9)
- Horizontal narrative card just below lens cards: "Yesterday — 142 audits run · 3 regressions detected · 2 fixed · LCP improved 8% on average".
- Computed from yesterday's audits vs. their prior baselines.
- New `dailySummary()` private method on `Overview`.

#### Demo seeder enrichment (feature 10)
- `DemoSeeder` now generates 4 URLs × 30 days × 2 devices = 240 audits (was 4 × 14 × 2 = 112).
- Realistic patterns: weekend traffic dip (minor perf improvement), occasional spikes (1-in-10 days), midweek regression on `dashboard` URL.
- ~50 RUM events per URL per day for 30 days.
- Memory peaks vary 20–80 MB.
- Idempotent: running `vitals:demo` twice truncates existing demo data first.

#### SEO deep dive page (feature 11)
- New `AuditSeo` Livewire page at `/vitals/audits/{audit}/seo`.
- Checks: meta description, canonical URL, structured data, HTML lang, title length, H1, sitemap, robots.txt.
- SEO recommendations filtered from the audit's recommendation set.
- Linked from audit-detail breadcrumbs.

#### Critical CSS analyzer (feature 12)
- New `CriticalCssAnalyzer` in the `CodeAnalyzer` pipeline.
- Parses Blade templates for class names in elements matching above-fold sentinels (hero, header, nav, banner, masthead).
- Generates "Inline critical CSS" recommendation with found class names.
- Registered in `VitalsServiceProvider` alongside the 7 existing analyzers.

#### Security headers analyzer (feature 13)
- New `SecurityHeadersAnalyzer` in the `CodeAnalyzer` pipeline.
- Checks CSP, HSTS, X-Frame-Options (or CSP frame-ancestors), X-Content-Type-Options, Referrer-Policy, Permissions-Policy.
- Each missing/weak header generates a recommendation entry with web.dev/MDN doc link.
- Registered in `VitalsServiceProvider`.

### Schema changes (all folded into source migrations — no ALTER files)
- `vitals_audits`: added `api_call_count INT default 0`, `api_call_cost DECIMAL(10,4) default 0`
- `vitals_backend_telemetry`: added `events_log JSON nullable`

### Translations
- All new user-visible strings added to `lang/en/vitals.php`, `lang/fr/vitals.php`, `lang/de/vitals.php`, `lang/es/vitals.php` under keys: `compare.*`, `trace.*`, `health.*`, `status.*`, `seo.*`, `self_check.*`, `overview.*`, `security_headers.*`.

### Tests
- 31 new tests added across 8 files. Total: 337 → 368 tests.

### Routes added
- `GET /vitals/health` (public)
- `GET /vitals/status` (public, conditional on `vitals.status.enabled`)
- `GET /vitals/audits/{a}/compare/{b}` (auth)
- `GET /vitals/audits/{audit}/seo` (auth)
- `GET /vitals/admin/self-check` (auth)

## [v1.0.0-alpha.52] - 2026-05-10

### Changed

- **Complete README rewrite** — every feature now explained in plain language for any reader (junior dev, senior dev, DevOps, product manager, agency client). The previous README was technical and dense; this version walks through each of the 16 features in 80–200 words before showing any configuration snippet.
- Added "Why Laravel Vitals" comparison table vs GTMetrix and Google PageSpeed Insights, covering 9 capabilities side by side.
- Added "How it works" lifecycle diagram showing the full audit flow from URL declaration to recommendation with file/line reference.
- Added a "Privacy and data" section documenting exactly what RUM collects and what it does not (no IP addresses, no cookies, no fingerprinting beyond UA string).
- Added a "Performance impact" section covering middleware overhead (sub-microsecond on non-audit requests), RUM bundle size (4.25 kB gzipped), and dashboard asset caching strategy.
- Added a "Top 5 troubleshooting" section covering the most common issues: URL not found, Chrome sandbox errors in Docker, dashboard access denied, missing RUM data, and `vitals:doctor` failures.
- All 16 feature sections now follow a consistent structure: what value it gives you → concrete example → how to configure.
- CHANGELOG alpha.51 entry was already complete and human-friendly — no changes needed.
- CONTRIBUTING.md tightened: setup steps verified, design conventions made actionable, test/lint commands explicit.
- Created `docs/screenshots/` directory with `.gitkeep` to provide a stable location for screenshot files.
- Updated `.gitignore` to allow `docs/screenshots/*.png` while continuing to exclude root-level PNGs.

## [v1.0.0-alpha.51] - 2026-05-10

### Added

#### Real User Monitoring (RUM)
- New `vitals_rum_events` table: stores CWV beacons from real visitors (metric, value, rating, device, navigation type, connection, attribution JSON, user-agent, occurred_at)
- New `RumEvent` model with `Prunable` trait (configurable retention, default 90 days)
- `POST /vitals/rum/ingest` endpoint — CSP-friendly, no CSRF (uses `sendBeacon`), rate-limited to 120 req/min; disabled while `vitals.rum.enabled = false`
- New `@vitalsRum` Blade directive — emits the `<script>` config block + `vitals-rum.js` deferred script tag
- `resources/js/rum.js` — wraps `web-vitals@4` attribution API (`onLCP`, `onINP`, `onCLS`, `onTTFB`, `onFCP`) with `sendBeacon` / `fetch` fallback; client-side sample rate applied before any network activity
- New Vite entry point `vitals-rum` → `dist/vitals-rum.js` (4.25 kB gzipped, well under 10 kB target)
- New `/vitals/rum` Livewire dashboard page: metric cards with p75 + good/needs-improvement/poor distribution bars, per-URL breakdown table (LCP/INP/CLS p75), INP attribution panel showing element selectors + event types
- Period (24h/7d/30d/90d) and device (all/mobile/desktop) filters on RUM page
- `vitals.rum` config block: `enabled`, `sample_rate`, `retention_days`
- Privacy: no IP addresses, no cookies, no fingerprinting beyond UA string — documented in config and README

#### Memory profiling
- Added `peak_memory_bytes BIGINT nullable` column to `vitals_backend_telemetry` (folded into source migration per alpha.15 convention)
- `TelemetryRecorder::snapshot()` now captures `memory_get_peak_usage(true)` as `peakMemoryBytes` in `BackendTelemetrySnapshot`
- `PersistTelemetryJob` writes `peak_memory_bytes` to the record
- Memory hogs panel on `/vitals/queries` showing top 5 routes by p75 peak memory (MB)

#### Database query baseline
- New `/vitals/queries` Livewire page: avg / p75 / p95 of `queries_count` and `queries_time_ms` per route name, sorted by p95 descending
- Regression detection: routes where current period p75 > 2× previous period p75 are flagged with a "↑ regression" badge
- Memory hogs sub-panel: top 5 routes by p75 `peak_memory_bytes`

#### INP attribution breakdown
- INP `attribution` JSON from web-vitals (interaction target selector, event type) is stored in `vitals_rum_events.attribution`
- RUM page shows an "INP attribution — slow interactions" table: element selector, event type, sample count, p75 INP

#### Navigation
- "RUM" and "Queries" nav items added to dashboard header (desktop navbar + mobile drawer)

#### Translations
- All new copy i18n'd in EN, FR, DE, ES (`vitals::vitals.rum.*`, `vitals::vitals.queries.*`)

#### Tests
- `tests/Feature/Http/RumControllerTest.php` — 8 tests: ingest validation, persistence, 5 metric types, `enabled=false` short-circuit, attribution storage, nullable fields
- `tests/Feature/Models/RumEventTest.php` — 6 tests: casts, prunable scope, all metric types
- `tests/Feature/Livewire/Pages/RumTest.php` — 6 tests: empty state, metric cards, period filter, device filter, invalid period guard, p75 calculation
- `tests/Feature/Telemetry/MemoryCaptureTest.php` — 3 tests: `peak_memory_bytes` stored, non-zero in snapshot, KB consistency
- `tests/Feature/Livewire/Pages/QueriesTest.php` — 6 tests: empty state, route display, period filter, regression detection, memory hogs, invalid period guard
- Total: 337 tests (+29 from v1.0.0-alpha.50)

## [v1.0.0-alpha.30] - 2026-05-07

### Added
- GitHub Actions CI workflow running Pest on PHP 8.2–8.4 × Laravel 11–13 matrix, PHPStan static analysis, and npm asset build
- CI status badge in README

## [v1.0.0-alpha.29] - 2026-05-07

### Changed
- Tables (urls-list, recommendations-index, url-detail audit history) wrapped in `overflow-x-auto` with bleed padding for full-width horizontal scroll on mobile
- Period controls (overview, url-detail) and metric toggle (url-detail) become horizontally scrollable below `md` breakpoint
- Lens cards padding reduced to `p-4` on mobile (`lg:p-5` on desktop) for breathing room at 375px
- URL hero card on url-detail stacks vertically on mobile (`flex-col sm:flex-row`)
- Mobile hamburger drawer added to header for `lg:hidden` screens — Alpine `x-data` toggle reveals a full-width nav drawer with all six routes

## [v1.0.0-alpha.28] - 2026-05-07

### Added
- Guided empty states on every data-dependent page: overview (no URLs / no audits), urls-list, recommendations-index, insights, budgets
- Each empty state includes: icon, title, body copy, primary CTA, and where applicable a copyable artisan command or config snippet
- Empty state copy i18n'd into EN, FR, DE, ES
- Tests for all new empty state states across Overview, UrlsList, RecommendationsIndex, Budgets, and a new InsightsTest

## [v1.0.0-alpha.27] - 2026-05-07

### Fixed
- Sparkline chart is now destroyed when the period yields fewer than 2 data points, preventing a vertical spike artifact on the 24h period

## [v1.0.0-alpha.26] - 2026-05-07

### Fixed
- Hourly bucket expression on the 24h period (was day-bucketing, collapsing sparkline to a single point)
- Database-portable `bucketExpression()` with correct `strftime`/`DATE_FORMAT`/`to_char` per driver (SQLite/MySQL/Postgres)

## [v1.0.0-alpha.25] - 2026-05-07

### Changed
- Replaced activity rings on Overview with Pulse-style lens cards: 4-up grid with metric score, delta badge, and ApexCharts sparkline per metric

### Removed
- `<x-vitals::activity-rings>` Blade component (unused after the lens card switch)

## [v1.0.0-alpha.24] - 2026-05-07

### Fixed
- Browse tile grid on Learn now renders at correct `365px` width (was `140px` due to `flux:tooltip` wrapper affecting grid measurement)
- Activity ring center text overlap with innermost SEO ring
- Tooltip count culled from ~30 to ~14 — kept only tooltips that explain non-obvious things
- All tooltip strings moved to `lang/{en,fr,de,es}/vitals.php` (no more hardcoded English in views)

## [v1.0.0-alpha.23] - 2026-05-07

### Fixed
- Activity rings handle `null` scores gracefully without throwing
- Smooth fill animation on ring progress arcs
- Added `flux:tooltip` coverage across all score cells, metric labels, and action buttons

## [v1.0.0-alpha.22] - 2026-05-07

### Added
- Card layout for the Learn view (category tiles, Browse tile grid)
- Browse tile grid on Learn page showing per-category issue count and active-recommendation count
- Favorites pinning on URLs list (star button, persisted to `pinned_at`)

## [v1.0.0-alpha.21] - 2026-05-07

### Fixed
- Expanded accent color palette to the full `50–950` scale so the logo gradient renders correctly (was invisible in dark mode)

## [v1.0.0-alpha.20] - 2026-05-07

### Fixed
- Restored alpha.18 view layouts after alpha.19 design-token refactor broke chart rendering
- Repaired ApexCharts initialization after the token migration

## [v1.0.0-alpha.19] - 2026-05-06

### Changed
- Editorial redesign: escaped "AI-slop" hero metric layout, introduced Geist Sans/Mono type pairing, `ink-*` tinted neutrals, asymmetric overview layout

## [v1.0.0-alpha.18] - 2026-05-06

### Added
- Health/Linear-style visual redesign: activity rings hero, period control button group, gradient area charts, refined `rounded-2xl` card style with `paper`/`canvas` surface tokens

## [v1.0.0-alpha.17] - 2026-05-06

### Added
- Laravel Vitals logo (rose ECG mark in SVG) and favicon served via asset controller route

## [v1.0.0-alpha.16] - 2026-05-06

### Fixed
- Repaired 16 broken `web.dev` documentation URLs in recommendation cards
- Version-aware Laravel documentation links (resolves against the running Laravel major version)

## [v1.0.0-alpha.15] - 2026-05-06

### Changed
- Consolidated `is_demo` and `details` columns into the source `create_*_table` migrations (removed separate add-column migrations)

## [v1.0.0-alpha.14] - 2026-05-06

### Added
- Six new Laravel-specific Core Web Vitals recommendations: image dimensions, `font-display`, resource preloading, HTTP/2, Laravel Octane, and asset hashing

## [v1.0.0-alpha.13] - 2026-05-06

### Added
- Action buttons throughout dashboard (view audit, view URL, browse learn)
- Metric tooltips with threshold guidance on all score columns
- `/vitals/learn` knowledge base page with full recommendation registry browsable by category

## [v1.0.0-alpha.12] - 2026-05-06

### Added
- Five new detail-driven Insights analyzers (N+1 correlation, third-party cost, slow-query impact, LCP element, cache miss rate)
- UrlDetail page enriched with frequent issues panel and failed-audits panel
- `/vitals/insights` global page with quick wins, worsening/improving URL lists, and third-party cost table
- FR, DE, ES translations for all new Insights strings

## [v1.0.0-alpha.11] - 2026-05-06

### Added
- GTMetrix-grade AuditDetail panels: page details, resource waterfall summary, third-party costs, main-thread blocking, slow requests, cache analysis, and diagnostics
- Inline score deltas (Δ vs prior audit) on AuditDetail

## [v1.0.0-alpha.10] - 2026-05-06

### Added
- Resource summary, third-party costs, main-thread blocking time, and LCP element extracted from Lighthouse JSON into `audits.details`

## [v1.0.0-alpha.9] - 2026-05-06

### Fixed
- Active nav state now visually highlighted
- Breadcrumbs added to URL detail and audit detail pages
- Audit history chart now renders oldest-to-newest (previously reversed)

## [v1.0.0-alpha.8] - 2026-05-06

### Fixed
- Replaced invalid Flux icon names with correct ones throughout the dashboard
- Wired Flux dark-mode toggle to `$flux.dark`
- Replaced placeholder `<div>` charts with real ApexCharts instances on Overview, AuditDetail, and UrlDetail

## [v1.0.0-alpha.7] - 2026-05-06

### Added
- Phase 3 dashboard: polished UrlsList, UrlDetail, Recommendations, Budgets pages with Flux components
- Phase 2b: AuditDetail redesign with correlation panel (TTFB vs N+1 heuristic)
- Phase 2a: redesigned Overview with active-alerts section and activity feed
- Phase 1: Apple Health–inspired rose theme, `Health` and `Correlation` helper classes, Flux layout

## [v1.0.0-alpha.6] - 2026-05-06

### Fixed
- Tailwind content scan now includes Flux vendor paths, fixing purged utility classes in production
- Removed bundled Alpine.js (Flux ships its own)

## [v1.0.0-alpha.5] - 2026-05-06

### Fixed
- Dashboard CSS and JS assets served via dedicated package routes (`/vitals/assets/{file}`) — no publish step needed

## [v1.0.0-alpha.4] - 2026-05-06

### Fixed
- Allow `symfony/process ^8` for Laravel 13 / Symfony 8 compatibility

## [v1.0.0-alpha.3] - 2026-05-06

### Added
- `vitals:demo` artisan command + `DemoSeeder` for fast sample data
- `vitals:doctor` diagnostic command
- `vitals:purge --demo` flag to clear demo data
- Publishable Blade mail templates for digest and regression notifications
- FR, DE, ES translations for all recommendations and dashboard strings
- `is_demo` flag on vitals tables for demo-data isolation
- Vite asset pipeline (Tailwind + Alpine + ApexCharts bundled)

### Changed
- Removed BrowsershotDriver (was non-functional on Browsershot v5)
- Switched from custom `VitalsSlackChannel` to `laravel/slack-notification-channel`
- `device=both` now dispatches separate mobile and desktop audit jobs

### Fixed
- `vitals.dashboard.enabled` config flag honoured on all routes
- `UrlSeeder::sync()` wrapped in a cache lock for concurrent batch safety
- Telemetry listeners registered once at boot (Octane accumulation fix)
- `view-cache-disabled` false positive suppressed when compiled views exist
- N+1 batch query in `CheckRegressionsCommand`

## [v1.0.0-alpha.2] - 2026-05-06

### Added
- PlaywrightDriver for headless Chromium audits
- Pulse and Telescope sources feeding into recommendations
- `vitals:digest:send` command + `WeeklyDigest` notification
- `vitals:check-regressions` command + `RegressionDetected` notification
- `BudgetViolated` and `AuditCompleted` notifications via `VitalsNotifier`
- `VitalsNotifier` dispatcher with config-driven channel gates

## [v1.0.0-alpha.1] - 2026-05-06

### Added
- Initial alpha release combining v0.1–v0.4 milestones into the first named alpha
- Full Livewire dashboard (Overview, UrlsList, UrlDetail, AuditDetail, RecommendationsIndex, Budgets)
- Lighthouse audit engine (Local, PageSpeed, Stub drivers)
- Backend telemetry capture (queries, memory, views, jobs, cache, N+1 heuristic)
- Recommendation system with 20+ audit keys and code-level file/line pointers
- Performance budgets with CI exit codes and JUnit XML output
- English recommendation translations

[Unreleased]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.52...HEAD
[v1.0.0-alpha.52]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.51...v1.0.0-alpha.52
[v1.0.0-alpha.51]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.30...v1.0.0-alpha.51
[v1.0.0-alpha.30]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.29...v1.0.0-alpha.30
[v1.0.0-alpha.29]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.28...v1.0.0-alpha.29
[v1.0.0-alpha.28]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.27...v1.0.0-alpha.28
[v1.0.0-alpha.27]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.26...v1.0.0-alpha.27
[v1.0.0-alpha.26]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.25...v1.0.0-alpha.26
[v1.0.0-alpha.25]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.24...v1.0.0-alpha.25
[v1.0.0-alpha.24]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.23...v1.0.0-alpha.24
[v1.0.0-alpha.23]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.22...v1.0.0-alpha.23
[v1.0.0-alpha.22]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.21...v1.0.0-alpha.22
[v1.0.0-alpha.21]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.20...v1.0.0-alpha.21
[v1.0.0-alpha.20]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.19...v1.0.0-alpha.20
[v1.0.0-alpha.19]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.18...v1.0.0-alpha.19
[v1.0.0-alpha.18]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.17...v1.0.0-alpha.18
[v1.0.0-alpha.17]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.16...v1.0.0-alpha.17
[v1.0.0-alpha.16]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.15...v1.0.0-alpha.16
[v1.0.0-alpha.15]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.14...v1.0.0-alpha.15
[v1.0.0-alpha.14]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.13...v1.0.0-alpha.14
[v1.0.0-alpha.13]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.12...v1.0.0-alpha.13
[v1.0.0-alpha.12]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.11...v1.0.0-alpha.12
[v1.0.0-alpha.11]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.10...v1.0.0-alpha.11
[v1.0.0-alpha.10]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.9...v1.0.0-alpha.10
[v1.0.0-alpha.9]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.8...v1.0.0-alpha.9
[v1.0.0-alpha.8]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.7...v1.0.0-alpha.8
[v1.0.0-alpha.7]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.6...v1.0.0-alpha.7
[v1.0.0-alpha.6]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.5...v1.0.0-alpha.6
[v1.0.0-alpha.5]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.4...v1.0.0-alpha.5
[v1.0.0-alpha.4]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.3...v1.0.0-alpha.4
[v1.0.0-alpha.3]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.2...v1.0.0-alpha.3
[v1.0.0-alpha.2]: https://github.com/corentinbtmps/laravel-vitals/compare/v1.0.0-alpha.1...v1.0.0-alpha.2
[v1.0.0-alpha.1]: https://github.com/corentinbtmps/laravel-vitals/releases/tag/v1.0.0-alpha.1
