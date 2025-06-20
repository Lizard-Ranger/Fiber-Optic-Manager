# Fiber-Optic Manager - Usage Guide

## 1. Register and Login
- Open the application in your browser.
- Click "Register" to create a new user account.
- Fill in your username, email, and password.
- After registering, log in with your credentials.

## 2. Dashboard
- After login, you will see the dashboard with quick actions and an overview of upcoming maintenance tasks:
  - **Add New Connection**: Add a new fiber-optic connection.
  - **Generate Report**: View and export all connections.
  - **View Network Map**: See all connections on an interactive map.
  - **Maintenance Schedule**: View and schedule maintenance tasks.

## 3. Add New Connection
- Click "Add New Connection" on the dashboard.
- Fill in the connection details, including:
  - **Name**: A descriptive name for the connection
  - **Type**: The fiber type (OS2, OM2, OM3, OM4, OM5)
  - **Length (km)**: The length of the connection in kilometers
  - **Status**: Current status (e.g., Active, Inactive, Maintenance)
  - **Core Count**: Number of fiber cores (defaults to 12)
  - **Location**: Text description of the location
  - **Notes**: Additional information about the connection
- Use the integrated map to pick the location (latitude/longitude will auto-fill).
- Click "Add Connection" to save.

## 4. Generate Report
- Click "Generate Report" on the dashboard.
- View all connections in a comprehensive table with all details.
- Download the report as a CSV file for use in Excel or other tools.

## 5. View Network Map
- Click "View Network Map" on the dashboard.
- See all connections as interactive pins on the map.
- The left panel shows a list of all connections with their details.
- Click a connection name in the list to automatically pan the map to that connection's pin and open its popup.
- Click a map pin to see connection details.
- Use the "View Splice Map" button next to each connection to access splice mapping.

## 6. Interactive Splice Mapping
- From the "Connections List" on the Network Map page, click "View Splice Map" next to a connection.
- The splice map will display Side A and Side B cores based on the connection's core count.
- **To create a link:** Click a core on Side A, then click a core on Side B. A temporary orange line will appear.
- **To save links:** Click the "Save" button to permanently store all new connections. Saved links will appear as blue lines and in the "Existing Links" list.
- **To reset links:** Click "Reset All Links" to clear all currently displayed (saved and pending) links for that connection.
- The splice map now uses composite core identifiers (e.g., A-Orange-2) for accurate tube/core mapping and linking.
- Links are saved using these composite keys, ensuring correct tube/core associations.
- The fiber_tubes table enables advanced mapping and color-coding features.

## 7. Maintenance Scheduling
- View upcoming maintenance tasks on the Dashboard.
- Click "Schedule Maintenance" from the Dashboard or navigation menu to add a new maintenance task.
- Fill in the details:
  - **Connection**: Select a specific connection (optional - can be general maintenance)
  - **Title**: Brief description of the maintenance task
  - **Description**: Detailed description of what needs to be done
  - **Scheduled Date**: When the maintenance should occur

## 8. Admin Features
- **Admin users** (with `is_admin=1` in the database) can access `/admin.php` for advanced management.
- **Manage Connections**: Delete any existing connection from the system.
- **Database Backup/Restore**:
  - Download a filtered SQL backup of the entire database (excludes HTML and unwanted comments).
  - Restore the database from a valid SQL file. The system validates the uploaded file to ensure it is a safe SQL dump.

## 9. Logout
- Click "Logout" in the navigation menu to end your session.

## Tips
- You can extend the application by adding more fields to connections, integrating with external APIs, or improving the map features.
- For more advanced mapping capabilities, consider integrating with services like Google Maps or Mapbox.
- The application uses Leaflet.js for interactive maps and OpenStreetMap for map tiles.

## Troubleshooting
- If you encounter database errors, double-check your `config/database.php` settings and ensure your MariaDB/MySQL server is running and accessible.
- Make sure all required PHP extensions (e.g., `php-pdo`, `php-mysql`) are installed and enabled.
- When performing database backup/restore, always use the provided functionality and upload only valid SQL files.
- For network or server errors during link saving, check your browser's developer console (Network tab) for the exact server response.

For more details, see the main [README](./README.md). 