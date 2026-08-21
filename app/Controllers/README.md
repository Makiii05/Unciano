# Controllers/

Handle HTTP requests. Receive input, call the appropriate service, and return a view or JSON response.

## Rules

- Never contain SQL queries
- Never contain business logic
- Always delegate to a Service
- Return views for page requests, JSON for API requests

## Naming

```
StudentController.php      # Student CRUD
EnrollmentController.php   # Enrollment operations
GradeController.php        # Grade input/approval
```
