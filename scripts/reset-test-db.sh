#!/usr/bin/env bash
set -e

DB_HOST="${DB_HOST:-mysql}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-testMySQLPassword123}"
DB_NAME="${DB_NAME:-wordpress_test}"

echo "Resetting test database..."

php -r "
try {
  \$pdo = new PDO(
    'mysql:host=$DB_HOST',
    '$DB_USER',
    '$DB_PASS',
    [PDO::ATTR_TIMEOUT => 5]
  );
  
  // Drop the test database if it exists
  \$pdo->exec('DROP DATABASE IF EXISTS \`$DB_NAME\`');
  
  // Create a fresh test database
  \$pdo->exec('CREATE DATABASE \`$DB_NAME\`');
  
  echo 'Test database reset successfully!' . PHP_EOL;
} catch (PDOException \$e) {
  echo 'Error resetting database: ' . \$e->getMessage() . PHP_EOL;
  exit(1);
}
"
