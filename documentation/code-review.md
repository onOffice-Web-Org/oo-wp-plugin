# Pull request reviews

Part of the oo-wp-plugin working instructions — index: [CLAUDE.md](../CLAUDE.md).

These rules apply both when a developer asks for a review locally and to the automated review in
`.github/workflows/claude-code-review.yml`, which points at this file. They mirror the four theme
repos' rules — the `code-review.md` in
[onoffice-pure](https://github.com/onOffice-Web-Org/onoffice-pure), which in turn mirrors the one in
[onoffice-shared](https://github.com/onOffice-Web-Org/onoffice-shared), where the full reasoning on
where these rules come from lives — with **this repo's own checklist**, which differs
substantially: this is the data layer, it has a real test suite, and it owns customer database
schema.

The automated review is **on request, not on every push**. It starts when

- the *Ready for review* button is pressed on a draft pull request,
- someone comments `@claude review` on the pull request (an `@claude` comment may also carry a
  specific request, e.g. `@claude review nur die Migration` — then answer exactly that, in the same
  format, without the full review around it),
- or the workflow is started manually with a PR number.

A pull request opened directly (not as a draft) never fires *Ready for review* — open it as a draft
and mark it ready, or ask by comment. The label `no-claude-review` suppresses the automatic run; an
explicit comment or a manual start is always honoured.

Because this repository is public, two restrictions apply — the workflow file explains them in full:

- **Pull requests from a fork are not reviewed.** The job would otherwise run the fork's code next
  to this repository's secrets. Push the branch into this repository instead.
- **`@claude` only starts a run for OWNER, MEMBER or COLLABORATOR.** A comment from anyone else is
  ignored.

## The one rule

**Approve a change when it clearly improves the codebase, even if it is not perfect.** A review is
not a wish list. Block only on a real defect, a migration that endangers existing installations
(see [database-and-migrations.md](database-and-migrations.md)), a cross-repo breakage (see
[project.md](project.md)), a security issue, or a missing test for behaviour that must not regress.

## Keep it short

- **Report only findings worth a developer's time.** Aim for **3–8 findings**, hard cap **10**. If
  there is nothing, say so in one line — an empty review is a valid, useful result.
- **One finding, one entry.** Repeated pattern → say "same pattern in `X.php:12`, `Y.php:40`".
- **No summary of what the PR does.** The author knows. Two sentences of context at most, and only
  if the diff is hard to read.
- **No praise sections, no checklist of things that are fine, no "consider in the future"
  brainstorming.**
- Every finding needs: `file:line`, what is wrong, why it matters, and a concrete fix (a short code
  snippet when it helps).
- **Don't restate a red CI check.** `unit-tests.yml` already reports failing tests, PHPUnit
  warnings/risky tests, and unprefixed vendor imports; `guard-languages.yml` already reports
  forbidden `languages/` changes; **Lint PR** already reports a bad PR title.
- Review **only the changed lines and what they touch** — not the rest of the repository. A
  pre-existing problem in a file the PR merely touches is out of scope unless the change makes it
  materially worse.

## Severity labels

| Prefix | Meaning |
| --- | --- |
| **Blocker:** | must be fixed before merge — renumbered/edited existing `DatabaseChanges` case, a new table missing from `deinstall()`, unprepared SQL, missing capability *or* nonce check on a write action, credentials leaving the plugin, a removed/renamed public class, method or template variable a theme or customer template still calls, an edited generated `languages/` file, a typo in a persisted name (option key, DB column, hook, capability) |
| **Issue:** | should be fixed in this PR — bug in an edge case, missing regression test, raw superglobal read, missing/wrong output escaping, hardcoded or untranslated user-facing string, duplicated logic, misleading name, unprefixed vendor import |
| **Nit:** | optional polish — wording, comment, small simplification |
| **FYI:** | context the author should know, no action required (e.g. "this also lands in the four theme repos' rendering") |

Never invent a severity. If you are not sure a finding is real, verify it in the code, or label it
**FYI** and say what you could not check.

## How to run a review

1. Read the PR title/description and the `P#<number>` ticket reference (or the triggering `@claude`
   comment, if that's how this run started). Judge the diff against the stated intent; flag anything
   in the diff not covered by it.
2. Get the diff (`gh pr diff`, or `git diff origin/master...HEAD`).
3. **Read the tests first.** They state what the author believes the change does. A behavioural
   change with no test change is the first thing to ask about — unlike the theme repos, tests are a
   real gate here ([testing.md](testing.md)).
4. Open the surrounding code of each changed file. Most real findings come from what the diff *does
   not* show: the DI binding in `config/di-config.php`, the hook registration in `plugin.php`, the
   existing `RecordManager` that already does this, the `*Environment` interface that also needs the
   new method.
5. Work the project-specific checklist below — that is where the expensive mistakes are.
6. Then the general axes.
7. Verify what is cheap to verify: is a removed symbol really unused (grep `plugin/`, `tests/`,
   `templates.dist/`, `config/di-config.php`, `plugin.php`, `js/`)? Say what couldn't be checked in
   the sibling repos.
8. Decide: **Approve** / **Approve with comments** / **Request changes** (only when there is a
   Blocker).

## Project-specific checklist

Highest signal first.

- [ ] **Database migration safety** — the single highest-risk area in this repo. Was an existing
      `case $dbversion <= N:` renumbered, reordered or changed in behaviour? (**Blocker** — sites in
      the field have already passed it.) Was `MAX_VERSION` bumped without a migration, or a
      migration added without bumping it? Is `TestClassDatabaseChanges` updated? Is a new table also
      in `deinstall()`? Does a migration assume a column that older installs don't have? See
      [database-and-migrations.md](database-and-migrations.md).
- [ ] **Cross-repo impact** — does the change rename/remove a public `onOffice\WPlugin\*` class,
      method or constant, a variable exposed to templates (`$pEstates`, `$pForm`, `$pAddressList`,
      `$generateSortDropDown`, `$getListName`, `$scriptLoader`), a CSS class, or a script handle? All
      four theme repos call this plugin's namespace directly from
      `onoffice-theme/templates/fields.php`, and customers copy `templates.dist/` out. In the automated
      review the sibling repositories are checked out under `cross-repo/<repo>/` — grep there. If
      they are missing, or locally, say "Nicht geprüft". See [project.md](project.md).
- [ ] **Security** — unprepared SQL or string-interpolated table names; a raw
      `$_GET`/`$_POST`/`$_REQUEST` read instead of `RequestVariablesSanitizer`; missing or
      context-wrong output escaping; a write action missing either `current_user_can()` or the nonce
      check; API credentials logged or forwarded. See
      [coding-standards.md](coding-standards.md#security-and-output-escaping).
- [ ] **Tests** — a real gate here. Bugfix without a regression test that fails before the fix →
      **Issue**. New field/filter/migration logic without a test → **Issue**. But: is the test
      actually testing the change, or only asserting what the mock was told to return? And don't
      demand tests for `Gui/` markup (excluded from coverage) or wording changes.
      See [testing.md](testing.md).
- [ ] **Prefixed imports** — any `use` of a prefixed package under its original name
      (`DI\`, `Parsedown`, phpgeo, ALTCHA, PSR-11) instead of `onOffice\WPlugin\Vendor\…`. A new
      production dependency must be classified in `extra.strauss`. CI catches this, so keep it to one
      line if it's already red. See [architecture.md](architecture.md#prefixed-dependencies-strauss).
- [ ] **Removed code** — is it truly unused? Check callers, the DI bindings in
      `config/di-config.php`, hook registrations in `plugin.php`, usage in `templates.dist/`,
      `@covers` in tests, and JS handles. Deleting something a customer template or a sibling theme
      still calls is a **Blocker**. Ask before agreeing to a deletion that looks unused but wasn't
      verified.
- [ ] **Reusability / redundancy** — is an existing helper being reimplemented? Check
      `RequestVariablesSanitizer`, `Escape`/`ArrayContainerEscape`, `RecordManager*`, the `WP/`
      wrappers, `Utility\__String`, `HtmlIdGenerator`, `FileVersionHelper`, `PriceFormatService`,
      `CostsCalculator`, `Types\FieldTypes`. Is the same logic now in two places? Name where the
      shared home should be, and whether it belongs one layer down (`RecordManager` / `*Environment`
      / `Renderer`).
- [ ] **Framework first** — WordPress core (`wp_remote_post`, `wp_parse_args`, `dbDelta`,
      `sanitize_*`) before a hand-rolled equivalent; an existing plugin helper before a new one.
- [ ] **Hardcoded values / translations** — user-facing string wrapped in `__()`/`_e()`/`_n()`/`_x()`
      with the **literal** `'onoffice-for-wp-websites'` domain and correct escaping; German source
      translation added to `-de_DE.po`; no concatenated sentences. A value that already has a
      constant (`ONOFFICE_API_SERVER`, `TABLENAME_*`, `OO_PLUGINCAP_*`, `FieldTypes`,
      `Language::LOCALE_MAPPING`) must not be inlined. A new locale also needs an entry in
      `Language::LOCALE_MAPPING`, not just the sync allowlist. See
      [TRANSLATIONS.md](TRANSLATIONS.md).
- [ ] **Naming and spelling** — new class/method/variable names against the conventions in
      [coding-standards.md](coding-standards.md#naming) (`_p`/`p` prefixes, `TestClass*`,
      `*Environment`/`*Default`). A typo in a **persisted or hooked** name — option key, DB column,
      hook name, capability constant, shortcode attribute — is a **Blocker**: it cannot be fixed
      later without a migration.
- [ ] **Consistency** — a German comment or German identifier in new code (**always flag it**);
      comments that restate the code instead of explaining why; comment/structure style not matching
      the surrounding file; spaces where the file uses tabs; an unrelated reformat mixed into the
      diff; leftover commented-out code or debug output.
- [ ] **Performance** — judge against a real portfolio (hundreds of estates), not a demo dataset. An
      API call or DB query inside a loop over estates/addresses/fields (N+1); a new frontend API call
      that bypasses `DBCache`; an unbounded `SELECT` on an `oo_plugin_*` table or missing pagination;
      filesystem calls per item instead of per request; a rebuilt DI container per call instead of
      the injected one.
- [ ] **BFSG / accessibility** — for changes to `templates.dist/`, `plugin/Renderer/`, `plugin/Gui/`
      or the frontend JS, the concrete checks in
      [coding-standards.md](coding-standards.md#accessibility--bfsg). Flag only what you can point at
      in the diff.

## General axes

- **Correctness** — does it do what the title/comment says, including edge cases: empty API
  response, missing field, `null` where an array is expected, unset request variable, a non-`de_DE`
  locale, WPML active, multisite.
- **Readability** — prefer a few extra, clear lines over a compact but harder-to-follow one. In
  important code, an explicit early return or a named local variable beats a clever one-liner. Don't
  push back on length alone.
- **Architecture** — is `$wpdb`, API or WordPress-global access leaking out of its layer
  (`RecordManager*` / `SDKWrapper` / `WP/`)? Is a new class building its own DI container instead of
  injecting? Is admin HTML being emitted from a controller instead of a `Renderer`? Does the change
  reduce complexity or just move it?

## Output format

Post one comment, in **German**, code/paths/identifiers verbatim:

```markdown
**Review** — <eine Zeile: was geprüft wurde und Fazit>

**Blocker: <Titel>**
`plugin/Installer/DatabaseChanges.php:142` — <was falsch ist und warum>
```php
// Vorschlag
```

**Issue: <Titel>**
`plugin/Record/RecordManagerReadForm.php:88` — <…>

**Nit: <Titel>**
`plugin/Gui/AdminPageEstate.php:30` — <…>

---
Nicht geprüft: <was nicht verifizierbar war, z.B. kein Zugriff auf die Theme-Repos>
```

- Findings ordered by severity, then by file.
- Skip empty severity groups. Skip the `Nicht geprüft` line when there is nothing to say.
- No finding? → `**Review** — keine Auffälligkeiten. <ein Satz, was geprüft wurde.>`

## Where these rules come from

The public skill
[addyosmani/agent-skills · code-review-and-quality](https://github.com/addyosmani/agent-skills/blob/main/skills/code-review-and-quality/SKILL.md)
was evaluated, consistently with the four theme repos.

**Adopted:** the severity/triage mechanic and the "lead with what matters" brevity rule; the Dead
Code Hygiene process (extended here across the customer-template and six-repo boundary); the
Architecture axis's duplication / layer-leakage language (a good fit for the
`RecordManager`/`SDKWrapper`/`WP` split); the Readability axis's core framing question; **"Review the
Tests First" as a real gate** — adopted in full here, unlike in the theme repos, because this repo
has a real PHPUnit suite; and its Security axis, also adopted in full here, because this repo
writes SQL, reads request variables and holds API credentials.

**Not adopted:** Change Sizing (line-count thresholds, PR-splitting strategies) — migrations,
`FormModelBuilder` changes and field-configuration work are legitimately large here; Change
Descriptions/commit-message rules (semantic-release and **Lint PR** already own that); the
Multi-Model Review pipeline; Review Speed SLAs; Dependency Discipline as written — Dependabot
single-package PRs are the intended workflow here and a `composer.lock`-only change needs CI, not a
code review; and its Performance bullets as written, rewritten above into this project's idioms
(API N+1 over an estate list, `DBCache`, unbounded `oo_plugin_*` selects).

Everything project-specific in the checklist above — migration safety, cross-repo impact, prefixed
imports, `onoffice-for-wp-websites` translations, BFSG — is this project's own addition.
