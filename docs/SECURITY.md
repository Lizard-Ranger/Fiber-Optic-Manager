# Fiber-Optic Manager - Security Notes

## Authentication
- User authentication is handled via PHP sessions.
- Passwords are securely hashed using PHP's `password_hash()` and verified with `password_verify()`.
- Admin access is controlled by the `is_admin` field in the users table.

## Database Security
- All database queries, including those for connections, splice links, and maintenance schedules, use PDO with prepared statements to prevent SQL injection.
- Database credentials are stored in `config/database.php` and should be protected.
- Use a dedicated database user with limited privileges (not root).
- Database configuration uses constants for better security practices.

## Session Security
- Sessions are started at the top of each page that requires authentication.
- Always call `session_start()` before accessing session variables.
- Use `session_destroy()` on logout to clear session data.

## Input Validation and Output Escaping
- All user input, including data for new connections, splice links, and maintenance tasks, is rigorously validated and sanitized on the server side.
- Output is escaped using `htmlspecialchars()` to prevent Cross-Site Scripting (XSS) vulnerabilities.
- SQL backup/restore is validated for file type and content (no HTML, only valid SQL) to prevent malicious code execution.
- Backups are filtered to remove unwanted comments (e.g., sandbox mode) ensuring clean data.

## File Permissions
- Ensure that sensitive files (like `config/database.php`) are not world-readable.
- The `docs/` folder and other non-public folders should not be web-accessible in production.
- Appropriate write permissions are necessary for features like database backup/restore (e.g., to `/tmp` or a designated backup directory).

## HTTPS
- For production deployments, always use HTTPS to encrypt data in transit and protect sensitive information.

## Recommendations
- Regularly update PHP and all dependencies to their latest stable versions.
- Use strong, unique passwords for all user accounts and database credentials.
- Consider adding CSRF protection for all forms, especially for sensitive operations.
- Monitor server logs for suspicious activity and unexpected errors.
- Restrict admin access to trusted users only and follow the principle of least privilege.

## Further Reading
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Security_Cheat_Sheet.html)
- [PHP: Security](https://www.php.net/manual/en/security.php)

## New Features
- Splice links now use composite core identifiers (e.g., A-Orange-2) for accurate mapping.
- The fiber_tubes table is protected by the same validation and access controls as other tables. 