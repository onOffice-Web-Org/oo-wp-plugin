ifeq ($(PREFIX),)
    PREFIX := /tmp
endif

ifeq ($(OO_PLUGIN_VERSION),)
	OO_PLUGIN_VERSION := $(shell git describe --tags --always)
endif

.PHONY: clean-zip clean-target clean release

copy-files-release:
	install -d $(PREFIX)/onoffice
	find * -type f \( ! -path "bin/*" ! -path "build/*" ! -path "./.*" ! -path "nbproject/*"  ! -path "tests/*" ! -iname ".*" ! -iname "Readme.md" ! -iname "phpstan.neon" ! -iname "phpunit.xml*" ! -iname "Makefile" ! -iname "phpcs.xml*" \) -exec install -v -D -T ./{} $(PREFIX)/onoffice/{} \;

change-title: copy-files-release
	sed -i -r "s/(Plugin Name: .+) \(dev\)$$/\1/" $(PREFIX)/onoffice/plugin.php

add-version: copy-files-release
	sed -i -r "s/Version:\ [^$$]+/Version:\ $(patsubst v%,%,$(OO_PLUGIN_VERSION))/" $(PREFIX)/onoffice/plugin.php

composer-install-nodev:
	cd $(PREFIX)/onoffice; composer install --no-dev -a
	
release: copy-files-release change-title add-version composer-install-nodev

test-zip: copy-files-release add-version composer-install-nodev
	cd $(PREFIX); zip -r onoffice.zip onoffice/

clean-zip:
	rm -f $(PREFIX)/onoffice.zip

clean-target:
	rm -rf $(PREFIX)/onoffice/

clean: clean-zip clean-target