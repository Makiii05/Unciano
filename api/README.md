# api/

JSON API endpoints for AJAX/Fetch requests.

## Structure

```
api/
├── auth/                  # Login endpoint
├── students/              # Search/get students
├── teachers/              # Search/get teachers
├── programs/              # Get programs by department
├── levels/                # Get levels by program
├── subjects/              # Search/get subjects
├── subject-offerings/     # Get offerings by term/department
├── teacher-offerings/     # Get offerings by term, class list
├── enlistment/            # Check duplicates, get by student
├── grades/                # CRUD for columns, scores, submit
├── prospectus/            # Get by curriculum
├── assessment/            # Get student assessment
├── cashier/               # Process payment, toggle account
├── grade-report/          # Generate PDF
└── admission/             # Admission-related AJAX
```

## Response Format

All endpoints return JSON:

```json
{
    "success": true,
    "data": [],
    "message": ""
}
```
