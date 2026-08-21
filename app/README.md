# app/

Core application logic for the Enrollment Management System.

## Structure

```
app/
├── Controllers/    # Handle HTTP requests, call services, return responses
├── Services/       # Contain all business logic and database operations
├── Core/           # Infrastructure (Database connection)
└── Helpers/        # Reusable utility functions
```

## Flow

```
PHP Page → Controller → Service → PDO → MySQL
```

## Rules

- Controllers never contain SQL or business logic
- Services never contain HTTP concerns
- Models/Directories stay focused on their single responsibility
