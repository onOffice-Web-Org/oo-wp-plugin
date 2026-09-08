# CLAUDE.md

Working instructions for Claude (and any other AI agent) in **oo-wp-plugin** ("onOffice for
WP-Websites") — the WordPress plugin that brings estates, addresses and contact forms from the
onOffice enterprise API into WordPress, and the data layer the four onOffice parent themes render
on top of.

**The detailed rules live in `.claude/docs/`. They are not loaded automatically — open the file that
covers your task before you start.**

**Three things make this repo different from the four theme repos:** it owns **customer database
schema** (a bad migration is unrecoverable on live installations), it has a **real 319-file PHPUnit
suite** (so tests are a review gate, not advisory), and its production dependencies are
**namespace-prefixed by Strauss** (so an ordinary-looking `use DI\Container;` is a bug). Don't
assume anything from the theme repos' docs transfers here without checking.

## Documentation map

| `.claude/docs/…` | Read it for |
| --- | --- |
| [project.md](.claude/docs/project.md) | what oo-wp-plugin is, "what belongs where", the six-repo family and when you need another repo |
| [stack-and-commands.md](.claude/docs/stack-and-commands.md) | composer/npm/make commands, what's generated where, local setup |
| [architecture.md](.claude/docs/architecture.md) | bootstrap, DI, the `*Environment` seam, layers, request flow, **Strauss prefixing**, templates as a public API |
| [coding-standards.md](.claude/docs/coding-standards.md) | PHP/WordPress conventions, naming, comments, security & escaping, hardcoded values, translations, BFSG |
| [database-and-migrations.md](.claude/docs/database-and-migrations.md) | **`DatabaseChanges` — how to add a migration and what must never be touched** |
| [testing.md](.claude/docs/testing.md) | PHPUnit against the WP test suite, mocks, what must have a test |
| [workflow.md](.claude/docs/workflow.md) | branches, releases, the workflow table — **and what each of them damages if it is triggered unasked** |
| [code-review.md](.claude/docs/code-review.md) | **the pull request review rules** |

Long-form documents, for humans and agents alike:

| Document | Content |
| --- | --- |
| [Readme.md](Readme.md) | project overview, getting started, API access, licensing |
| [documentation/Building.md](documentation/Building.md) | development setup, the prefixed-dependency mechanism in full, building a release ZIP |
| [documentation/RELEASE.md](documentation/RELEASE.md) | the definitive release process: branch flow, beta bugfixes, checklists, troubleshooting |
| [documentation/TRANSLATIONS.md](documentation/TRANSLATIONS.md) | the translation pipeline (POEditor, `guard-languages.yml`, the two text domains, WPML) |

**Rule of thumb:** `.claude/docs/` wins on *how to work*; `documentation/` and `Readme.md` win on
*details of a process*. If they contradict each other, the long-form doc is authoritative and the
`.claude/docs/` file needs fixing.

## How to work in this repository

- **Answer in the language the developer writes in.** Code, identifiers, comments and documentation
  are English; PR titles and reviews are German.
- **Match the surrounding file.** This codebase is old enough to have drifted — don't modernize a
  file you are only touching in one place, and don't mix a reformat into a change.
- **Never touch an existing migration.** Renumbering or editing a `DatabaseChanges` case breaks live
  customer installations irreversibly — see
  [database-and-migrations.md](.claude/docs/database-and-migrations.md).
- **Import the prefixed vendor namespace** (`onOffice\WPlugin\Vendor\…`). An unprefixed import does
  not necessarily throw — it silently binds to another plugin's copy. See
  [architecture.md](.claude/docs/architecture.md#prefixed-dependencies-strauss).
- **Read before writing.** Most real bugs here come from what a diff doesn't show: the DI binding in
  `config/di-config.php`, the hook registration in `plugin.php`, the `*Environment` interface that
  also needs the new method, the customer template that still uses the variable.
- **Never guess about cross-repo impact** — say what couldn't be checked. See
  [project.md](.claude/docs/project.md#when-you-need-another-repository).
- **Nothing may be pushed, released or deployed without being asked** — see
  [workflow.md](.claude/docs/workflow.md#nothing-here-may-be-triggered-without-being-asked).
- **Finish with QA:** `make test-docker` — the same suite `unit-tests.yml` runs.

## Do's and Don'ts

- Put `$wpdb` access in a `RecordManager*`, API access behind `SDKWrapper`/an `APIClientAction`, and
  WordPress globals behind a `WP/` wrapper. Those seams are what makes this code testable.
- Register hooks in `plugin.php`, keep the callback bodies in controllers. Add a DI binding in
  `config/di-config.php` when you introduce an interface.
- Never read `$_GET`/`$_POST` directly (`RequestVariablesSanitizer`), never interpolate into SQL
  (`$wpdb->prepare()`), never output unescaped, never write without both a capability *and* a nonce
  check.
- Never edit generated files: `dist/`, `vendor/`, `vendor-prefixed/`, and everything under
  `languages/` except `onoffice-for-wp-websites-de_DE.po`. Never hand-edit a version number or the
  `readme.txt` changelog — semantic-release owns them.
- Don't rename or drop a public class, method or template variable (`$pEstates`, `$pForm`,
  `$pAddressList`, …) without calling it out as breaking: the four themes and every customer's own
  template copy depend on them.
- Don't edit `SDK/` to fix a plugin problem — it mirrors `onOfficeGmbH/sdk`, with one deliberate
  divergence.
- Don't hardcode user-facing text, API URLs, table names, option names or capabilities — use the
  existing constants or add one.
- Keep important code readable even if it costs lines, and write comments that explain the **why**,
  in English.

## Pull request reviews

The rules are in **[.claude/docs/code-review.md](.claude/docs/code-review.md)** — read that file
before reviewing. In one paragraph: approve what improves the codebase, report only findings worth a
developer's time (**3–8, hard cap 10**), severity prefixes **Blocker / Issue / Nit / FYI**, tests
read first, no PR summary and no praise section, post exactly one comment in German. Highest signal
in this repo: migration safety, cross-repo breakage, security, missing regression tests.

The same rules drive the automated review in `.github/workflows/claude-code-review.yml`. It runs
**on request, not on every push**: on *Ready for review*, on an `@claude` comment, or on a manual
workflow start. The `no-claude-review` label suppresses the automatic run.
