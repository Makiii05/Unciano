# Services/

Contain all business logic for the application.

## Responsibilities

- Validation and duplicate checking
- Database transactions
- Business rules and workflows
- CRUD operations via PDO
- Enrollment, grading, and assessment logic

## Naming

```
StudentService.php         # Student-related operations
EnrollmentService.php      # Enrollment workflow
GradeService.php           # Grade computation
```

## Rules

- Use PDO prepared statements for all queries
- Use transactions for multi-step critical operations
- Never return views or handle HTTP concerns
