<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    private const ALLOWED_TYPES = ['admin', 'registrar', 'accounting', 'admission', 'department'];

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function staffLoginForm(string $type): void
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            redirect(url('views/login/index.php'));
        }

        $title = ucfirst($type) . ' Login';
        $typeLabel = ucfirst($type);
        $formAction = url("views/login/staff.php?type=" . urlencode($type));
        $backUrl = url('views/login/index.php');

        require __DIR__ . '/../../views/layouts/auth.php';
    }

    public function studentLoginForm(): void
    {
        $title = 'Student Login';
        $formAction = url('views/login/student.php');
        $backUrl = url('views/login/index.php');

        require __DIR__ . '/../../views/layouts/auth.php';
    }

    public function teacherLoginForm(): void
    {
        $title = 'Teacher Login';
        $formAction = url('views/login/teacher.php');
        $backUrl = url('views/login/index.php');

        require __DIR__ . '/../../views/layouts/auth.php';
    }

    public function staffLogin(): void
    {
        $type = $_GET['type'] ?? '';
        if (!in_array($type, self::ALLOWED_TYPES)) {
            redirect(url('views/login/index.php'));
        }

        if (!validate_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect(url("views/login/staff.php?type=" . urlencode($type)));
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            flash('error', 'Email and password are required.');
            redirect(url("views/login/staff.php?type=" . urlencode($type)));
        }

        $user = $this->authService->loginStaff($email, $password, $type);

        if (!$user) {
            flash('error', 'The provided credentials do not match our records.');
            redirect(url("views/login/staff.php?type=" . urlencode($type)));
        }

        $_SESSION['staff_id'] = $user['id'];
        $_SESSION['staff_type'] = $user['type'];
        session_regenerate_id(true);

        redirect(url('views/dashboard.php'));
    }

    public function studentLogin(): void
    {
        if (!validate_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect(url('views/login/student.php'));
        }

        $studentNumber = trim($_POST['student_number'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($studentNumber) || empty($password)) {
            flash('error', 'Student number and password are required.');
            redirect(url('views/login/student.php'));
        }

        $result = $this->authService->loginStudent($studentNumber, $password);

        if (!$result) {
            flash('error', 'The provided credentials do not match our records.');
            redirect(url('views/login/student.php'));
        }

        if (isset($result['_disabled'])) {
            flash('error', 'Your account is disabled. Please contact the accounting office.');
            redirect(url('views/login/student.php'));
        }

        $_SESSION['student_id'] = $result['account_id'];
        session_regenerate_id(true);

        redirect(url('views/student/index.php'));
    }

    public function teacherLogin(): void
    {
        if (!validate_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect(url('views/login/teacher.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($code) || empty($password)) {
            flash('error', 'Teacher code and password are required.');
            redirect(url('views/login/teacher.php'));
        }

        $result = $this->authService->loginTeacher($code, $password);

        if (!$result) {
            flash('error', 'The provided credentials do not match our records.');
            redirect(url('views/login/teacher.php'));
        }

        if (isset($result['_disabled'])) {
            flash('error', 'Your account is closed. Please contact the administrator.');
            redirect(url('views/login/teacher.php'));
        }

        $_SESSION['teacher_id'] = $result['account_id'];
        session_regenerate_id(true);

        redirect(url('views/teacher/index.php'));
    }

    public function logout(): void
    {
        $this->authService->logout();
        redirect(url('views/login/index.php'));
    }
}
