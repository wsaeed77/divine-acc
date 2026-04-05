#!/bin/bash
set -euo pipefail
# Run from /var/www/divinne-acc after Laravel code is present (as ec2-user)

cd /var/www/divinne-acc

if [ ! -f artisan ]; then
  echo "ERROR: artisan not found. Clone / upload the app to /var/www/divinne-acc first."
  exit 1
fi

composer install --no-dev --optimize-autoloader
npm ci
npm run build

if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate --force
fi

# DB from ec2-setup (optional)
if [ -f /home/ec2-user/.divinne_db_password ]; then
  DB_PASS=$(cat /home/ec2-user/.divinne_db_password)
  sed -i "s/^DB_DATABASE=.*/DB_DATABASE=divinne_acc/" .env
  sed -i "s/^DB_USERNAME=.*/DB_USERNAME=divinne/" .env
  sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
fi
sed -i "s/^APP_ENV=.*/APP_ENV=production/" .env 2>/dev/null || true
sed -i "s/^APP_DEBUG=.*/APP_DEBUG=false/" .env 2>/dev/null || true

php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo chown -R ec2-user:apache /var/www/divinne-acc
sudo chmod -R ug+rwx storage bootstrap/cache
sudo systemctl reload php-fpm
sudo systemctl reload nginx

echo "Deploy finished."
