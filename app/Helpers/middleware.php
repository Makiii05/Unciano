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
