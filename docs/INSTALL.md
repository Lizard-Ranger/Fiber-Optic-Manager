# Fiber-Optic Manager - Installation Guide

This guide outlines the steps to set up the Fiber-Optic Manager application on your web server.

## 1. System Requirements
Ensure your server meets the following:
- **Web Server:** Apache or Nginx
- **PHP:** Version 7.4 or higher
- **Database:** MariaDB or MySQL
- **PHP Extensions:**
  - `php-mysql` (or `php-mysqli` for MySQL)
  - `php-pdo` (required for database interactions)
  - `php-xml` (often needed for various PHP functionalities)
  - `php-mbstring` (for multibyte string support)

## 2. Obtain the Application Files
- **Clone the repository:**
  ```bash
  git clone [repository_url] /var/www/html/
  ```
- **Or, copy the project files** to your web server's document root (e.g., `/var/www/html/`).

## 3. Database Setup

### A. Create the Database
Access your MariaDB/MySQL server (e.g., via `mysql` client or phpMyAdmin) and create the database:
```sql
CREATE DATABASE IF NOT EXISTS webapp_db;
```

### B. Create a Database User (Recommended for Production)
It is highly recommended to create a dedicated database user with specific privileges instead of using `root`.
```sql
CREATE USER 'fiberuser'@'localhost' IDENTIFIED BY 'fiberpass123';
GRANT ALL PRIVILEGES ON webapp_db.* TO 'fiberuser'@'localhost';
FLUSH PRIVILEGES;
```
(Replace `fiberuser`, `fiberpass123`, and `localhost` as appropriate for your environment).

### C. Import Database Schema
The application requires specific tables. You can import the complete schema using the provided `schema.sql` file:
```bash
mysql -u fiberuser -pfiberpass123 webapp_db < /var/www/html/config/schema.sql
```
This will create all required tables, including the new `fiber_tubes` table and the corrected `splice_links` table (with `core_b`).

### D. Populate Tube/Core Mapping Table
After importing the schema, run the following script to auto-populate the `fiber_tubes` table for all connections:
```bash
php /var/www/html/populate_fiber_tubes.php
```

## 4. Configure Database Connection
Edit the database configuration file:

`config/database.php`
```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'fiberuser'); // Use the dedicated user
define('DB_PASS', 'fiberpass123'); // Use the dedicated user's password
define('DB_NAME', 'webapp_db');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Create PDO instance
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );
    
    // Set charset
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    // Log the error
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
    }
?>
```
Update `DB_USER` and `DB_PASS` to match the database user you created.

## 5. Set File Permissions
Ensure your web server has read/write permissions where necessary. For example, if you plan to use the database backup/restore feature, the web server user (e.g., `www-data` on Ubuntu/Debian) might need write access to a temporary directory (e.g., `/tmp`) or a designated backup directory.

## 6. Access the Application
Open your web browser and navigate to your server's IP address or domain name where the application files are hosted (e.g., `http://your-server-ip/` or `http://your-domain.com/`).

You should see the Fiber-Optic Manager homepage. You can then register a new user and begin managing your connections.

## 7. First Login
- Register a new user via the web interface.
- To make a user admin, run:
```sql
UPDATE webapp_db.users SET is_admin=1 WHERE username='yourusername';
```

## 8. (Optional) Secure Your Server
- Enable HTTPS (Let's Encrypt or other SSL)
- Change default passwords
- Restrict admin access

## 9. Troubleshooting
- If you encounter database errors, ensure the schema has been imported correctly
- Check that the database user has proper permissions
- Verify PHP extensions are installed and enabled
- Check web server error logs for additional debugging information

---
For troubleshooting, see the [USAGE.md](./USAGE.md) and [SECURITY.md](./SECURITY.md) docs. 