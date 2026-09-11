# Coding standards

Part of the oo-wp-plugin working instructions — index: [CLAUDE.md](../CLAUDE.md).

**Match the surrounding file.** This codebase is old enough to have drifted; do not modernize a file
you are only touching in one place. An unrelated reformat mixed into a diff is a review finding.

## Formatting

- **Tabs** for indentation (`.editorconfig`: `indent_style = tab`), LF line endings
  (`.gitattributes`).
- `declare(strict_types=1);` in new files. Many old files lack it — adding it to an existing file is
  a separate, tested change, not a drive-by.
- License header at the top of new files: **AGPL** for `plugin/`, **GPL** for `config/` and
  `templates.dist/`. Copy it from a neighbouring file.
- Class declarations put `extends`/`implements` on the next line, indented one tab.
- Every PHP file reachable over HTTP starts with `if ( ! defined( 'ABSPATH' ) ) exit;`.

## Naming

| Thing | Convention | Example |
| --- | --- | --- |
| Class | `PascalCase`, one per file, filename = class name | `EstateListEnvironmentDefault` |
| Interface | usually no suffix; `DatabaseChangesInterface` is the exception | `EstateListEnvironment` |
| Private/protected property | `_` prefix, `_p` when it holds an object | `$_pWPDB`, `$_templateName` |
| Local variable holding an object | `p` prefix | `$pEstateList` |
| Constant | `SCREAMING_SNAKE_CASE`; DB tables as `TABLENAME_*` | `TABLENAME_LIST_VIEW` |
| Method | `camelCase`; WordPress hook callbacks may keep `snake_case` | `getFieldsCollectionBuilderShort()`, `register_menu()` |
| Test class | `TestClass<Subject>` | `TestClassEstateList` |

**Language:** code, identifiers, comments and documentation are **English**. Only translations are
not. A German comment or a German identifier in new code is a review finding — see
[code-review.md](code-review.md).

**Spelling matters more than usual** for anything that gets persisted or hooked: an option key, a DB
column, a hook name or a capability constant cannot be renamed later without a migration.

## Comments

- Explain the **why**, not the what. Short.
- Keep `@param` / `@return` / `@throws` on new public methods — the codebase leans on them where
  signatures are untyped.
- Skip the empty `/** * */` blocks that older files are full of; don't add more.
- No commented-out code and no debug output (`var_dump()`, `print_r()`, `error_log()` left behind).
  Git remembers.

## Prefer the framework, then the project, then your own code

1. **WordPress core** — `wp_remote_post`, `wp_json_encode`, `sanitize_text_field`, `wp_parse_args`,
   `dbDelta`, `wp_nonce_field`, `human_time_diff`, …
2. **An existing plugin helper** — `RequestVariablesSanitizer`, `Escape`/`ArrayContainerEscape`,
   `RecordManager*`, `WPOptionWrapperBase`, `WPNonceWrapper`, `HtmlIdGenerator`,
   `FileVersionHelper`, `Utility\__String`, `Language::LOCALE_MAPPING`, `Types\FieldTypes`,
   `Field\PriceFormatService`, `Field\CostsCalculator`.
3. **Only then something new** — and put it where the next caller will find it, not in the class
   that happened to need it first.

## Security and output escaping

- **Never interpolate into SQL.** `$wpdb->prepare()` for values; `esc_sql()` only where `prepare()`
  cannot be used (identifiers). Table names always via `RecordManager::TABLENAME_*` +
  `$wpdb->prefix`.
- **Never read `$_GET`/`$_POST`/`$_REQUEST` directly in new code.** Use
  `RequestVariablesSanitizer::getFilteredGet()` / `getFilteredPost()` — it strips slashes and rejects
  the characters that must not reach the onOffice API (`INVALID_CHARACTERS`).
- **Escape at the point of output, matching the context:** `esc_html` / `esc_attr` / `esc_url` /
  `esc_js` / `esc_textarea`. For translated strings use `esc_html__()`, `esc_attr__()`,
  `esc_html_e()` — never `echo __()`.
- **Admin write actions need both** a capability check (`current_user_can()`, see
  `Controller\UserCapabilities::OO_PLUGINCAP_*`) **and** a nonce check (`WPNonceWrapper`,
  `check_admin_referer`). One without the other is a finding.
- API credentials are stored encrypted (`Utility\SymmetricEncryption`). Never log, echo or forward
  them. `oo-updater.php` deliberately transmits the API key to the update server — do not extend
  that pattern to new endpoints without asking.

### `phpcs:ignore` — annotations without a checker

There are several hundred `phpcs:ignore` annotations in the tree but **no phpcs configuration and no
`squizlabs`/`wp-coding-standards` dependency** — nothing evaluates them. They document intent for
the wordpress.org review. Do not add new ones expecting a checker to honour them, and do not remove
existing ones as "dead". If a PR adds phpcs annotations, ask whether phpcs should be wired up
instead.

## Hardcoded values

Use the existing constant, or add one — don't inline:

`ONOFFICE_API_SERVER` · `RecordManager::TABLENAME_*` · `UserCapabilities::OO_PLUGINCAP_*` ·
`Types\FieldTypes` · `Types\ImageTypes` · `Language::LOCALE_MAPPING` · `Escape::*` ·
`Template::KEY_*` · `DatabaseChanges::MAX_VERSION`

A magic number or string that appears more than once wants a name. A user-facing string wants a
translation function.

## Translations

Wrap every user-facing string in `__()` / `_e()` / `_n()` / `_x()` with the **literal** text domain
`'onoffice-for-wp-websites'`, escaped for its output context. Never build a sentence by
concatenation; use placeholders and a `/* translators: */` comment.

**Only `languages/onoffice-for-wp-websites-de_DE.po` may be changed in a pull request** — every
other file under `languages/` is written by automation, and `guard-languages.yml` fails the PR.

Full pipeline, the two text domains, WPML, and the `Language::LOCALE_MAPPING` trap when adding a
locale: [TRANSLATIONS.md](TRANSLATIONS.md).

## Accessibility / BFSG

The plugin's output sits on public real-estate websites subject to the German
Barrierefreiheitsstärkungsgesetz (BFSG, implementing EN 301 549 / WCAG 2.1 AA). For any change to
`templates.dist/`, `plugin/Renderer/`, `plugin/Gui/` or the frontend JS:

- Every form control has a programmatically associated label (`<label for>`, or `aria-label` where
  no visible label exists). The existing `InputField*Renderer` classes do this — a new one must too.
- Validation state is conveyed in **text**, not by colour alone, and wired up with `aria-invalid` /
  `aria-required` / `aria-describedby` as the existing form renderers do.
- Icon-only and decorative markup: `aria-hidden="true"` on the decoration, an accessible name on the
  control.
- Interactive elements are real `<button>`/`<a>`, keyboard-operable, with a visible focus style — no
  `div` with a click handler, no `tabindex` juggling.
- Images carry meaningful, translated `alt` (estate photos: the estate title, not "image");
  decorative ones carry `alt=""`.
- Dynamic updates (filtering, pagination, favorites) announce themselves — `aria-live` /
  `aria-atomic` are already used (`js/onoffice-custom-select.js`), keep it consistent.
- Heading order is not skipped; lists are marked up as lists.
- Don't invent ARIA where semantic HTML does the job.

No full accessibility audit of this plugin has been done — flag concrete issues you can point at in
a diff, don't speculate.
