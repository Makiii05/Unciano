<?php

namespace App\Controllers;

use App\Services\AccountService;
use App\Services\DepartmentService;

class AccountController
{
    private AccountService $accountService;
    private DepartmentService $departmentService;

    private const ALLOWED_OFFICE_TYPES = ['admin', 'registrar', 'accounting', 'admission', 'department'];
    private const ALLOWED_ROLES = ['head', 'proctor', 'interviewer', 'guidance', 'principal', 'secretary'];

    public function __construct()
    {
        $this->accountService = new AccountService();
        $this->departmentService = new DepartmentService();
    }

    public function index(): void
    {
        ensureAdmin();

        $pageTitle = 'Accounts Management';
        $pageSubheader = 'Manage all user accounts';

        $users = $this->accountService->getUsers();
        $teacherAccounts = $this->accountService->getTeacherAccounts();
        $studentAccounts = $this->accountService->getStudentAccounts();
        $departments = $this->departmentService->getForDropdown();

        ob_start();
        require __DIR__ . '/../../views/admin/accounts/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    // ---- Faculty create ----

    public function storeUser(): void
    {
        ensureAdmin();

        if (!validate_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        $type = trim($_POST['type'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $departmentId = $_POST['department_id'] ?? null;
        $departmentId = $departmentId !== '' && $departmentId !== null ? (int) $departmentId : null;

        $errors = $this->validateStoreUser($name, $email, $password, $passwordConfirmation, $type, $role, $departmentId);

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/admin/accounts/index.php'));
        }

        $this->accountService->createUser([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'type' => $type,
            'role' => $role ?: null,
            'department_id' => $departmentId,
            'status' => 'active',
        ]);

        unset($_SESSION['old']);
        flash('success', 'Faculty account created successfully.');
        redirect(url('views/admin/accounts/index.php'));
    }

    private function validateStoreUser(string $name, string $email, string $password, string $passwordConfirmation, string $type, string $role, ?int $departmentId): array
    {
        $errors = [];

        if ($name === '') {
            $errors[] = 'Name is required.';
        } elseif (mb_strlen($name) > 255) {
            $errors[] = 'Name may not be greater than 255 characters.';
        }

        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email must be a valid email address.';
        } elseif (mb_strlen($email) > 255) {
            $errors[] = 'Email may not be greater than 255 characters.';
        } elseif ($this->accountService->emailExists($email)) {
            $errors[] = 'Email has already been taken.';
        }

        if ($password === '') {
            $errors[] = 'Password is required.';
        } elseif (mb_strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif ($password !== $passwordConfirmation) {
            $errors[] = 'Password confirmation does not match.';
        }

        if ($type === '') {
            $errors[] = 'Office type is required.';
        } elseif (!in_array($type, self::ALLOWED_OFFICE_TYPES, true)) {
            $errors[] = 'Invalid office type.';
        }

        if ($type === 'department' && $departmentId === null) {
            $errors[] = 'Department is required when office is Department.';
        }

        if ($departmentId !== null) {
            $dept = $this->departmentService->getById($departmentId);
            if (!$dept) {
                $errors[] = 'Selected department is invalid.';
            }
        }

        // Role optional in vanilla (Laravel had required); keep optional to avoid breaking existing data
        if ($role !== '' && !in_array($role, self::ALLOWED_ROLES, true)) {
            $errors[] = 'Invalid role.';
        }

        return $errors;
    }

    // ---- Faculty update ----

    public function updateUser(): void
    {
        ensureAdmin();

        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $userId = (int) ($_POST['user_id'] ?? $_GET['id'] ?? 0);
        if ($userId <= 0) {
            flash('error', 'Invalid user.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $user = $this->accountService->getUserById($userId);
        if (!$user) {
            flash('error', 'User not found.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 255) {
            $errors[] = 'Name is required and may not exceed 255 characters.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
            $errors[] = 'Valid email is required (max 255).';
        } elseif ($this->accountService->emailExists($email, $userId)) {
            $errors[] = 'Email has already been taken.';
        }
        if (!in_array($status, ['active', 'inactive'], true)) {
            $errors[] = 'Invalid status.';
        }

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/admin/accounts/index.php'));
        }

        $this->accountService->updateUser($userId, [
            'name' => $name,
            'email' => $email,
            'status' => $status,
        ]);

        flash('success', 'User account updated successfully.');
        redirect(url('views/admin/accounts/index.php'));
    }

    // ---- Faculty delete ----

    public function destroyUser(): void
    {
        ensureAdmin();

        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $userId = (int) ($_POST['user_id'] ?? $_POST['account_id'] ?? $_POST['id'] ?? 0);
        if ($userId <= 0) {
            flash('error', 'Invalid user.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $user = $this->accountService->getUserById($userId);
        if (!$user) {
            flash('error', 'User not found.');
            redirect(url('views/admin/accounts/index.php'));
        }

        // Prevent deleting own account
        $currentUser = auth();
        if ($currentUser && (int) $currentUser['id'] === $userId) {
            flash('error', 'You cannot delete your own account.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $this->accountService->deleteUser($userId);
        flash('success', 'User account deleted successfully.');
        redirect(url('views/admin/accounts/index.php'));
    }

    // ---- Teacher account update ----

    public function updateTeacherAccount(): void
    {
        ensureAdmin();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $id = (int) ($_POST['teacher_account_id'] ?? $_POST['account_id'] ?? $_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if ($id <= 0 || !$this->accountService->getTeacherAccountById($id)) {
            flash('error', 'Teacher account not found.');
            redirect(url('views/admin/accounts/index.php'));
        }

        if (!in_array($status, ['open', 'close'], true)) {
            flash('error', 'Invalid status. Use open or close.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $this->accountService->updateTeacherAccount($id, ['status' => $status]);
        flash('success', 'Teacher account updated successfully.');
        redirect(url('views/admin/accounts/index.php'));
    }

    public function destroyTeacherAccount(): void
    {
        ensureAdmin();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/admin/accounts/index.php'));
        }
        $id = (int) ($_POST['teacher_account_id'] ?? $_POST['account_id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0 || !$this->accountService->getTeacherAccountById($id)) {
            flash('error', 'Teacher account not found.');
            redirect(url('views/admin/accounts/index.php'));
        }
        $this->accountService->deleteTeacherAccount($id);
        flash('success', 'Teacher account deleted successfully.');
        redirect(url('views/admin/accounts/index.php'));
    }

    // ---- Student account update ----

    public function updateStudentAccount(): void
    {
        ensureAdmin();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/admin/accounts/index.php'));
        }
        $id = (int) ($_POST['student_account_id'] ?? $_POST['account_id'] ?? $_POST['id'] ?? 0);
        $accountStatus = trim($_POST['account_status'] ?? '');
        if ($id <= 0 || !$this->accountService->getStudentAccountById($id)) {
            flash('error', 'Student account not found.');
            redirect(url('views/admin/accounts/index.php'));
        }
        // Laravel enum AccountStatus: active|inactive|off but DB default is 'off'/'on' for students (see student_accounts.account_status)
        // Accept both to be tolerant: on|off|active|inactive
        if (!in_array($accountStatus, ['on', 'off', 'active', 'inactive'], true)) {
            flash('error', 'Invalid account status.');
            redirect(url('views/admin/accounts/index.php'));
        }
        $this->accountService->updateStudentAccount($id, ['account_status' => $accountStatus]);
        flash('success', 'Student account updated successfully.');
        redirect(url('views/admin/accounts/index.php'));
    }

    public function destroyStudentAccount(): void
    {
        ensureAdmin();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/admin/accounts/index.php'));
        }
        $id = (int) ($_POST['student_account_id'] ?? $_POST['account_id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0 || !$this->accountService->getStudentAccountById($id)) {
            flash('error', 'Student account not found.');
            redirect(url('views/admin/accounts/index.php'));
        }
        $this->accountService->deleteStudentAccount($id);
        flash('success', 'Student account deleted successfully.');
        redirect(url('views/admin/accounts/index.php'));
    }

    // ---- Password changes ----

    public function changeUserPassword(): void
    {
        $this->changePassword('user');
    }

    public function changeTeacherPassword(): void
    {
        $this->changePassword('teacher');
    }

    public function changeStudentPassword(): void
    {
        $this->changePassword('student');
    }

    private function changePassword(string $type): void
    {
        ensureAdmin();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $id = (int) ($_POST['account_id'] ?? $_POST['user_id'] ?? $_POST['teacher_account_id'] ?? $_POST['student_account_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirmation'] ?? '';

        if ($id <= 0) {
            flash('error', 'Invalid account.');
            redirect(url('views/admin/accounts/index.php'));
        }

        $errors = [];
        if (mb_strlen($newPassword) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($newPassword !== $confirm) {
            $errors[] = 'Password confirmation does not match.';
        }
        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/admin/accounts/index.php'));
        }

        if ($type === 'user') {
            if (!$this->accountService->getUserById($id)) {
                flash('error', 'User not found.');
                redirect(url('views/admin/accounts/index.php'));
            }
            $this->accountService->changeUserPassword($id, $newPassword);
        } elseif ($type === 'teacher') {
            if (!$this->accountService->getTeacherAccountById($id)) {
                flash('error', 'Teacher account not found.');
                redirect(url('views/admin/accounts/index.php'));
            }
            $this->accountService->changeTeacherPassword($id, $newPassword);
        } else {
            if (!$this->accountService->getStudentAccountById($id)) {
                flash('error', 'Student account not found.');
                redirect(url('views/admin/accounts/index.php'));
            }
            $this->accountService->changeStudentPassword($id, $newPassword);
        }

        flash('success', 'Password changed successfully.');
        redirect(url('views/admin/accounts/index.php'));
    }
}
