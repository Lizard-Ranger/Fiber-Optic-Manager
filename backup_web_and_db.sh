#!/bin/bash

# === CONFIGURATION ===
WEB_ROOT="/var/www/html"
BACKUP_DIR="/var/www/Backups"
DB_NAME="webapp_db"
DB_USER="fiberuser"
DB_PASS="fiberpass123"
DATE=$(date +'%Y-%m-%d_%H-%M-%S')
BACKUP_FILE="$BACKUP_DIR/backup_$DATE.tar.gz"
DB_DUMP="$BACKUP_DIR/db_$DATE.sql"

# === CREATE BACKUP DIRECTORY IF IT DOESN'T EXIST ===
mkdir -p "$BACKUP_DIR"

# === DUMP THE DATABASE ===
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$DB_DUMP"

# === ARCHIVE WEB FILES AND DB DUMP ===
tar -czf "$BACKUP_FILE" -C "$WEB_ROOT" . -C "$BACKUP_DIR" "$(basename "$DB_DUMP")"

# === REMOVE THE TEMPORARY DB DUMP ===
rm -f "$DB_DUMP"

# === DELETE BACKUPS OLDER THAN 7 DAYS ===
find "$BACKUP_DIR" -name "backup_*.tar.gz" -mtime +7 -exec rm {} \;

# === OPTIONAL: LOG SUCCESS ===
echo "Backup completed: $BACKUP_FILE"