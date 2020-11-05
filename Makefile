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

pot:
	vendor/bin/wp i18n make-pot . languages/onoffice.pot --skip-js
	sed -i -r "s/onOffice for WP-Websites \(dev\)/onOffice for WP-Websites/" languages/onoffice.pot

concat-css:
	@cat `ls -I ./third_party/slick/*\.css`  ./css/*\.css > ./css/build/oo-wp-plugin.css

mkdirs:
	@mkdir ./css/build/
	
release: pot copy-files-release change-title add-version composer-install-nodev mkdirs concat-css

test-zip: pot copy-files-release add-version composer-install-nodev
	cd $(PREFIX); zip -r onoffice.zip onoffice/

clean-zip:
	rm -f $(PREFIX)/onoffice.zip

clean-target:
	rm -rf $(PREFIX)/onoffice/

clean: clean-zip clean-target