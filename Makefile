ifeq ($(PREFIX),)
    PREFIX := /tmp
endif

ifeq ($(OO_PLUGIN_VERSION),)
	OO_PLUGIN_VERSION := $(shell git describe --tags --always)
endif

.PHONY: clean-target clean release copy-files-release composer-install-nodev pot

copy-files-release:
	install -d $(PREFIX)
	find * -type f \( ! -path "build/*" ! -path "vendor/bin/*" ! -path "./.*" ! -path "nbproject/*"  ! -path "tests/*" ! -path "documentation/*" ! -path "scripts/*" ! -iname ".*" ! -iname "Readme.md" ! -iname "phpstan.neon" ! -iname "phpstan-baseline.neon" ! -iname "phpunit.xml*" ! -iname "Makefile" ! -iname "phpcs.xml*" \) -exec install -v -D -T ./{} $(PREFIX)/{} \;

composer-install-nodev:
	cd $(PREFIX); composer install --no-dev -a
	find $(PREFIX) '-type' 'l' '-exec' 'unlink' '{}' ';'

pot:
	vendor/bin/wp i18n make-pot . languages/onoffice-for-wp-websites.pot --skip-js
	vendor/bin/wp i18n make-pot . languages/onoffice.pot --domain=onoffice --skip-js

release: pot copy-files-release composer-install-nodev

clean-target:
	rm -rf $(PREFIX)

clean: clean-target
