# Building

## Set up

1. Run `git clone --recursive https://github.com/onOfficeGmbH/oo-wp-plugin.git` to clone this repository and its submodules.
2. Install [composer](https://getcomposer.org/).
3. Run `composer check-platform-reqs` to ensure you have installed the required PHP extensions.
    - Example: The command tells you that the extensions `ext-mbstring` and `ext-simplexml` are missing. On Ubuntu, you can install these by running `sudo apt install php8.2-mbstring php8.2-simplexml`. Make sure you match the version to your php installation, currently 8.2
4. Run `composer install` to install the dependencies.

## Prefixed dependencies

WordPress loads every plugin into one PHP process, so two plugins bundling different versions of
the same library crash each other - whichever loads first wins. To avoid this, the production
dependencies are moved into `vendor-prefixed/` and rewritten to the `onOffice\WPlugin\Vendor\` namespace
(`Parsedown` becomes `onOffice_WPlugin_Vendor_Parsedown`) by
[Strauss](https://github.com/BrianHenryIE/strauss).

* `composer install` and `composer update` run this automatically (`composer prefix-dependencies`).
* Import the prefixed names in plugin code: `use onOffice\WPlugin\Vendor\DI\ContainerBuilder;`.
* `vendor-prefixed/` is generated and not checked in.
* The alias file Strauss offers is deleted on purpose - it would put the unprefixed names back into
  the global namespace and reintroduce the conflicts.
* Only PHP packages are prefixed. `select2` and `tom-select` are JavaScript assets and stay in
  `vendor/`, because the plugin enqueues them by path.

> `composer install --no-dev` in this repository does **not** produce a working plugin: Strauss is
> itself a dev dependency, so `vendor-prefixed/` is never built. The prefixing step fails loudly in
> that case instead of leaving a broken tree behind. Use `composer install` for development and
> `make release` for a shippable artifact.

### Updating dependencies

`composer update` (or `composer update <vendor>/<package>`) rebuilds `vendor-prefixed/` on its own via
`post-update-cmd`. Dependabot pull requests need no extra work either - they only change
`composer.lock`, and CI regenerates the prefixed copies from it.

A **new production dependency** has to be classified in `composer.json`:

* PHP package -> add it to `extra.strauss.packages` so it gets prefixed.
* Asset-only package (JavaScript or CSS that the plugin enqueues by path) -> add it to
  `extra.strauss.exclude_from_copy.packages` so it stays in `vendor/`.

Forgetting this is not possible: `composer prefix-dependencies` aborts and names any production
package that is in neither list.

`composer check-prefixed-imports` guards the other direction: it fails if any source file still
references a prefixed dependency under its original name. Such a reference does not necessarily
error - it can silently bind to the copy another plugin loaded first. CI runs the check on every
pull request.

That check matters most for `SDK/`, which is a mirror of
[onOfficeGmbH/sdk](https://github.com/onOfficeGmbH/sdk) and carries its own README, CHANGELOG and
phpunit config. `SDK/src/internal/ApiCall.php` imports phpgeo and therefore uses the prefixed names,
which upstream does not. A re-sync from upstream silently reverts those two imports; the check turns
that into a failing build instead of a broken radius search.

Two things to know when running it by hand:

* Prefixing consumes the packages in `vendor/` and deletes them afterwards, so it cannot run twice in
  a row. Run `composer install` first; a second run without it exits with a notice instead of
  replacing `vendor-prefixed/` with an empty autoloader.
* The release build calls `php scripts/prefix-dependencies.php <staging-dir>` rather than
  `vendor/bin/strauss`, because Strauss takes its project directory from the current working
  directory while it is only installed as a dev dependency in this repository.

## Make a release .zip

This is how you can generate a .zip to upload to a WordPress instance.

1. Run `PREFIX=/tmp/release/onoffice-for-wp-websites make release`.
    - The `PREFIX` needs to be an absolute path. If you use a relative path, the script might not behave correctly and get into an infinite loop.
2. Run `sed -i "s/Version: .*$/Version: $(git describe --tags)/" /tmp/release/onoffice-for-wp-websites/plugin.php` to overwrite the version so that you can distinguish it from the stable release.
3. Create a zip file that you can upload to WordPress by running:
    1. `cd /tmp/release` (This is needed so that the zip has the correct folder hierarchy.)
    2. `zip -r onoffice-for-wp-websites.zip ./onoffice-for-wp-websites`
4. Upload `/tmp/release/onoffice-for-wp-websites.zip` to WordPress.