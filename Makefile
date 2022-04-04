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

composer-install-nodev:
	cd $(PREFIX)/onoffice; composer install --no-dev -a
	find $(PREFIX)/onoffice '-type' 'l' '-exec' 'unlink' '{}' ';'

pot:
	vendor/bin/wp i18n make-pot . languages/onoffice-for-wp-websites.pot --skip-js
	vendor/bin/wp i18n make-pot . languages/onoffice.pot --domain=onoffice --skip-js

release: pot copy-files-release composer-install-nodev

test-zip: pot copy-files-release add-version composer-install-nodev
	cd $(PREFIX); zip -r onoffice.zip onoffice/

clean-zip:
	rm -f $(PREFIX)/onoffice.zip

clean-target:
	rm -rf $(PREFIX)/onoffice/

clean: clean-zip clean-target
