<?php
require_once __DIR__ . '/../../bootstrap.php';

use App\Controllers\AuthController;

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->teacherLogin();
} else {
    $controller->teacherLoginForm();
}
