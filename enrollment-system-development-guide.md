# Enrollment Management System - Development Guide

## 1. Project Overview

This guide defines the development structure and conventions for the Enrollment Management System.

The system will use **organized Vanilla PHP** instead of Laravel. The goal is to keep the codebase clean and maintainable while ensuring that a developer with basic PHP knowledge can understand and maintain it without learning a custom framework.

### Technology Stack

- **Backend:** Vanilla PHP
- **Database:** MySQL
- **Database Access:** PDO
- **Frontend:** HTML + Tailwind CSS
- **Client-side JavaScript:** Vanilla JavaScript
- **Asynchronous Requests:** Fetch API

### Architecture Principle

> Do not recreate Laravel. Do not build a custom framework. Use simple, organized PHP.

The core application flow is:

```text
PHP Page
    ↓
Controller
    ↓
Service
    ↓
PDO
    ↓
MySQL
```

For page rendering:

```text
PHP Page
    ↓
Controller
    ↓
Service
    ↓
PDO / MySQL
    ↓
Controller
    ↓
View / HTML
```

For asynchronous requests:

```text
JavaScript Fetch API
    ↓
PHP API Endpoint
    ↓
Controller
    ↓
Service
    ↓
PDO / MySQL
    ↓
JSON Response
    ↓
JavaScript
```

---

# 2. Project Structure

A recommended starting structure is:

```text
project/
│
├── app/
│   ├── Controllers/
│   │   ├── StudentController.php
│   │   ├── ProgramController.php
│   │   ├── SubjectController.php
│   │   └── EnrollmentController.php
│   │
│   ├── Services/
│   │   ├── StudentService.php
│   │   ├── ProgramService.php
│   │   ├── SubjectService.php
│   │   └── EnrollmentService.php
│   │
│   └── Core/
│       └── Database.php
│
├── config/
│   └── database.php
│
├── students/
│   ├── index.php
│   ├── create.php
│   └── edit.php
│
├── programs/
│   ├── index.php
│   ├── create.php
│   └── edit.php
│
├── subjects/
│   ├── index.php
│   ├── create.php
│   └── edit.php
│
├── enrollment/
│   ├── index.php
│   └── create.php
│
├── api/
│   ├── students/
│   ├── programs/
│   ├── subjects/
│   └── enrollment/
│
├── assets/
│   ├── css/
│   └── js/
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── sidebar.php
│
└── bootstrap.php
```

This structure can evolve as the system grows. Do not add folders or architectural layers unless they solve a real problem.

---

# 3. Responsibilities of Each Layer

## 3.1 PHP Pages

The PHP pages are the direct entry points of the system.

Examples:

```text
/students/index.php
/students/create.php
/students/edit.php
```

A maintainer should be able to locate a page directly from its URL.

Example flow:

```text
/students/index.php
        ↓
StudentController::index()
        ↓
StudentService::getAll()
```

Pages should remain simple. They should not contain SQL queries or major business logic.

---

## 3.2 Controllers

Controllers coordinate requests.

Controllers should:

- Receive input from forms, query parameters, or requests.
- Call the appropriate service.
- Pass data to a page or view.
- Return JSON for API requests.
- Redirect after successful actions.

Controllers should not contain:

- SQL queries.
- Complex business rules.
- Large amounts of processing logic.

Example:

```php
class StudentController
{
    private StudentService $studentService;

    public function __construct()
    {
        $this->studentService = new StudentService();
    }

    public function index(): array
    {
        return $this->studentService->getAll();
    }
}
```

The goal is to keep controllers easy to read.

---

## 3.3 Services

Services contain the application's business logic.

Examples:

```text
StudentService
ProgramService
SubjectService
EnrollmentService
UserService
```

A service may handle:

- Validation.
- Duplicate checking.
- Business rules.
- Enrollment workflows.
- Database transactions.
- Creating, updating, and retrieving records.

Example:

```php
class StudentService
{
    public function getAll(): array
    {
        $db = Database::connection();

        $statement = $db->query(
            'SELECT * FROM students ORDER BY last_name'
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

For a simple project, services may communicate directly with PDO.

---

## 3.4 Database and PDO

The system should use one reusable database connection.

Example responsibility:

```text
Database.php
    ↓
Creates and returns the PDO connection
```

Do not create a new PDO configuration inside every service.

Always use prepared statements when handling external input.

Example:

```php
$statement = $db->prepare(
    'SELECT * FROM students WHERE id = :id'
);

$statement->execute([
    'id' => $studentId
]);
```

Avoid:

```php
$sql = "SELECT * FROM students WHERE id = $studentId";
```

---

## 3.5 Views and HTML

Views are responsible for presentation.

They should:

- Display data.
- Render forms.
- Render tables.
- Include reusable layout components.

Views should not:

- Execute SQL.
- Contain complex business rules.
- Perform database operations.

Example:

```php
<?php foreach ($students as $student): ?>
    <tr>
        <td>
            <?= htmlspecialchars($student['student_number']) ?>
        </td>
    </tr>
<?php endforeach; ?>
```

Use `htmlspecialchars()` when displaying dynamic user or database content.

---

# 4. Development Flow for Each Feature

When developing a new feature, follow this sequence.

## Step 1: Define the Feature

Example:

```text
Feature: Student Management

Required actions:
- View students
- Search students
- Create student
- Edit student
- Archive student
```

Before coding, identify the user actions and required database operations.

---

## Step 2: Create the Service

Start with the application's logic.

Example:

```text
app/Services/StudentService.php
```

Methods may include:

```php
getAll()
getById()
create()
update()
archive()
search()
```

This keeps student-related logic in one predictable location.

---

## Step 3: Create the Controller

Create:

```text
app/Controllers/StudentController.php
```

The controller calls the service and coordinates the result.

---

## Step 4: Create the Pages

Create pages based on actual user actions:

```text
students/
    index.php
    create.php
    edit.php
