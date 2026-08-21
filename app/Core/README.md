# Core/

Infrastructure classes that power the application.

## Files

- `Database.php` — Singleton PDO connection. All services use this to access the database.

## Rules

- Never create a new PDO connection inside services
- Always use `Database::connection()` to get the shared instance
