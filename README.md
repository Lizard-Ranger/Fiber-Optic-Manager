# Fiber-Optic Manager

A modern web-based application for managing fiber-optic network infrastructure. Built with PHP, MariaDB, Leaflet.js for mapping, and SVG for interactive splice mapping.

---

## Features
- User registration and login
- Dashboard with quick actions and maintenance overview
- Add, view, and manage fiber-optic connections (type: OS2, OM2, OM3, OM4, OM5)
- Interactive network map with connection locations (Leaflet.js)
- **Interactive Splice Mapping:**
  - Visual representation of fiber core connections (Side A to Side B) with lines drawn
  - Dynamic core counts based on connection properties
  - Batch saving of new links and reset functionality
  - Composite core identifiers (e.g., A-Orange-2) for accurate tube/core mapping
- **OTDR Results:** Upload and view PDF OTDR test results for each connection
- **Maintenance Scheduling:** Schedule, view, and manage upcoming maintenance tasks
- **Admin Section:**
  - Manage (delete) connections
  - Download filtered database backups
  - Restore database from validated SQL files
- **Color-coded UI:** TIA-598-C standard for fiber types and tubes/cores
- Automated daily backup with retention

---

## Quick Start Installation

1. **Clone or copy the repository to your server.**
2. **Run the installer:**
   ```bash
   sudo bash FOMinstaller.sh
   ```
   - This script installs all dependencies, sets up the database, applies the schema, populates tube/core mapping, and configures permissions and backups.
3. **Access the app:**
   - Open `http://<your-server-ip>/` in your browser.

---

## Usage
- Register a new user and log in.
- Use the dashboard to add new connections, generate reports, view the network map, or manage maintenance tasks.
- On the Add Connection page, you can pick a location on the map for each connection.
- The Network Map page shows all connections as pins. Click a name in the list to focus the map on that connection.
- **Interactive Splice Map:** Access from the Network Map or Connections list to visually manage fiber core splices. Links are saved using composite tube/core identifiers.
- **Maintenance:** View upcoming maintenance tasks on the dashboard or schedule new ones.
- **Admin users** can access `/admin.php` for advanced management, including connection deletion and database backup/restore.

---

## Database Schema (Summary)
- **connections:** Stores all fiber connections and their properties
- **splice_links:** Stores links between Side A and Side B cores (composite identifiers)
- **fiber_tubes:** Stores tube/core color mapping for each connection (TIA-598-C)
- **maintenance:** Maintenance tasks for each connection
- **users:** User authentication and admin access

See `docs/DB_SCHEMA.md` for full details.

---

## Backup & Restore
- Daily automated backups are created and retained for 7 days.
- Use the admin section to download or restore database backups.

---

## Security Notes
- Passwords are hashed using PHP's `password_hash()`
- Database access uses PDO with prepared statements
- Session management for authentication
- All user input is validated and output is escaped
- Sensitive files are protected by permissions
- HTTPS is recommended for production

---

## Support & Contributions
- For issues, open a ticket or contact the maintainer.
- Pull requests are welcome! Please follow the coding and security guidelines.

---

## License
[MIT License](LICENSE) 