#!/bin/bash

# --- CONFIGURATION ---
# SSH_KEY="/home/www-data/.ssh/id_rsa"
# SSH_USER="kqajxcmy"
# SSH_HOST="162.241.253.168"
# 
# REMOTE_DB_NAME="kqajxcmy_WPJTH"
# REMOTE_DB_USER="kqajxcmy_WPJTH"
# REMOTE_DB_PASS="Ripple1956!"
# 
# LOCAL_DB_NAME="johngalemusic_dev"
# LOCAL_DB_USER="wpuser"
# LOCAL_DB_PASS="Ripple"
# 
# DUMP_FILE="/tmp/johngalemusic_db.sql"



# # --- STEP 1: Dump remote DB ---
# echo "📦 Dumping remote DB..."
# ssh -i "$SSH_KEY" "$SSH_USER@$SSH_HOST" \
#   "mysqldump -u$REMOTE_DB_USER -p'$REMOTE_DB_PASS' $REMOTE_DB_NAME" > "$DUMP_FILE"
# 
# if [ $? -ne 0 ]; then
#   echo "❌ Remote dump failed"
#   exit 1
# fi
# echo "✅ Remote dump complete: $DUMP_FILE"
# 
# # --- STEP 2: Drop and recreate local DB ---
# echo "🧹 Dropping local DB..."
# mysql -u"$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" -e "DROP DATABASE IF EXISTS \`$LOCAL_DB_NAME\`;"
# 
# echo "🛠️ Creating local DB..."
# mysql -u"$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" -e "CREATE DATABASE \`$LOCAL_DB_NAME\`;"
# 
# # --- STEP 3: Import the dump into local DB ---
# echo "📥 Importing into local DB..."
# mysql -u"$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" "$LOCAL_DB_NAME" < "$DUMP_FILE"
# 
# if [ $? -ne 0 ]; then
#   echo "❌ Import failed"
#   exit 1
# fi
# echo "✅ Local DB import complete"
