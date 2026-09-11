# Testing

Part of the oo-wp-plugin working instructions — index: [CLAUDE.md](../CLAUDE.md).

**Unlike the four theme repos, this one has a real test suite** — several hundred `TestClass*`
files under `tests/`. Tests are a review gate here, not advisory.

PHPUnit runs against the **real WordPress test suite** (`WP_UnitTestCase`, MySQL required), so
these are integration tests, not isolated unit tests.

## Commands

```bash
make test-docker                        # everything, in Docker — easiest, no local setup
make test-docker tests/TestClassX.php   # one file
./vendor/bin/phpunit                    # local; needs scripts/install-wp-tests.sh first
./vendor/bin/phpunit --filter testFoo
./vendor/bin/phpstan analyse            # level 1 — local only, NOT run in CI
```

`make test-docker` runs `scripts/run-tests-docker.sh`, which waits for MySQL, **drops and recreates
the test database**, installs the WP test suite into a cached volume on first run, and then runs
PHPUnit.

## What CI enforces

`.github/workflows/unit-tests.yml` (on PRs to `master`/`beta`/`prerelease`/`release` and weekly;
the PHP and MySQL versions it pins are in that file, and they are not the ones
`docker-compose.test.yml` uses) runs:

```
composer install --no-scripts && composer update --lock
composer prefix-dependencies && composer check-prefixed-imports
phpunit --fail-on-warning --fail-on-risky --disallow-test-output --coverage-clover …
```

So a change must survive: **no `echo`/`var_dump` in tests, no test without assertions, no
deprecation warning, no unprefixed vendor import.** Don't restate a failure CI already reports red.

**PHPStan is configured** (`phpstan.neon`, level 1, with `phpstan-baseline.neon`) **but no workflow
runs it.** Run it locally before pushing. Wiring it into CI would be welcome, but as its own PR.

## Conventions

- File and class named `TestClass<Subject>.php`, flat in `tests/`, namespace `onOffice\tests`.
- Add `@covers onOffice\WPlugin\…` to the class docblock.
- `tests/bootstrap.php` loads `plugin.php` and swaps `DatabaseChangesInterface` for
  `Mocks\DatabaseChangesDummy`, so tests do not run migrations. If your feature needs real tables,
  create them in the test.
- **New reusable doubles go in `tests/Mocks/`** — not into `plugin/`, even though the existing
  `*Test` doubles live there (see
  [architecture.md](architecture.md#the-environment--configuration-seam)).
- Existing helpers, use them rather than rolling your own: `SDKWrapperMocker` (fake API responses),
  `EstateListMocker`, `TemplateMocker`, `RedirectWrapperMocker`,
  `DefaultFilterBuilderListViewAddressMocker`, `TestDateTimeImmutableFactory`,
  `ViewFieldModifierTypesTestBase`.
- Compare rendered HTML with `HtmlNormalizerTrait`, not raw string equality.
- Fixtures live in `tests/resources/`.
- `plugin/Gui/` is excluded from coverage in `phpunit.xml.dist` — admin pages are not expected to be
  unit tested, but the `Model/` and `Renderer/` classes behind them are.

## What must have a test

| Change | Test expectation |
| --- | --- |
| Bugfix | **A regression test that fails without the fix.** Missing one is a review finding. |
| New field / filter / view-field-modifier logic | Yes — this is where the fiddly bugs live. |
| `DatabaseChanges` migration | Yes, always: `TestClassDatabaseChanges` asserts the full schema. |
| API request/response shaping | Yes, via `SDKWrapperMocker`. |
| Admin markup in `Gui/` | No — excluded from coverage. |
| Pure markup or wording change in `templates.dist/` | No. |

Conversely: do **not** demand tests for pure markup or wording changes, and do not accept a test
that only asserts what the mock was told to return.
