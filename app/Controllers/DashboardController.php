<?php

namespace App\Controllers;

class DashboardController
{
    public function index(): void
    {
        $user = require_auth();

        $type = $user['type'] ?? ($_SESSION['student_id'] ? 'student' : ($_SESSION['teacher_id'] ? 'teacher' : ''));

        $pageTitle = 'Dashboard';

        ob_start();
        require __DIR__ . '/../../views/' . $type . '/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../views/layouts/portal.php';
    }
}
