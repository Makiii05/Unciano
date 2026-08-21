# views/

All PHP page templates organized by portal/section.

## Structure

```
views/
├── admin/          # System administrator portal
├── registrar/      # Registrar office portal
├── admission/      # Admissions office portal
├── department/     # Department head portal
├── accounting/     # Accounting/cashier portal
├── student/        # Student self-service portal
├── teacher/        # Teacher portal
├── login/          # Login pages for all user types
└── application/    # Public application form (no auth required)
```

## Rules

- Pages should not contain SQL queries
- Use `htmlspecialchars()` for all dynamic output
- Include shared layout via `require`
