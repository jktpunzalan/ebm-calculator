#!/bin/bash
set -e
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "Deploy complete."
