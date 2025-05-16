#!/bin/bash
set -e

CERT_FILE="/etc/apache2/ssl/whim-local.pem"
KEY_FILE="/etc/apache2/ssl/whim-local.key"

if [[ ! -f "$CERT_FILE" || ! -f "$KEY_FILE" ]]; then
  openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
    -keyout "$KEY_FILE" \
    -out "$CERT_FILE" \
    -subj "/C=CA/ST=BC/L=MapleRidge/O=WHIM/OU=Dev/CN=localhost"

chmod 644 "$CERT_FILE"
chown root:www-data "$KEY_FILE"
chmod 640 "$KEY_FILE"


fi
