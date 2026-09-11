# Stack and commands

Part of the oo-wp-plugin working instructions — index: [CLAUDE.md](../CLAUDE.md).

## Stack

| | |
| --- | --- |
| PHP | `>= 8.2`, `strict_types` in newer files |
| DI | `php-di/php-di` — prefixed to `onOffice\WPlugin\Vendor\DI` |
| Production deps | `erusev/parsedown`, `mjaschen/phpgeo`, `altcha-org/altcha` (PHP, prefixed) · `select2/select2`, `orchidjs/tom-select` (assets, **not** prefixed) |
| Dev deps | `phpunit/phpunit`, `yoast/phpunit-polyfills`, `phpstan/phpstan`, `brianhenryie/strauss`, `wp-cli/i18n-command`, `php-coveralls` |
| JS build | terser via `webpack.config.js` (no webpack bundling — it just minifies `js/*.js` → `dist/*.min.js`) |
| Release | `semantic-release` (`.releaserc`), Conventional Commits |

## Commands

```bash
# Setup
composer check-platform-reqs     # verify PHP extensions before anything else
composer install                 # deps + generates vendor-prefixed/ via post-install-cmd
npm install

# Dependencies / prefixing
composer prefix-dependencies     # regenerate vendor-prefixed/ (needs a fresh vendor/ — see below)
composer check-prefixed-imports  # fail on unprefixed imports of prefixed packages (CI runs this)

# Build
npm run build                    # js/*.js -> dist/*.min.js
npm run i18n                     # regenerate .pot/.po/.mo — read TRANSLATIONS.md first
make build                       # composer + npm + onoffice-for-wp-websites.zip
PREFIX=/tmp/release/onoffice-for-wp-websites make release   # shippable tree; PREFIX must be absolute

# QA
make test-docker                 # unit tests in Docker — the finishing check
./vendor/bin/phpstan analyse     # level 1, local only
```

**`PREFIX` must be an absolute path** — a relative one can send `make release` into an infinite
loop.

**Prefixing cannot run twice in a row:** it consumes the packages in `vendor/` and deletes them
afterwards. Run `composer install` first; a second run without it exits with a notice instead of
replacing `vendor-prefixed/` with an empty autoloader.

## What is built where, and what is generated

| Path | Status |
| --- | --- |
| `dist/*.min.js` | **generated** by `npm run build`, gitignored (only `dist/.gitkeep` is tracked). **Edit `js/`, never `dist/`.** |
| `vendor/`, `vendor-prefixed/` | **generated** by composer/Strauss, gitignored |
| `node_modules/` | generated |
| `languages/*` except `-de_DE.po` | **generated** by the POEditor sync — see [TRANSLATIONS.md](TRANSLATIONS.md) |
| `readme.txt` changelog, version fields in `plugin.php` / `readme.txt` / `package.json` | **generated** by semantic-release — never edit by hand |
| `css/` | not built, enqueued as-is |
| `onoffice-for-wp-websites.zip` | build artifact, gitignored |

Adding a new script means: put the source in `js/`, run `npm run build`, and register an
`IncludeFileModel` in `ScriptLoader/ScriptLoaderGenericConfigurationDefault.php` pointing at
`dist/<name>.min.js`. Cache busting comes from `FileVersionHelper::getFileVersion()` — don't
hand-roll a `?ver=`.

## Local WordPress test suite

`make test-docker` needs nothing but Docker. For a local run:

```bash
bash scripts/install-wp-tests.sh wordpress_test <user> <pass> <host> latest
./vendor/bin/phpunit
```

Details and the full test conventions: [testing.md](testing.md).
