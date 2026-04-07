# Refactoring Assessment

## What was reviewed

- PHP plugin code (`plugin/`, plus bootstrap files)
- JavaScript in `js/`
- Existing tooling/config (`composer.json`, `phpstan.neon`, `.editorconfig`, `package.json`)

Audit date: 2026-04-07

## Executive Findings

The plugin is functional and test-backed, but style and architecture drift is significant due to long-term multi-author development. The biggest opportunities are:

1. Standardize coding rules through automation.
2. Split large classes/methods into focused services.
3. Consolidate request-input handling.
4. Modernize frontend scripts incrementally.

## Evidence Snapshot

### PHP consistency signals

- Scanned PHP files in plugin scope: 446
- Files with `strict_types`: 230
- Files without `strict_types`: 216
- Files with underscore-prefixed private/protected properties: 283
- Files mixing tabs and spaces: 78
- Methods without return type declarations: 1552 of 3037
- `==` (non-strict) comparisons in `plugin/`: 118 occurrences
- `array()` usages in `plugin/`: 281 occurrences

### WordPress/global state usage

- Files using `$_GET`: 25
- Files using `$_POST`: 10
- Files using `$_REQUEST`: 1
- Files using `$_SERVER`: 2
- Files using `global $...`: 13

### Lint suppression signal

- PHPCS suppression directives in `plugin/`: 205

### JavaScript consistency signals

- JS files scanned: 30
- Files using `var`: 17
- Files using `let`: 15
- Files using `const`: 21
- Files using jQuery ready wrappers: 18

## Discrepancies and Refactoring Candidates

### 1) Mixed style conventions inside the same module

Examples:

- `plugin/AddressDetail.php:72` uses non-strict checks and inline exit flow.
- `plugin/FilterCall.php:134` uses one-line method bodies and legacy property naming.
- `plugin/RequestVariablesSanitizer.php:76` has mixed spacing/brace conventions in the same file.

Impact:

- Inconsistent readability and harder code review.

Refactor direction:

- Enforce one formatting standard and apply only in touched files first.

### 2) Oversized classes and methods (high maintenance cost)

Hotspot files:

- `plugin/EstateList.php` (~1648 LOC)
- `plugin/Model/FormModelBuilder/FormModelBuilderDBForm.php` (~1407 LOC)
- `plugin/Installer/DatabaseChanges.php` (~1325 LOC)
- `plugin/Gui/AdminPageFormSettingsBase.php` (~1064 LOC)

Hotspot methods:

- `plugin/Renderer/InputModelRenderer.php:121` `createInputField()` (~233 LOC switch)
- `plugin/Record/RecordManagerDuplicateListViewForm.php:73` `duplicateByName()`
- `plugin/Gui/AdminPageFormSettingsContact.php:95` `buildForms()`
- `plugin/Controller/DetailViewPostSaveController.php:96` `onSavePost()`

Impact:

- Harder to test, risky to modify, easy to introduce regressions.

Refactor direction:

- Extract cohesive services (builders/strategies), keep public API stable.

### 3) Input handling is split between wrappers and direct superglobals

Examples:

- Wrapper exists: `plugin/RequestVariablesSanitizer.php`
- Direct superglobal access still appears in business logic:
  - `plugin/Controller/SearchParametersModelBuilderEstate.php:116`
  - `plugin/EstateList.php:586`
  - `plugin/FormPost.php:129`

Impact:

- Harder to guarantee consistent sanitization and auditing.

Refactor direction:

- Route request reads through one boundary service and keep sanitization policy centralized.

### 4) Bootstrap orchestration is too dense

Example:

- `plugin.php` is a large bootstrap/orchestration file with many closures and hooks.

Impact:

- Startup behavior is hard to reason about and test in isolation.

Refactor direction:

- Split into registrars (hooks, routes/rewrite, admin, i18n, metadata) invoked from a lean bootstrap.

### 5) Frontend/admin JS mixes paradigms and contains brittle selectors

Examples:

- Mixed `var`/`let`/`const`: `js/onoffice-sort-by-user-selection.js:1`
- Deep DOM traversal chains: `js/admin.js:49`
- Large monolithic ready handlers: `js/admin.js:1`
- Potentially confusing duplicate insert behavior: `js/onoffice-honeypot.js:35`

Impact:

- Fragile behavior when markup changes; higher bug risk.

Refactor direction:

- Break scripts into smaller modules by feature and normalize declaration/event patterns.

### 6) Tooling gap for style enforcement

Observed:

- No root PHPCS ruleset file.
- No ESLint/Prettier configuration.
- PHPStan is present but low level and baseline-heavy (`phpstan.neon`).

Impact:

- Style regressions continue because conventions are not automatically enforced.

Refactor direction:

- Add lint configs and run them in CI as warning-only first, then enforce.

## Prioritized Refactoring Plan

### Priority 1 (quick wins, low risk)

1. Add project lint configs (PHPCS + ESLint) in non-blocking mode.
2. Introduce a short PR checklist aligned with `documentation/CodingStyleGuide.md`.
3. Normalize strict comparisons (`===`) in touched files.

### Priority 2 (medium risk, high impact)

1. Extract request input reader service and migrate high-traffic paths.
2. Decompose `InputModelRenderer::createInputField()` into strategy/registry handlers.
3. Split `plugin.php` into bootstrap registrars.

### Priority 3 (larger structural work)

1. Break up `EstateList` and large FormModelBuilder classes into composable services.
2. Reduce global state and direct WP calls behind wrappers where testability benefits.
3. Modernize JS modules (`admin.js`, form scripts) with small incremental PRs.

## Recommended Working Model

- Refactor in vertical slices (one hotspot at a time, with tests).
- Avoid broad formatting-only rewrites across the entire repository.
- Require behavior-preserving tests for each extracted component.
- Keep compatibility with existing WordPress integrations while modernizing internals.
