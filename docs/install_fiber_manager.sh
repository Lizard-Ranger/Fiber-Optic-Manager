#!/bin/bash

# === CONFIGURATION ===
WEB_ROOT="/var/www/html"
REPO_URL="https://github.com/Lizard-Ranger/Fiber-Optic-Manager.git"   # <-- CHANGE THIS TO YOUR REPO
DB_NAME="webapp_db"
DB_USER="fiberuser"
DB_PASS="fiberpass123"
SCHEMA_FILE="$WEB_ROOT/config/schema.sql"
BACKUP_DIR="$WEB_ROOT/uploads/db_backups"
BACKUP_SCRIPT="$WEB_ROOT/backup_web_and_db.sh"

# === 1. INSTALL SYSTEM PACKAGES ===
sudo apt-get update
sudo apt-get install -y apache2 mariadb-server php php-mysql php-pdo php-xml php-mbstring git unzip

# === 2. CLONE THE REPOSITORY ===
sudo rm -rf $WEB_ROOT/*
sudo git clone $REPO_URL $WEB_ROOT

# === 3. CREATE DIRECTORY STRUCTURE (if not in repo) ===
sudo mkdir -p $WEB_ROOT/uploads/otdr
sudo mkdir -p $BACKUP_DIR
sudo chown -R $USER:www-data $WEB_ROOT
sudo chmod -R 755 $WEB_ROOT

# === 4. CREATE DATABASE AND USER ===
sudo mysql -u root <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS $DB_NAME;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

# === 5. APPLY DATABASE SCHEMA ===
sudo mysql -u $DB_USER -p$DB_PASS $DB_NAME < $SCHEMA_FILE

# === 5b. POPULATE FIBER TUBE/CORE MAPPING TABLE ===
sudo php $WEB_ROOT/populate_fiber_tubes.php

# === 6. SET PERMISSIONS FOR UPLOADS ===
sudo chown -R www-data:www-data $WEB_ROOT/uploads
sudo chmod -R 775 $WEB_ROOT/uploads

# === 7. ENABLE APACHE REWRITE (optional, but recommended) ===
sudo a2enmod rewrite
sudo systemctl restart apache2

# === 8. CREATE BACKUP SCRIPT ===
cat <<'EOF' | sudo tee $BACKUP_SCRIPT > /dev/null
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

sudo chmod +x $BACKUP_SCRIPT

# === 9. SETUP DAILY CRON JOB FOR BACKUP ===
(crontab -l 2>/dev/null; echo "0 2 * * * $BACKUP_SCRIPT >> $BACKUP_DIR/backup.log 2>&1") | crontab -

echo "======================================"
echo "Fiber-Optic Manager installation complete!"
echo "Visit: http://<your-server-ip>/"
echo "Default DB: $DB_NAME, User: $DB_USER"
echo "Daily backup script installed: $BACKUP_SCRIPT"
echo "Backups will be stored in: $BACKUP_DIR"
echo "======================================"