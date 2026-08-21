# Helpers/

Reusable utility functions loaded on every request via `bootstrap.php`.

## Expected Functions

- `url($path)` — Generate URL path
- `e($value)` — Escape HTML output (`htmlspecialchars`)
- `old($key, $default)` — Retrieve old form input from session
- `redirect($url)` — Redirect to a URL
- `auth()` — Get authenticated user from session
- `flash($type, $message)` — Set flash message in session
- `csrf_field()` — Output CSRF hidden input
    