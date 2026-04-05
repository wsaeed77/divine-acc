#!/bin/bash
set -euo pipefail
# Run ON THE EC2 INSTANCE (after DNS for your subdomain points to this server's public IP).
# Opens HTTP-01 on port 80; ensure the EC2 security group allows inbound TCP 80 and 443.
#
# Usage:
#   sudo bash deploy/ec2-tls-certbot.sh app.example.com you@example.com
#
# Cloudflare: set the DNS record to "DNS only" (grey cloud) for Let's Encrypt HTTP-01, or use DNS validation instead.
#
# If certificate was already issued, certbot will renew/reinstall; to change domain, edit server_name and re-run.

DOMAIN="${1:?Usage: sudo bash deploy/ec2-tls-certbot.sh SUBDOMAIN.EXAMPLE.COM EMAIL@EXAMPLE.COM}"
EMAIL="${2:?Usage: second argument must be your contact email for Lets Encrypt}"

CONF_PATH="/etc/nginx/conf.d/divinne.conf"
APP_ROOT="/var/www/divinne-acc"

sudo tee "$CONF_PATH" > /dev/null <<EOF
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name ${DOMAIN};

    root ${APP_ROOT}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;
    client_max_body_size 64M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

sudo nginx -t
sudo systemctl reload nginx

sudo certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --redirect

if [ -f "${APP_ROOT}/.env" ]; then
  sudo sed -i "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" "${APP_ROOT}/.env"
  if grep -q '^SESSION_SECURE_COOKIE=' "${APP_ROOT}/.env"; then
    sudo sed -i "s/^SESSION_SECURE_COOKIE=.*/SESSION_SECURE_COOKIE=true/" "${APP_ROOT}/.env"
  else
    echo SESSION_SECURE_COOKIE=true | sudo tee -a "${APP_ROOT}/.env" > /dev/null
  fi
  sudo -u ec2-user bash -c "cd ${APP_ROOT} && php artisan config:cache"
fi

sudo systemctl reload php-fpm
sudo systemctl reload nginx

echo "TLS configured for https://${DOMAIN}"
