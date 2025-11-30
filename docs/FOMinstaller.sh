#!/bin/bash

# === CONFIGURATION ===
WEB_ROOT="/var/www/html"
REPO_URL="https://github.com/Lizard-Ranger/Fiber-Optic-Manager.git"   # <-- CHANGE THIS TO YOUR REPO
DB_NAME="fiberuser"
DB_USER="fiberuser"
DB_PASS="sL9DCLWXPUYkXHbNhYQH+NqAjkF#fh!VxRPs(Fxg7RXZ4gdNYyKWbLR&5)RBCJ7K"
SCHEMA_FILE="$WEB_ROOT/config/schema.sql"
BACKUP_DIR="$WEB_ROOT/uploads/db_backups"
BACKUP_SCRIPT="$WEB_ROOT/backup_web_and_db.sh"

# === 1. INSTALL SYSTEM PACKAGES ===
echo "[FOMinstaller] Installing required packages..."
apt-get update
apt-get install -y apache2 mariadb-server php php-mysql php-pdo php-xml php-mbstring git unzip

# === 2. CLONE OR COPY THE REPOSITORY ===
echo "[FOMinstaller] Deploying application files..."
if [ -d "$WEB_ROOT/.git" ]; then
    echo "[FOMinstaller] Existing git repo found, pulling latest..."
    cd $WEB_ROOT && git pull
else
    rm -rf $WEB_ROOT/*
    git clone $REPO_URL $WEB_ROOT
fi

# === 3. CREATE DIRECTORY STRUCTURE ===
mkdir -p $WEB_ROOT/uploads/otdr
mkdir -p $BACKUP_DIR
chown -R $USER:www-data $WEB_ROOT
chmod -R 755 $WEB_ROOT

# === 4. CREATE DATABASE AND USER ===
echo "[FOMinstaller] Setting up database..."
mysql -u root <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS $DB_NAME;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

# === 5. APPLY DATABASE SCHEMA ===
echo "[FOMinstaller] Importing schema..."
mysql -u $DB_USER -p$DB_PASS $DB_NAME < $SCHEMA_FILE

# === 6. POPULATE FIBER TUBE/CORE MAPPING TABLE ===
echo "[FOMinstaller] Populating fiber_tubes table..."
php $WEB_ROOT/populate_fiber_tubes.php

# === 7. SET PERMISSIONS FOR UPLOADS ===
echo "[FOMinstaller] Setting permissions for uploads..."
chown -R www-data:www-data $WEB_ROOT/uploads
chmod -R 775 $WEB_ROOT/uploads

# === 8. ENABLE APACHE REWRITE (optional, but recommended) ===
a2enmod rewrite
systemctl restart apache2

# === 9. CREATE BACKUP SCRIPT ===
cat <<'EOF' > $BACKUP_SCRIPT
#!/bin/bash
WEB_ROOT="/var/www/html"
BACKUP_DIR="$WEB_ROOT/uploads/db_backups"
DB_NAME="webapp_db"
DB_USER="fiberuser"
DB_PASS="fiberpass123"
DATE=$(date +'%Y-%m-%d_%H-%M-%S')
BACKUP_FILE="$BACKUP_DIR/backup_$DATE.tar.gz"
DB_DUMP="$BACKUP_DIR/db_$DATE.sql"

mkdir -p "$BACKUP_DIR"
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$DB_DUMP"
tar -czf "$BACKUP_FILE" -C "$WEB_ROOT" . -C "$BACKUP_DIR" "$(basename "$DB_DUMP")"
rm -f "$DB_DUMP"
find "$BACKUP_DIR" -name "backup_*.tar.gz" -mtime +7 -exec rm {} \;
EOF
chmod +x $BACKUP_SCRIPT

# === 10. SETUP DAILY CRON JOB FOR BACKUP ===
(crontab -l 2>/dev/null; echo "0 2 * * * $BACKUP_SCRIPT >> $BACKUP_DIR/backup.log 2>&1") | crontab -

# === 11. SUMMARY ===
echo "======================================"
echo "Fiber-Optic Manager installation complete!"
echo "Visit: http://<your-server-ip>/"
echo "Default DB: $DB_NAME, User: $DB_USER"
echo "Daily backup script installed: $BACKUP_SCRIPT"
echo "Backups will be stored in: $BACKUP_DIR"
echo "======================================" 