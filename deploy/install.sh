#!/usr/bin/env bash
# Installiert oder aktualisiert das Toiletten-Meldesystem auf Ubuntu (22.04 oder 24.04).
# Starten im Projektordner mit:  sudo bash deploy/install.sh
# Kann beliebig oft ausgeführt werden. Passwort und Meldungen bleiben erhalten.

set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "Bitte mit sudo starten: sudo bash deploy/install.sh"
    exit 1
fi

QUELLE="$(cd "$(dirname "$0")/.." && pwd)"
ZIEL=/var/www/toiletten

echo "== 1/6 Programme installieren: nginx (Webserver), PHP und SQLite"
apt-get update
DEBIAN_FRONTEND=noninteractive apt-get install -y nginx php-fpm php-sqlite3 php-mbstring ufw

PHPV=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
PHP_SOCK="/run/php/php${PHPV}-fpm.sock"

echo "== 2/6 Dateien nach $ZIEL kopieren"
mkdir -p "$ZIEL/config" "$ZIEL/data"
rm -rf "$ZIEL/public" "$ZIEL/src" "$ZIEL/tools"
cp -r "$QUELLE/public" "$QUELLE/src" "$QUELLE/tools" "$QUELLE/datenbank.sql" "$ZIEL/"
cp "$QUELLE/config/config.example.php" "$ZIEL/config/"

echo "== 3/6 Hausmeister-Passwort"
if [ ! -f "$ZIEL/config/config.php" ] || grep -q "'hausmeister_passwort_hash' => ''" "$ZIEL/config/config.php"; then
    php "$ZIEL/tools/passwort_setzen.php"
else
    echo "Passwort ist schon gesetzt. Ändern mit: sudo php $ZIEL/tools/passwort_setzen.php"
fi

echo "== 4/6 Rechte setzen (der Webserver darf nur lesen, außer im Ordner data/)"
chown -R root:www-data "$ZIEL"
find "$ZIEL" -type d -exec chmod 750 {} +
find "$ZIEL" -type f -exec chmod 640 {} +
chown -R www-data:www-data "$ZIEL/data"

echo "== 5/6 Webserver einrichten"
sed "s#__PHP_SOCK__#${PHP_SOCK}#g" "$QUELLE/deploy/nginx.conf" > /etc/nginx/sites-available/toiletten
ln -sf /etc/nginx/sites-available/toiletten /etc/nginx/sites-enabled/toiletten
rm -f /etc/nginx/sites-enabled/default
systemctl enable --now "php${PHPV}-fpm" nginx
nginx -t
systemctl reload nginx

echo "== 6/6 Firewall: nur SSH und Webseite erlauben"
if ufw allow OpenSSH && ufw allow 80/tcp && ufw --force enable; then
    echo "Firewall aktiv."
else
    echo "Hinweis: Firewall konnte nicht aktiviert werden (in manchen Containern normal)."
fi

IP=$(hostname -I | awk '{print $1}')
echo
echo "Fertig!"
echo "  Schüler-Seite:     http://$IP/"
echo "  Hausmeister-Seite: http://$IP/hausmeister.html"
