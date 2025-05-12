#!/bin/bash

# === CONFIG ===
DEFAULT_OWNER="john"
DEFAULT_GROUP="devs"
ROOT="/var/www/projects"

# === Resolve project path ===
PROJECT="$1"
FULL="$ROOT/$PROJECT"

if [[ -z "$PROJECT" || ! -d "$FULL" ]]; then
    echo "Invalid or missing project: $PROJECT in directory: $FULL"
    exit 1
fi

echo "🔧 Fixing permissions for $PROJECT (owner: $OWNER, group: $DEFAULT_GROUP)"

# General ownership and group permissions
chown -R "$OWNER:$DEFAULT_GROUP" "$FULL"
chmod -R g+rwX "$FULL"
find "$FULL" -type d -exec chmod g+s {} +

# WordPress writeable directories
for dir in "$FULL/wp-content" "$FULL/wp-content/uploads" "$FULL/wp-content/plugins" "$FULL/wp-content/themes"; do
    if [[ -d "$dir" ]]; then
        echo "➕ Making writable: $dir"
        chmod -R g+w "$dir"
    fi
done

# Ensure .htaccess and wp-config.php are writable
for file in "$FULL/.htaccess" "$FULL/wp-config.php"; do
    if [[ -f "$file" ]]; then
        chmod g+w "$file"
    fi
done

echo "✅ Permissions fixed."
