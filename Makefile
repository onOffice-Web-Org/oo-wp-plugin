ifeq ($(PREFIX),)
    PREFIX := /tmp
endif

ifeq ($(OO_PLUGIN_VERSION),)
	OO_PLUGIN_VERSION := $(shell git describe --tags --always)
endif

.PHONY: clean-zip clean-target clean release

copy-files-release:
	install -d $(PREFIX)/onoffice
	find * -type f \( ! -path "bin/*" ! -path "./.*" ! -path "nbproject/*"  ! -path "tests/*" ! -iname ".*" ! -iname "Readme.md" ! -iname "phpstan.neon" ! -iname "phpunit.xml*" ! -iname "Makefile" ! -iname "phpcs.xml*" \) -exec install -v -D -T ./{} $(PREFIX)/onoffice/{} \;

add-version: copy-files-release
	sed -r "s/Version:\ [^$$]+/Version:\ $(patsubst v%,%,$(OO_PLUGIN_VERSION))/" plugin.php > $(PREFIX)/onoffice/plugin.php

composer-install-nodev: add-version
	cd $(PREFIX)/onoffice; composer install --no-dev -a
	
release: composer-install-nodev

zip: release
	cd $(PREFIX); zip -r onoffice.zip onoffice/

clean-zip:
	rm -f $(PREFIX)/onoffice.zip

clean-target:
	rm -rf $(PREFIX)/onoffice/

clean: clean-zip clean-target