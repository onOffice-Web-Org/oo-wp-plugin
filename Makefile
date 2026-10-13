ifeq ($(PREFIX),)
    PREFIX := /tmp
endif

ifeq ($(OO_PLUGIN_VERSION),)
	OO_PLUGIN_VERSION := $(shell git describe --tags --always)
endif

.PHONY: clean-target clean release copy-files-release composer-install-nodev build test-docker

copy-files-release:
	install -d $(PREFIX)
	find * -type f \( ! -path "build/*" ! -path "vendor/bin/*" ! -path "node_modules/*" ! -path "./.*" ! -path "bin/*" ! -path "nbproject/*"  ! -path "tests/*" ! -path "documentation/*" ! -path "scripts/*" ! -iname ".*" ! -iname "Readme.md" ! -iname "CLAUDE.md" ! -iname "phpstan.neon" ! -iname "phpstan-baseline.neon" ! -iname "phpunit.xml*" ! -iname "Makefile" ! -iname "phpcs.xml*" \) -exec install -v -D -T ./{} $(PREFIX)/{} \;

composer-install-nodev:
	cd $(PREFIX); composer install --no-dev -a --no-scripts
	php $(CURDIR)/scripts/prefix-dependencies.php $(PREFIX)
	cd $(PREFIX); composer dump-autoload --no-dev -a --no-scripts
	find $(PREFIX) '-type' 'l' '-exec' 'unlink' '{}' ';'

release: copy-files-release composer-install-nodev

build:
	composer install
	npm install
	npm run build
	rm -f onoffice-for-wp-websites.zip
	# Stage outside the repo, copy-files-release would otherwise pick up the staging dir itself
	STAGING=$$(mktemp -d) && \
		$(MAKE) release PREFIX=$$STAGING/onoffice-for-wp-websites && \
		(cd $$STAGING && zip -r $(CURDIR)/onoffice-for-wp-websites.zip onoffice-for-wp-websites); \
		STATUS=$$?; rm -rf $$STAGING; exit $$STATUS

clean-target:
	rm -rf $(PREFIX)

test-docker:
	docker compose -f docker-compose.test.yml run --rm test $(filter-out $@,$(MAKECMDGOALS))

%:
	@:

clean: clean-target
