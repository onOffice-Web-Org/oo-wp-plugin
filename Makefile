ifeq ($(PREFIX),)
    PREFIX := /tmp
endif

ifeq ($(OO_PLUGIN_VERSION),)
	OO_PLUGIN_VERSION := $(shell git describe --tags --always)
endif

.PHONY: clean-target clean release copy-files-release composer-install-nodev build test-docker

copy-files-release:
	install -d $(PREFIX)
	find * -type f \( ! -path "build/*" ! -path "vendor/bin/*" ! -path "./.*" ! -path "nbproject/*"  ! -path "tests/*" ! -path "documentation/*" ! -path "scripts/*" ! -iname ".*" ! -iname "Readme.md" ! -iname "phpstan.neon" ! -iname "phpstan-baseline.neon" ! -iname "phpunit.xml*" ! -iname "Makefile" ! -iname "phpcs.xml*" \) -exec install -v -D -T ./{} $(PREFIX)/{} \;

composer-install-nodev:
	cd $(PREFIX); composer install --no-dev -a
	find $(PREFIX) '-type' 'l' '-exec' 'unlink' '{}' ';'

release: copy-files-release composer-install-nodev

build:
	composer install
	npm install
	npm run build
	rm -rf onoffice-for-wp-websites
	mkdir onoffice-for-wp-websites
	find . -maxdepth 1 ! -name . ! -name .git ! -name build ! -name node_modules ! -name onoffice-for-wp-websites ! -name onoffice-for-wp-websites.zip -exec cp -r {} onoffice-for-wp-websites/ \;
	zip -r onoffice-for-wp-websites.zip onoffice-for-wp-websites
	rm -rf onoffice-for-wp-websites

clean-target:
	rm -rf $(PREFIX)

test-docker:
	docker compose -f docker-compose.test.yml run --rm test $(filter-out $@,$(MAKECMDGOALS))

%:
	@:

clean: clean-target
