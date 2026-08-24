# Unciano Enrollment Management System

A university enrollment management system built with **organized Vanilla PHP**, MySQL, Tailwind CSS, and Fetch API.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Vanilla PHP |
| Database | MySQL (PDO) |
| Frontend | Tailwind CSS v4 |
| JavaScript | Vanilla JS + Fetch API |

## Project Structure

```
unciano/
├── app/                # Application logic
│   ├── Controllers/    # Handle HTTP requests
│   ├── Services/       # Business logic
│   ├── Core/           # Database connection
│   └── Helpers/        # Utility functions
│
├── views/              # Page templates
│   ├── admin/          # System admin portal
│   ├── registrar/      # Registrar portal
│   ├── admission/      # Admissions portal
│   ├── department/     # Department head portal
│   ├── accounting/     # Accounting/cashier portal
│   ├── student/        # Student self-service portal
│   ├── teacher/        # Teacher portal
│   ├── login/          # Login pages
│   └── application/    # Public application form
│
├── layouts/            # Shared layout templates
├── api/                # JSON API endpoints (AJAX)
├── assets/             # Static CSS, JS, images
├── config/             # Database configuration
├── public/             # Web root (compiled CSS)
├── src/                # Source files (Tailwind input)
└── bootstrap.php       # App initialization
```

## Architecture

```
PHP Page → Controller → Service → PDO → MySQL
```

## Getting Started

```bash
# Install dependencies
npm install

# Build CSS (dev mode with watch)
npm run dev

# Build CSS (production)
npm run build
```

## Database

Import `uca-nexus.sql` into MySQL to set up the schema.
Import `uca-nexus-data.sql` into MySQL to set up the schema.

## Root Folder

Change the root folder in `portal.php` and `function.php`

## Development Guide

See `enrollment-system-development-guide.md` for full conventions and rules.
