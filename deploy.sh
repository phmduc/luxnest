#!/usr/bin/env bash
#
# Production deploy script for luxnest.vn
# Run as root on the VPS: cd /www/wwwroot/luxnest.vn && ./deploy.sh
#
set -euo pipefail

APP_DIR="/www/wwwroot/luxnest.vn"
PHP=php8.4

cd "$APP_DIR"

echo "==> Pulling latest code"
git pull origin main

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing JS dependencies and building assets"
npm install
npm run build

echo "==> Running database migrations"
sudo -u www $PHP artisan migrate --force

echo "==> Rebuilding caches"
sudo -u www $PHP artisan config:clear
sudo -u www $PHP artisan cache:clear
sudo -u www $PHP artisan config:cache
sudo -u www $PHP artisan route:cache
sudo -u www $PHP artisan view:cache

echo "==> Fixing permissions"
chown -R www:www storage bootstrap/cache public/build

echo "==> Done"
