<?php

function url(string $path = ''): string
{
    $base = '/unciano';
    $path = ltrim($path, '/');
    return $base . ($path ? '/' . $path : '');
}

function e(mixed $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['old'][$key] ?? $default;
}

function redirect(string $url): never
{
    header("Location: $url");
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function validate_csrf(): bool
{
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals(csrf_token(), $token);
}

function auth(string $guard = 'web'): ?array
{
    $db = \App\Core\Database::connection();

    if ($guard === 'web') {
        $userId = $_SESSION['staff_id'] ?? null;
        if (!$userId) {
            return null;
        }
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ? AND status = ?');
        $stmt->execute([$userId, 'active']);
        return $stmt->fetch() ?: null;
    }

    if ($guard === 'student') {
        $accountId = $_SESSION['student_id'] ?? null;
        if (!$accountId) {
            return null;
        }
        $stmt = $db->prepare(
            'SELECT sa.*, s.student_number, s.first_name, s.last_name, s.middle_name,
                    s.department_id, s.program_id, s.level_id, s.sex, s.status as student_status
             FROM student_accounts sa
             JOIN students s ON s.id = sa.student_id
             WHERE sa.id = ? AND sa.account_status = ?'
        );
        $stmt->execute([$accountId, 'on']);
        return $stmt->fetch() ?: null;
    }

    if ($guard === 'teacher') {
        $accountId = $_SESSION['teacher_id'] ?? null;
        if (!$accountId) {
            return null;
        }
        $stmt = $db->prepare(
            'SELECT ta.*, t.code, t.first_name, t.last_name, t.middle_name, t.email
             FROM teacher_accounts ta
             JOIN teachers t ON t.id = ta.teacher_id
             WHERE ta.id = ? AND ta.status = ?'
        );
        $stmt->execute([$accountId, 'open']);
        return $stmt->fetch() ?: null;
    }

    return null;
}

function auth_check(string $guard = 'web'): bool
{
    return auth($guard) !== null;
}

function require_auth(string $guard = 'web'): array
{
    $user = auth($guard);
    if (!$user) {
        if ($guard === 'student') {
            redirect(url('views/login/student.php'));
        } elseif ($guard === 'teacher') {
            redirect(url('views/login/teacher.php'));
        } else {
            redirect(url('views/login/staff.php'));
        }
    }
    return $user;
}

function dd(mixed $var): never
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    exit;
}
