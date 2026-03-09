#!/usr/bin/env bash
set -e

WP_TESTS_DIR="${WP_TESTS_DIR:-/tmp/wp-test-cache/wordpress-tests-lib}"
WP_CORE_DIR="${WP_CORE_DIR:-/tmp/wp-test-cache/wordpress}"
DB_HOST="${DB_HOST:-mysql}"
DB_NAME="${DB_NAME:-wordpress_test}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-testMySQLPassword123}"

if [ ! -f "$WP_TESTS_DIR/includes/functions.php" ]; then
	echo "Installing WordPress test suite..."
	export WP_TESTS_DIR WP_CORE_DIR
	bash scripts/install-wp-tests.sh "$DB_NAME" "$DB_USER" "$DB_PASS" "$DB_HOST" latest
fi

cd /app
[ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist --no-scripts

mkdir -p build/logs
exec ./vendor/bin/phpunit --fail-on-warning --fail-on-risky --disallow-test-output --coverage-clover build/logs/clover.xml "$@"
