# Translations

How translation works in this plugin, what you may change in a pull request, and what is owned by
automation.

Long-form document — indexed from [../CLAUDE.md](../CLAUDE.md), and authoritative over
[coding-standards.md](coding-standards.md#translations) wherever the
two differ.

## The one rule

**POEditor is the source of truth for every language except German.**

The only translation file a pull request may change is:

```
languages/onoffice-for-wp-websites-de_DE.po
```

Every other file under `languages/` — all `.mo` files, `*.pot`, and every non-`de_DE` locale — is
written by automation. The **guard-languages** workflow
(`.github/workflows/guard-languages.yml`) fails any PR against `master` or `beta` that touches them.
It is skipped only for branches whose name starts with `release`.

If you think a non-German translation is wrong, fix it in POEditor, not in the repository.

## The two text domains

| Domain | Used by | Files |
| --- | --- | --- |
| `onoffice-for-wp-websites` | **all plugin and `templates.dist/` code** | `languages/onoffice-for-wp-websites-*.po/.mo`, `onoffice-for-wp-websites.pot` |
| `onoffice` | strings in a *customer's own* templates, under `onoffice-personalized/languages` or `<theme>/onoffice-theme/languages` | legacy `languages/onoffice-*.po/.mo`, `onoffice.pot` |

Always use `'onoffice-for-wp-websites'` in this repository. The `onoffice` domain exists so that
customers can translate the strings they add to their own template copies; `plugin.php` loads it
from their folder on `plugins_loaded`. The legacy `languages/onoffice-*` files are kept for that
domain — do not "clean them up".

Note that the committed `.pot` still carries `X-Domain: onoffice` in its header, because POEditor
generates it. That is a cosmetic inconsistency, not something to fix in a PR.

## Adding a translatable string

```php
// plain output
echo esc_html__('Estate list', 'onoffice-for-wp-websites');

// with a placeholder — never build sentences by concatenation
printf(
    /* translators: %s is the name of the estate list */
    esc_html__('The list %s was saved.', 'onoffice-for-wp-websites'),
    esc_html($listName)
);

// plural
echo esc_html(sprintf(
    _n('%d estate', '%d estates', $count, 'onoffice-for-wp-websites'),
    $count
));

// disambiguation
echo esc_html_x('Buy', 'marketing type', 'onoffice-for-wp-websites');
```

Rules:

* **Escape for the output context, not by habit**: `esc_html__()` in HTML text, `esc_attr__()` in an
  attribute, `esc_html_e()` where you would `echo`. Never `echo __(...)` unescaped.
* **Never concatenate.** `__('The list ') . $name . __(' was saved.')` cannot be translated into
  languages with different word order. Use one string with placeholders.
* **Add a `/* translators: ... */` comment** whenever the placeholder or the term is ambiguous —
  translators in POEditor see only the string.
* **Never put a variable inside the string** passed to a translation function; the extractor cannot
  read it.
* **Keep the text domain literal.** `__($text, $domain)` is invisible to `wp i18n make-pot`.
* Strings in `templates.dist/` are translated too and use the same domain.

After adding strings, add the German translation to
`languages/onoffice-for-wp-websites-de_DE.po` in the same PR — that is what POEditor imports and
what every other language is translated from. Untranslated German means the other locales get
nothing.

## The pipeline

```
                 you edit
   code strings ─────────► languages/onoffice-for-wp-websites-de_DE.po
                                        │  merged to master
                                        ▼
                          poeditor.yml  ──► POEditor webhook (import de_DE)
                                        └─► Google Chat notification
                                             │
                                translators work in POEditor
                                             │
        sync-from-poeditor.yml (daily 03:40 UTC, manual, or repository_dispatch)
                    runs bin/pull-translations.sh
                                             ▼
        languages/*.po, *.mo, *.pot  ──► committed to master as
                                          "ci(i18n): Sync PO, MO, and POT files from POEditor"
                                             │
                                     release build pulls again
                                             ▼
                                     plugin ZIP
```

**Upload** — `.github/workflows/poeditor.yml` fires on any push to `master` that changes
`languages/onoffice-for-wp-websites-de_DE.po`, calls the POEditor import webhook and posts a Google
Chat notification so the translation team knows there is work.

**Download** — `.github/workflows/sync-from-poeditor.yml` runs `bin/pull-translations.sh` daily at
03:40 UTC (also manually, or via a `poeditor_sync` repository dispatch), and commits the result
straight to `master` under the `oo-actions-bot` GitHub App identity. It only exports languages with
a translation percentage above 0 and skips `de`, the POEditor source language.

**Release** — translations are pulled from POEditor again before the ZIP is built
(see [RELEASE.md](RELEASE.md)), so a release always ships the latest
state regardless of when the last sync commit landed.

## Shipped locales

`bin/pull-translations.sh` maps POEditor language codes to WordPress locales and only syncs this
allowlist:

| WordPress locale | File |
| --- | --- |
| `de_DE` | `onoffice-for-wp-websites-de_DE.po/.mo` — **the source, edited by hand** |
| `de_AT`, `de_CH`, `de_DE_formal` | `onoffice-for-wp-websites-<locale>.po/.mo` |
| `es_ES`, `es_CL` | `onoffice-for-wp-websites-<locale>.po/.mo` |
| `fr_FR`, `it_IT`, `nl_NL` | `onoffice-for-wp-websites-<locale>.po/.mo` |
| `hr` | `onoffice-for-wp-websites_hr.po/.mo` — **underscore, not hyphen** |

Croatian is the one exception in the naming scheme; the script special-cases it. Do not "normalize"
it — WordPress resolves that file under that name.

Adding a locale means: enable it in POEditor, then add its code to `WP_LOCALES` **and** to
`should_sync_wp_locale()` in `bin/pull-translations.sh`. The script also deletes the obsolete
short-code files (`-de.po`, `-es.po`, …) on every run, so they will not come back.

## Locale vs. onOffice API language

WordPress locales and onOffice language codes are two different things. The mapping lives in
`plugin/Language.php`:

```php
Language::LOCALE_MAPPING   // 'de_AT' => 'AUT', 'fr_FR' => 'FRA', ... default 'DEU'
Language::getDefault()     // onOffice code for the current get_locale()
```

`SDKWrapper` sends that code to the API so estate texts and field labels come back in the right
language. **A new WordPress locale therefore needs two changes:** the sync allowlist (see *Shipped locales* above) *and* an
entry in `LOCALE_MAPPING` — otherwise the UI is translated but the estate data silently falls back
to `DEU`.

Field labels are a third layer: they can be overridden per language in the admin under custom
labels / translated labels, stored in the `oo_plugin_fieldconfig_*_translated_labels` tables. A
missing translation there is a data problem, not a `.po` problem.

## Multilingual sites (WPML)

WPML is supported explicitly, Polylang is not:

* `Language::getAllWPMLLanguages()` reads the `wpml_active_languages` filter, so `SDKWrapper` can
  request field types for every active site language at once.
* `EstateIdRequestGuard` / `AddressIdRequestGuard` build the per-language detail-page URLs
  (`createEstateDetailLinkForSwitchLanguageWPML`) so the language switcher keeps working on estate
  detail pages.

When changing detail-page URLs, slugs or titles, check both guards — a change that only works in the
default language breaks the language switcher on WPML sites.

## `npm run i18n` — use with care

```bash
npm run i18n   # wp i18n make-pot + update-po (de_DE) + make-mo
```

It requires [WP-CLI](https://make.wordpress.org/cli/handbook/guides/installing/) and:

1. regenerates `languages/onoffice-for-wp-websites.pot` from the source (`--no-location`,
   excluding `node_modules`, `vendor`, `tests`),
2. merges new strings into `languages/onoffice-for-wp-websites-de_DE.po`,
3. rebuilds the German `.mo`.

Two things to know before running it:

* Steps 1 and 3 write files that **guard-languages** rejects in a PR. Use it locally to find the new
  `msgid`s, then commit only the `de_DE.po` change.
* The committed `.pot` comes from POEditor, so a locally generated one will differ in the whole
  header and in comment placement. Do not commit that diff.

For most work you do not need this command at all — add the string, add its German translation to
`de_DE.po`, and let the pipeline do the rest.

## Review checklist for translations

* Every user-facing string wrapped, with the literal `'onoffice-for-wp-websites'` domain, escaped
  for its output context.
* No concatenated sentences; placeholders documented with a `translators:` comment where ambiguous.
* German translation for every new string present in `de_DE.po`.
* No changes to `.mo` files, `*.pot`, or any locale other than `de_DE`.
* A new locale also added to `Language::LOCALE_MAPPING`, not just to the sync allowlist.
* No hardcoded language name, country code or currency symbol where a mapping or a WordPress
  function already exists.
