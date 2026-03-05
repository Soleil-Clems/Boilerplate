#!/bin/bash
set -e


echo "Starting Symfony DEV container"


if [ "${WAIT_FOR_MYSQL:-0}" = "1" ]; then
  echo "Waiting for MySQL..."
  until php -r "
    new PDO(
      'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT'),
      getenv('MYSQL_USER') ?: 'root',
      getenv('MYSQL_PASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD')
    );
  " 2>/dev/null; do
    sleep 2
  done
  echo "MySQL is up"
fi

# Run migrations (dev only)
php bin/console doctrine:migrations:migrate \
  --no-interaction \
  --allow-no-migration || true

# Start Symfony server with fpm
exec php-fpm

echo "Application ready"

#exec symfony server:start --no-tls --allow-http --allow-all-ip
