ifeq ($(PREFIX),)
    PREFIX := /tmp
endif

ifeq ($(OO_PLUGIN_VERSION),)
	OO_PLUGIN_VERSION := $(shell git describe --tags --always)
endif

.PHONY: clean-zip clean-target clean release copy-files-release composer-install-nodev change-title add-version pot

copy-files-release:
	install -d $(PREFIX)/onoffice
	find * -type f \( ! -path "build/*" ! -path "vendor/bin/*" ! -path "./.*" ! -path "nbproject/*"  ! -path "tests/*" ! -path "documentation/*" ! -path "scripts/*" ! -iname ".*" ! -iname "Readme.md" ! -iname "phpstan.neon" ! -iname "phpstan-baseline.neon" ! -iname "phpunit.xml*" ! -iname "Makefile" ! -iname "phpcs.xml*" \) -exec install -v -D -T ./{} $(PREFIX)/onoffice/{} \;

change-title: copy-files-release
	sed -i -r "s/(Plugin Name: .+) \(dev\)$$/\1/" $(PREFIX)/onoffice/plugin.php

add-version: copy-files-release
	sed -i -r "s/Version:\ [^$$]+/Version:\ $(patsubst v%,%,$(OO_PLUGIN_VERSION))/" $(PREFIX)/onoffice/plugin.php

composer-install-nodev:
	cd $(PREFIX)/onoffice; composer install --no-dev -a
	find $(PREFIX)/onoffice '-type' 'l' '-exec' 'unlink' '{}' ';'

pot:
	vendor/bin/wp i18n make-pot . languages/onoffice-for-wp-websites.pot --skip-js
	vendor/bin/wp i18n make-pot . languages/onoffice.pot --domain=onoffice --skip-js
	sed -i -r "s/onOffice for WP-Websites \(dev\)/onOffice for WP-Websites/" languages/onoffice-for-wp-websites.pot
	sed -i -r "s/onOffice for WP-Websites \(dev\)/onOffice for WP-Websites/" languages/onoffice.pot

release: pot copy-files-release change-title add-version composer-install-nodev

unprocessed-release: pot copy-files-release composer-install-nodev

test-zip: pot copy-files-release add-version composer-install-nodev
	cd $(PREFIX); zip -r onoffice.zip onoffice/

clean-zip:
	rm -f $(PREFIX)/onoffice.zip

clean-target:
	rm -rf $(PREFIX)/onoffice/

clean: clean-zip clean-target
