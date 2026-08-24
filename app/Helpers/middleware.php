<?php

// Middleware helpers for role-based authorization.
// Requires app/Helpers/functions.php to be loaded first (url, flash, redirect, require_auth).
// All functions are global (no namespace) to match functions.php style.
// CamelCase naming per project preference.

if (!function_exists('ensureAdmin')) {
    /**
     * Ensure current user is admin. Otherwise redirect to dashboard.
     */
    function ensureAdmin(string $redirectTo = 'views/dashboard.php'): void
    {
        $user = require_auth();
        if (($user['type'] ?? '') !== 'admin') {
            flash('error', 'Unauthorized access.');
            redirect(url($redirectTo));
        }
    }
}

if (!function_exists('ensureRegistrar')) {
    /**
     * Ensure current user is registrar (strict, admin not allowed).
     */
    function ensureRegistrar(string $redirectTo = 'views/dashboard.php'): void
    {
        $user = require_auth();
        $type = $user['type'] ?? '';
        if ($type !== 'registrar') {
            flash('error', 'Unauthorized access.');
            redirect(url($redirectTo));
        }
    }
}

if (!function_exists('ensureRole')) {
    /**
     * Ensure current user has one of the allowed roles.
     *
     * @param string[] $roles
     */
    function ensureRole(array $roles, string $redirectTo = 'views/dashboard.php'): void
    {
        $user = require_auth();
        $type = $user['type'] ?? '';
        if (!in_array($type, $roles, true)) {
            flash('error', 'Unauthorized access.');
            redirect(url($redirectTo));
        }
    }
}

if (!function_exists('ensureDepartment')) {
    /**
     * Ensure current user is department (strict, must have department_id).
     * Returns JSON 403 for AJAX/API requests.
     */
    function ensureDepartment(string $redirectTo = 'views/dashboard.php'): void
    {
        $user = require_auth();
        $type = $user['type'] ?? '';
        $deptId = $user['department_id'] ?? null;
        if ($type !== 'department' || $deptId === null) {
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $wantsJson = str_contains($accept, 'application/json') || isset($_SERVER['HTTP_X_REQUESTED_WITH']) || str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');
            if ($wantsJson) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['message' => 'Forbidden: Department access required.']);
                exit;
            }
            flash('error', 'Unauthorized access. Department assignment required.');
            redirect(url($redirectTo));
        }
    }
}

if (!function_exists('ensureTeacher')) {
    function ensureTeacher(string $redirectTo = 'views/login/teacher.php'): void
    {
        $account = require_auth('teacher');
        if (!$account) {
            flash('error', 'Please login as teacher.');
            redirect(url($redirectTo));
        }
    }
}

if (!function_exists('ensureStudent')) {
    function ensureStudent(string $redirectTo = 'views/login/student.php'): void
    {
        $account = require_auth('student');
        if (!$account) {
            flash('error', 'Please login as student.');
            redirect(url($redirectTo));
        }
    }
}
