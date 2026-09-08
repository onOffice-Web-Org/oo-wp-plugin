# Architecture

Part of the oo-wp-plugin working instructions — index: [../../CLAUDE.md](../../CLAUDE.md).

## Bootstrap

[`plugin.php`](../../plugin.php) is the only place hooks are registered. It defines the constants
(`ONOFFICE_PLUGIN_VERSION`, `ONOFFICE_PLUGIN_DIR`, `ONOFFICE_DI_CONFIG_PATH`,
`ONOFFICE_API_SERVER`, `OO_DB_REQUEST_CACHE`), builds the DI container, wires every
`add_action`/`add_filter`, and loads both autoloaders — `vendor/` **and** `vendor-prefixed/`.

Keep hook registration in `plugin.php` and the callback bodies in controllers.

## Prefixed dependencies (Strauss)

**Read this before touching any `use` statement.**

WordPress runs all plugins in one PHP process, so bundled libraries collide between plugins —
whichever loads first wins. Production PHP dependencies are therefore copied to `vendor-prefixed/`
and rewritten into the `onOffice\WPlugin\Vendor\` namespace by
[Strauss](https://github.com/BrianHenryIE/strauss).

- Import the **prefixed** name in plugin, SDK and test code:
  `use onOffice\WPlugin\Vendor\DI\Container;` — not `use DI\Container;`. Same for `Parsedown`,
  phpgeo, ALTCHA and PSR-11.
- `composer install` / `composer update` regenerate `vendor-prefixed/` via `post-install-cmd`.
- An unprefixed reference **does not necessarily throw** — it can silently bind to whatever copy
  another plugin loaded first. That is the bug being prevented, and why
  `composer check-prefixed-imports` runs in CI on every PR.
- A **new production dependency** must be classified in `composer.json`: PHP package →
  `extra.strauss.packages`; asset-only package (JS/CSS enqueued by path, e.g. `select2`,
  `tom-select`) → `extra.strauss.exclude_from_copy.packages`. Prefixing aborts and names any
  production package that is in neither list.
- `vendor-prefixed/` is generated and gitignored. `composer install --no-dev` does **not** produce a
  working plugin (Strauss is itself a dev dependency) — use `make release`.

Full detail: [documentation/Building.md](../../documentation/Building.md).

## Dependency injection

PHP-DI 7, one container built in `plugin.php` from [`config/di-config.php`](../../config/di-config.php);
the path is available everywhere as `ONOFFICE_DI_CONFIG_PATH`.

- Prefer constructor injection and let autowiring do the work.
- Add a binding **only** when needed: interface → implementation, a scalar constructor parameter, or
  `wpdb`.
- `wpdb` is bound to `WpdbReadCacheProxy::getWpdb()` — an in-request read cache
  (`OO_DB_REQUEST_CACHE`). **Never inject the `$wpdb` global directly.**
- Legacy wart: `SDKWrapper`, `Template` and `DatabaseChanges` each build *their own* container.
  `SDKWrapper` at least caches it statically. Do not copy the pattern into new code — inject
  `Container` or the concrete dependency.

## The `*Environment` / `*Configuration` seam

The dominant testability pattern. A collaborator-heavy class takes one interface that exposes
everything it needs from the outside world:

```
EstateListEnvironment          (interface)
EstateListEnvironmentDefault   (production, wired in di-config.php)
EstateListEnvironmentTest      (test double)
```

Same shape for `AddressListEnvironment`, `EstateViewSimilarEstatesEnvironment`,
`FormPostConfiguration`, `FormPostContactConfiguration`, `FormPostOwnerConfiguration`,
`FormPostInterestConfiguration`, `InputVariableReaderConfig`, `FieldnamesEnvironment`.

**Wart worth knowing:** the `*Test` doubles are shipped inside `plugin/`
(`plugin/Form/FormPostConfigurationTest.php`, `plugin/WP/WPOptionWrapperTest.php`,
`plugin/Field/FieldnamesEnvironmentTest.php`, …), not in `tests/`. Keep the naming for consistency
with the existing set, but put **new** doubles in `tests/Mocks/` — see [testing.md](testing.md).

## Layers

| Layer | Rule |
| --- | --- |
| `Record/RecordManager*` | **all** `$wpdb` access to the plugin's own tables. Table names only via `RecordManager::TABLENAME_*` + `$wpdb->prefix`. |
| `API/`, `SDKWrapper` | all onOffice API traffic. `APIClientActionGeneric` is the generic action; responses are cached in `Cache/DBCache`. |
| `WP/` | thin wrappers around WordPress globals — `WPOptionWrapperBase`, `WPNonceWrapper`, `WPQueryWrapper`, `WPScreenWrapper`, `WPScriptStyleBase`, `WPRedirectWrapper`, `WpdbReadCacheProxy`. |
| `Field/`, `Types/` | field metadata and typed value objects (`Field`, `FieldTypes`, `FieldsCollection`); the `FieldModuleCollectionDecorator*` chain layers custom labels, geo fields and module-specific fields on top. |
| `Model/` + `Renderer/` | admin forms: `FormModelBuilder*` defines input models, `InputField*Renderer` renders them. Never emit admin HTML from a controller. |
| `Gui/` | admin pages and WP list tables. **Excluded from coverage** in `phpunit.xml.dist`. |

## Request flow (frontend)

```
[oo_estate view="x"]
  -> ContentFilterShortCodeEstate            Controller/ContentFilter, registered by
                                             ContentFilterShortCodeRegistrator
  -> DataListViewFactory + RecordManagerRead  own DB tables: the saved view configuration
  -> Filter/DefaultFilterBuilder*             view config + request vars -> API filter
  -> SDKWrapper -> onOffice\SDK               HTTP, cached in DBCache (TTL 3600)
  -> ViewFieldModifier/*                      shape the API response for output
  -> Template.php -> templates.dist/...       or the customer's own template copy
```

Request variables enter through `RequestVariablesSanitizer`, never through raw superglobals — see
[coding-standards.md](coding-standards.md#security-and-output-escaping).

## Templates are a public API

`templates.dist/` is **copied by customers** into `onoffice-personalized/templates` or
`<theme>/onoffice-theme/templates` and then edited. Consequences for every change:

- Renaming or removing a variable that `Template::getIncludeContents()` exposes — `$pEstates`,
  `$pForm`, `$pAddressList`, `$generateSortDropDown`, `$getListName`, `$scriptLoader` — silently
  breaks every customer template *and* the four theme repos. Treat it as a breaking change. The
  method must also never expose `$this`.
- Improving `templates.dist/` does not reach customers who already copied it. Say so in the PR when
  a fix only lands in the dist templates.
- Templates receive data through `ArrayContainerEscape`, which escapes **on read** (default
  `Escape::HTML`). Do not add a second layer of escaping on top of values coming from it.

## Database and migrations

Own topic, own file: [database-and-migrations.md](database-and-migrations.md). The short version —
all schema and data migrations live in the single file `plugin/Installer/DatabaseChanges.php`,
driven by a version counter and a deliberate `switch (true)` fallthrough. Never renumber or edit an
existing case.
