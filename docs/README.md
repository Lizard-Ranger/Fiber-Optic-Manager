# Fiber-Optic Manager

A web-based application for managing fiber-optic network infrastructure. Built with PHP, MariaDB, Leaflet.js for mapping, and SVG for interactive splice mapping.

## Features
- User registration and login
- Dashboard with quick actions and maintenance overview
- Add, view, and manage fiber-optic connections (type: OS2, OM2, OM3, OM4, OM5)
- Interactive network map with connection locations
- Generate and download connection reports (CSV)
- **Interactive Splice Mapping:**
    - Visual representation of fiber core connections (Side A to Side B) with lines drawn.
    - Dynamic core counts based on connection properties.
    - Batch saving of new links and reset functionality.
    - **Composite core identifiers** (e.g., A-Orange-2) are now used for accurate tube/core mapping.
- **fiber_tubes table**: Stores tube/core color mapping for each connection (TIA-598-C standard).
- **splice_links table**: Now uses `core_a` and `core_b` (not `core_c`), both VARCHAR(50), to support composite identifiers.
- **Maintenance Scheduling:** Schedule, view, and manage upcoming maintenance tasks for connections.
- Responsive, modern UI
- **Enhanced Admin section:**
    - Manage (delete) connections.
    - Download filtered database backups (excludes HTML and unwanted comments).
    - Restore database from a validated SQL file.
- **Type**: The fiber type (OS2, OM2, OM3, OM4, OM5)

## Folder Structure
```
/var/www/html/
├── add_connection.php         # Add new fiber-optic connection
├── admin.php                 # Admin dashboard
├── admin_backup.php          # Admin: backup/restore database
├── admin_connections.php     # Admin: manage/delete connections
├── config/                   # Configuration and schema files
│   ├── database.php         # Database connection configuration
│   └── schema.sql           # Complete database schema
├── css/                      # Stylesheets
├── dashboard.php             # User dashboard and maintenance overview
├── docs/                     # Documentation
├── generate_report.php       # Report and CSV export
├── includes/                 # Header, footer, and shared PHP
├── index.php                 # Home page
├── js/                       # JavaScript (if any)
├── login.php                 # User login
├── logout.php                # User logout
├── network_map.php           # Interactive map of connections
├── register.php              # User registration
├── schedule_maintenance.php  # Schedule new maintenance tasks
├── splice_map.php            # Interactive fiber splice mapping
└── ...
```

## Setup Instructions
1. **Install dependencies:**
   - PHP 7.4+
   - MariaDB or MySQL
   - Apache or Nginx
   - PHP extensions: `php-mysql`, `php-pdo`, `php-xml`, `php-mbstring`
2. **Clone or copy the project to your web server directory.**
3. **Create the database and tables:**
   - Import `config/schema.sql` into MariaDB/MySQL.
4. **Configure database access:**
   - Edit `config/database.php` with your DB credentials.
5. **Set permissions:**
   - Ensure the web server can write to necessary folders (if needed, e.g., for database backups).
6. **Access the app:**
   - Open `http://your-server-ip/` in your browser.

## Usage
- Register a new user and log in.
- Use the dashboard to add new connections, generate reports, view the network map, or manage maintenance tasks.
- On the Add Connection page, you can pick a location on the map for each connection.
- The Network Map page shows all connections as pins. Click a name in the list to focus the map on that connection.
- **Interactive Splice Map:** Access from the Network Map or Connections list to visually manage fiber core splices.
- **Maintenance:** View upcoming maintenance tasks on the dashboard or schedule new ones.
- **Admin users** can access `/admin.php` for advanced management, including connection deletion and database backup/restore.

## Security Notes
- Passwords are hashed using PHP's `password_hash`.
- Database access uses PDO with prepared statements to prevent SQL injection.
- Session management is used for authentication.
- SQL backup/restore is validated and filtered for safety.
- Admin access is restricted via `is_admin` field.

## Customization
- You can extend the app by adding more fields to connections, integrating with external APIs, or improving the map features.
- For advanced mapping, consider integrating with Google Maps or Mapbox.

## Credits
- [Leaflet.js](https://leafletjs.com/) for interactive maps
- [OpenStreetMap](https://www.openstreetmap.org/) for map tiles

## License
This project is open source and free to use for any purpose. 