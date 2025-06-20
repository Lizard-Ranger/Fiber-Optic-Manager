# Fiber-Optic Manager - Database Schema

## Database: `webapp_db`

### Table: `users`
| Field       | Type         | Null | Key | Default           | Extra          |
|-------------|--------------|------|-----|-------------------|----------------|
| id          | INT          | NO   | PRI | auto_increment    |                |
| username    | VARCHAR(50)  | NO   | UNI |                   |                |
| email       | VARCHAR(100) | NO   | UNI |                   |                |
| password    | VARCHAR(255) | NO   |     |                   |                |
| is_admin    | TINYINT(1)   | NO   |     | 0                 |                |
| created_at  | TIMESTAMP    | NO   |     | CURRENT_TIMESTAMP |                |
| updated_at  | TIMESTAMP    | NO   |     | CURRENT_TIMESTAMP | on update ...  |

### Table: `connections`
| Field        | Type           | Null | Key | Default           | Extra          |
|--------------|----------------|------|-----|-------------------|----------------|
| id           | INT            | NO   | PRI | auto_increment    |                |
| name         | VARCHAR(100)   | NO   |     |                   |                |
| type         | VARCHAR(50)    | NO   |     |                   |                |  // Allowed: OS2, OM2, OM3, OM4, OM5
| length_m     | INT            | NO   |     |                   |                |
| status       | VARCHAR(30)    | NO   |     |                   |                |
| location     | VARCHAR(255)   | YES  |     |                   |                |
| notes        | TEXT           | YES  |     |                   |                |
| latitude     | DECIMAL(10,7)  | YES  |     |                   |                |
| longitude    | DECIMAL(10,7)  | YES  |     |                   |                |
| core_count   | INT            | NO   |     | 12                |                |
| otdr_results | VARCHAR(255)   | YES  |     |                   |                |
| created_at   | TIMESTAMP      | NO   |     | CURRENT_TIMESTAMP |                |

### Table: `maintenance`
| Field         | Type           | Null | Key | Default           | Extra          |
|---------------|----------------|------|-----|-------------------|----------------|
| id            | INT            | NO   | PRI | auto_increment    |                |
| connection_id | INT            | YES  | MUL |                   |                |
| title         | VARCHAR(100)   | NO   |     |                   |                |
| description   | TEXT           | YES  |     |                   |                |
| scheduled_date| DATE           | NO   |     |                   |                |
| status        | VARCHAR(30)    | NO   |     | Scheduled         |                |
| created_at    | TIMESTAMP      | NO   |     | CURRENT_TIMESTAMP |                |

### Table: `splice_links`
| Field         | Type           | Null | Key | Default           | Extra          |
|---------------|----------------|------|-----|-------------------|----------------|
| id            | INT            | NO   | PRI | auto_increment    |                |
| connection_id | INT            | NO   | MUL |                   |                |
| core_a        | VARCHAR(50)    | NO   |     |                   |                |
| core_b        | VARCHAR(50)    | NO   |     |                   |                |
| created_at    | TIMESTAMP      | NO   |     | CURRENT_TIMESTAMP |                |

### Table: `fiber_tubes`
| Field         | Type           | Null | Key | Default           | Extra          |
|---------------|----------------|------|-----|-------------------|----------------|
| id            | INT            | NO   | PRI | auto_increment    |                |
| connection_id | INT            | NO   | MUL |                   |                |
| tube_number   | INT            | NO   |     |                   |                |
| tube_name     | VARCHAR(32)    | NO   |     |                   |                |
| tube_color    | VARCHAR(16)    | NO   |     |                   |                |
| core_number   | INT            | NO   |     |                   |                |
| core_name     | VARCHAR(32)    | NO   |     |                   |                |
| core_color    | VARCHAR(16)    | NO   |     |                   |                |
| created_at    | TIMESTAMP      | NO   |     | CURRENT_TIMESTAMP |                |

## Relationships
- Each `connection` can have multiple `maintenance` tasks (one-to-many).
- Each `connection` can have multiple `splice_links` (one-to-many).
- Each `connection` can have multiple `fiber_tubes` (one-to-many).
- `maintenance.connection_id` links to `connections.id` (nullable, for general tasks).
- `splice_links.connection_id` links to `connections.id`.
- `fiber_tubes.connection_id` links to `connections.id`.
- `users` table is for authentication and admin access.

## Example SQL
See `config/schema.sql` for the complete schema and table creation statements. 