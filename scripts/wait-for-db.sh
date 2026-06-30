#!/usr/bin/env bash
set -e

DB_HOST="${DB_HOST:-mysql}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-testMySQLPassword123}"
DB_NAME="${DB_NAME:-wordpress_test}"
MAX_ATTEMPTS=30
ATTEMPT=0

echo "Waiting for database at $DB_HOST to be ready..."

while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
  ATTEMPT=$((ATTEMPT + 1))
  
  if php -r "
    try {
      \$pdo = new PDO(
        'mysql:host=$DB_HOST',
        '$DB_USER',
        '$DB_PASS',
        [PDO::ATTR_TIMEOUT => 2]
      );
      echo 'Database is ready!' . PHP_EOL;
      exit(0);
    } catch (PDOException \$e) {
      exit(1);
    }
  " 2>/dev/null; then
    exit 0
  fi
  
  echo "Attempt $ATTEMPT/$MAX_ATTEMPTS: Database not ready yet, retrying in 1 second..."
  sleep 1
done

echo "Failed to connect to database after $MAX_ATTEMPTS attempts"
exit 1