```

The naming should be obvious to the next developer.

---

## Step 5: Add Asynchronous Features When Needed

Do not use Fetch API for everything.

Use it when asynchronous behavior improves the user experience.

Good examples:

- Search without reloading the page.
- Dependent dropdowns.
- Live filtering.
- Loading available subjects.
- Checking duplicates.
- Updating small sections of a page.

Example:

```text
JavaScript
    ↓
fetch()
    ↓
/api/programs/get-by-department.php
    ↓
ProgramController
    ↓
ProgramService
    ↓
PDO / MySQL
    ↓
JSON
```

Keep API files thin. They should only initialize the request and call the controller.

---

# 5. Fetch API Convention

A JavaScript request:

```javascript
const response = await fetch(
    '/api/programs/get-by-department.php?department_id=1'
);

const data = await response.json();
```

The endpoint:

```text
api/programs/get-by-department.php
```

should:

1. Load the required application files.
2. Call the controller.
3. Return JSON.

Example response format:

```json
{
    "success": true,
    "data": []
}
```

For errors:

```json
{
    "success": false,
    "message": "Unable to load programs."
}
```

Use a consistent JSON structure throughout the system.

---

# 6. Naming Conventions

Use clear and predictable names.

## Classes

```text
StudentController
StudentService
EnrollmentController
EnrollmentService
```

## Files

```text
StudentController.php
StudentService.php
```

## Pages

Use simple action names:

```text
index.php
create.php
edit.php
view.php
```

Avoid unclear names such as:

```text
student2.php
newpage.php
process1.php
final.php
```

## Service Methods

Use descriptive verbs:

```php
getAll()
getById()
create()
update()
archive()
search()
enrollStudent()
```

---

# 7. Form Handling Flow

For a normal form submission:

```text
User submits form
    ↓
create.php or processing PHP entry point
    ↓
Controller
    ↓
Service validates and processes data
    ↓
PDO / MySQL
    ↓
Success or failure result
    ↓
Redirect or display errors
```

Example responsibilities:

```text
Controller
    → Receives submitted data.

Service
    → Validates business rules.
    → Checks duplicates.
    → Saves data.

PDO
    → Executes database queries.
```

---

# 8. Enrollment Operations

The enrollment process may involve multiple database operations.

Example:

```text
Create Enrollment
    ↓
Check student eligibility
    ↓
Check duplicate enrollment
    ↓
Retrieve required subjects
    ↓
Create enrollment record
    ↓
Create enrollment subject records
    ↓
Commit transaction
```

When multiple database changes must either all succeed or all fail, use a PDO transaction.

Example:

```php
$db->beginTransaction();

try {
    // Multiple database operations.

    $db->commit();
} catch (Throwable $exception) {
    $db->rollBack();

    throw $exception;
}
```

This is especially important for critical processes such as enrollment.

---

# 9. Reusable Files

Use reusable files for common functionality.

Examples:

```text
bootstrap.php
app/Core/Database.php
includes/header.php
includes/footer.php
includes/sidebar.php
```

## bootstrap.php

The bootstrap file can centralize common initialization.

Examples:

- Start the session.
- Load configuration.
- Load required classes.
- Load helper functions.

This prevents repeated setup code across pages.

Keep it simple. It should not become a custom framework.

---

# 10. What We Are Intentionally Not Using

The project does not need to recreate Laravel.

Do not add these unless a real requirement appears:

- Custom router.
- Repository layer.
- DTO layer.
- Dependency injection container.
- Custom middleware framework.
- Events and observers.
- Factories.
- Facades.
- A custom ORM.

The rule is:

> Add complexity only when it solves an actual problem.

---

# 11. Code Quality Rules

Follow these rules throughout the project:

1. Do not write SQL inside pages or views.
2. Keep controllers focused on handling requests.
3. Keep business logic inside services.
4. Use PDO prepared statements.
5. Escape dynamic HTML output.
6. Use clear and descriptive names.
7. Keep related code together.
8. Avoid duplicate business logic.
9. Use transactions for critical multi-step operations.
10. Keep API endpoints thin.
11. Keep comments focused on explaining non-obvious decisions.
12. Prefer simple code that the next PHP developer can understand.

---

# 12. Recommended Feature Template

When adding a new module, use this pattern.

Example: Programs

```text
app/
├── Controllers/
│   └── ProgramController.php
│
└── Services/
    └── ProgramService.php

programs/
├── index.php
├── create.php
└── edit.php

api/
└── programs/
    └── search.php
```

Flow:

```text
Page
    ↓
ProgramController
    ↓
ProgramService
    ↓
PDO
    ↓
MySQL
```

Async flow:

```text
Fetch API
    ↓
api/programs/search.php
    ↓
ProgramController
    ↓
ProgramService
    ↓
PDO
    ↓
JSON
```

---

# 13. Final Development Principle

The system should be organized enough to scale, but simple enough for another PHP developer to maintain.

The goal is not to imitate Laravel.

The goal is to make the codebase predictable.

A developer should be able to ask:

```text
Where is the page?
→ Check the module folder.

Where is the request handling?
→ Check the Controller.

Where is the business logic?
→ Check the Service.

Where is the database access?
→ Check the Service and Database connection.

Where is the asynchronous endpoint?
→ Check the api/ folder.
```

If the structure remains consistent, the system will be easier to maintain even without Laravel.
