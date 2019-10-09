ifeq ($(PREFIX),)
    PREFIX := /tmp
endif

.PHONY: clean-zip clean-target clean release

copy-files-release:
	install -d $(PREFIX)/onoffice
	find * -type f \( ! -path "bin/*" ! -path "./.*" ! -path "nbproject/*"  ! -path "tests/*" ! -iname ".*" ! -iname "Readme.md" ! -iname "phpstan.neon" ! -iname "phpunit.xml*" ! -iname "Makefile" ! -iname "phpcs.xml*" \) -exec install -v -D -T ./{} $(PREFIX)/onoffice/{} \;

composer-install-nodev: copy-files-release
	cd $(PREFIX)/onoffice; composer install --no-dev -a
	
release: composer-install-nodev

zip: release
	cd $(PREFIX); zip -r onoffice.zip onoffice/

clean-zip:
	rm -f $(PREFIX)/onoffice.zip

clean-target:
	rm -rf $(PREFIX)/onoffice/

clean: clean-zip clean-target