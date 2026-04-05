#!/bin/bash
set -euo pipefail

# Amazon Linux 2023 — nginx + php-fpm + MariaDB + Composer (run once as ec2-user with sudo)
# PHP 8.4+ is required by composer.lock. If you previously installed php8.1, remove it before php8.4:
#   sudo dnf remove -y 'php8.1-*' && sudo dnf install -y php8.4-fpm ...

if ! rpm -q php8.4-fpm >/dev/null 2>&1; then
  sudo dnf install -y nginx git mariadb105-server nodejs npm \
    php8.4-fpm php8.4-cli php8.4-mbstring php8.4-xml php8.4-pdo php8.4-mysqlnd \
    php8.4-bcmath php8.4-opcache php8.4-process php8.4-common php8.4-gd php8.4-intl php8.4-zip
fi

if [ ! -f /usr/local/bin/composer ]; then
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
  sudo chmod +x /usr/local/bin/composer
fi
composer --version

DB_PASS=$(openssl rand -base64 32 | tr -dc 'a-zA-Z0-9' | head -c 24)
echo "$DB_PASS" > /home/ec2-user/.divinne_db_password
chmod 600 /home/ec2-user/.divinne_db_password

sudo systemctl enable --now mariadb
sleep 2
sudo mysql -e "CREATE DATABASE IF NOT EXISTS divinne_acc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'divinne'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON divinne_acc.* TO 'divinne'@'localhost'; FLUSH PRIVILEGES;"

sudo mkdir -p /var/www/divinne-acc
sudo chown ec2-user:apache /var/www/divinne-acc

sudo tee /etc/nginx/conf.d/divinne.conf > /dev/null <<'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/divinne-acc/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;
    client_max_body_size 64M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

sudo rm -f /etc/nginx/conf.d/default.conf 2>/dev/null || true

sudo nginx -t
sudo systemctl enable --now php-fpm
sudo systemctl enable --now nginx
sudo systemctl reload nginx

if ! rpm -q certbot >/dev/null 2>&1; then
  sudo dnf install -y certbot python3-certbot-nginx
fi
sudo systemctl enable --now certbot-renew.timer 2>/dev/null || true

sudo setsebool -P httpd_can_network_connect 1 2>/dev/null || true
sudo setsebool -P httpd_can_network_connect_db 1 2>/dev/null || true

echo ""
echo "=== Divinne EC2 base setup complete ==="
echo "App path:     /var/www/divinne-acc"
echo "DB name:      divinne_acc"
echo "DB user:      divinne"
echo "DB password:  (saved to /home/ec2-user/.divinne_db_password)"
echo "Next: clone or rsync the Laravel app into /var/www/divinne-acc, then run deploy/ec2-deploy-app.sh"
echo "TLS:  point DNS A/AAAA to this host, open ports 80+443, then: sudo bash deploy/ec2-tls-certbot.sh your.subdomain.domain.com you@email.com"
