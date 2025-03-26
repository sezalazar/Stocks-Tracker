#!/bin/sh
set -ex

wait_for_postgres() {
    until php -r "new PDO('pgsql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" >/dev/null 2>&1; do
        echo "Waiting for Postgres to be ready..."
        sleep 5
    done
}

if [ ! -f .env ]; then
    cp .env.example .env
fi

sed -i "s|APP_ENV=.*|APP_ENV=${APP_ENV}|g" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=${APP_DEBUG}|g" .env
sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=${DB_CONNECTION}|g" .env
sed -i "s|DB_HOST=.*|DB_HOST=${DB_HOST}|g" .env
sed -i "s|DB_PORT=.*|DB_PORT=${DB_PORT}|g" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|g" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|g" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|g" .env

if [ -z "$(grep '^APP_KEY=' .env | cut -d '=' -f2)" ]; then
    php artisan key:generate
fi

git config --global --add safe.directory /var/www/html

composer install --prefer-dist --optimize-autoloader
npm install
npm run build

# Wait for Postgres
wait_for_postgres

if ! php artisan migrate:status | grep -q "No migrations"; then
    echo "Running migrations..."
    php artisan migrate --seed --force
else
    echo "Migrations are up-to-date. Skipping."
fi

# Create symlink storage if it doesnt exists
if [ -L "public/storage" ]; then
    if [ ! -e "public/storage" ]; then
        echo "Removing dangling symlink public/storage"
        rm public/storage
        php artisan storage:link
    fi
else
    php artisan storage:link
fi

chmod -R 755 public/storage

chown -R www-data:www-data /var/www/html

exec php-fpm
