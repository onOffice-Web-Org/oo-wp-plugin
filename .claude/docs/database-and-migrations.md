# Database changes and migrations

Part of the oo-wp-plugin working instructions — index: [../../CLAUDE.md](../../CLAUDE.md).

**Deleting the plugin wipes all plugin data from the database** — that is documented, intended
behaviour (`Readme.md`, `DatabaseChanges::deinstall()`). Every change in this area affects live
customer installations that cannot be re-migrated. Treat it accordingly.

## The mechanism

All schema and data migrations live in **one** file:
[`plugin/Installer/DatabaseChanges.php`](../../plugin/Installer/DatabaseChanges.php).

A version counter in the WordPress option `oo_plugin_db_version` is compared against
`DatabaseChanges::MAX_VERSION` (currently **66**) and drives a **deliberate fallthrough
`switch (true)`**, so an installation at any old version replays every step up to the current one:

```php
// DELIBERATE FALLTHROUGH
switch (true) {
    case $dbversion <= 14:
        $this->updateSortByUserDefinedDefault();
    case $dbversion <= 16:
        $this->migrationsDataSimilarEstates();
    ...
    case $dbversion <= 65:
        $this->…();
    default:
        $dbversion = DatabaseChanges::MAX_VERSION;
}
```

Above the switch, every `dbDelta( $this->getCreateQuery…() )` call runs **unconditionally** —
`dbDelta` is idempotent and also serves fresh installs.

## Adding a migration

1. **Schema change** → add or adjust the `getCreateQuery…()` method and make sure its
   `dbDelta(...)` call is in the unconditional block in `install()`.
2. **Data migration** → add a `case $dbversion <= <current MAX_VERSION>:` at the **bottom** of the
   switch, directly above the `default:` that assigns `MAX_VERSION`.
3. **Bump `MAX_VERSION`** by one.
4. **Update the test.** `TestClassDatabaseChanges` asserts the full expected schema, and the source
   file says so itself: *"If you are modifying this, please also make sure to edit the test."*
5. **New table** → also add it to `deinstall()`, or uninstalling leaves it behind forever.

## Rules

- **Never renumber or reorder existing cases**, and never change an old migration's behaviour —
  installations in the field have already passed it. A renumbering silently skips or repeats steps
  on real customer data. This is the highest-severity finding in this repo.
- **Do not add `break`.** The fallthrough is the design.
- **Do not assume a column exists.** A migration runs on installations from any older version; guard
  reads against schemas that predate the column you are adding.
- New tables: `oo_plugin_*` prefix behind `$wpdb->prefix`, charset/collation from
  `getCharsetCollate()`.
- `install()` short-circuits when `get_site_option('oo_plugin_db_version')` already equals
  `MAX_VERSION` — a migration that needs to re-run for the same version number cannot, so pick a new
  number instead of editing in place.
- Fresh installs (`$dbversion == 0`) additionally get `getCreateQueryCache()` and
  `setDetailTemplate()`; a few migrations are deliberately new-install-only (guarded by
  `$isNewInstall`). Check which case you actually need.

## Tables

21 tables, all `oo_plugin_*` behind `$wpdb->prefix`, addressed only via
`RecordManager::TABLENAME_*`:

```
oo_plugin_listviews                                oo_plugin_forms
oo_plugin_listviews_address                        oo_plugin_form_fieldconfig
oo_plugin_listview_contactperson                   oo_plugin_form_activityconfig
oo_plugin_fieldconfig                              oo_plugin_form_taskconfig
oo_plugin_address_fieldconfig                      oo_plugin_form_multipage_title
oo_plugin_fieldconfig_form_defaults                oo_plugin_picturetypes
oo_plugin_fieldconfig_form_defaults_values         oo_plugin_contacttypes
oo_plugin_fieldconfig_form_customs_labels          oo_plugin_sortbyuservalues
oo_plugin_fieldconfig_form_translated_labels       (+ cache table)
oo_plugin_fieldconfig_estate_customs_labels
oo_plugin_fieldconfig_estate_translated_labels
oo_plugin_fieldconfig_address_customs_labels
oo_plugin_fieldconfig_address_translated_labels
```

The `*_translated_labels` tables hold per-language field label overrides. A missing label in another
language is a **data** problem, not a `.po` problem — see
[documentation/TRANSLATIONS.md](../../documentation/TRANSLATIONS.md).

## Options

Plugin options live in the `onoffice-*` / `oo_plugin_*` namespace and are read through
`WPOptionWrapperBase`, not `get_option()` directly. Notable ones: `oo_plugin_db_version`,
`onoffice-settings-apikey`, `onoffice-plugin-version-stored`,
`onoffice-duplicate-check-warning`.

## In tests

`tests/bootstrap.php` replaces `DatabaseChangesInterface` with `Mocks\DatabaseChangesDummy`, so the
test suite does **not** run migrations. `TestClassDatabaseChanges` instantiates the real class
explicitly. If a new feature needs real tables in a test, create them in the test — see
[testing.md](testing.md).
