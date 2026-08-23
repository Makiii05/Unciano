<?php

namespace App\Controllers;

use App\Services\DepartmentService;

class DepartmentController
{
    private DepartmentService $departmentService;

    private const ALLOWED_STATUSES = ['active', 'inactive'];
    private const ALLOWED_EDUCATION_LEVELS = ['college', 'K12', 'k12'];

    public function __construct()
    {
        $this->departmentService = new DepartmentService();
    }

    public function index(): void
    {
        ensureRegistrar();

        $pageTitle = 'Departments';
        $pageSubheader = 'Manage academic departments';

        $departments = $this->departmentService->getAll();

        ob_start();
        require __DIR__ . '/../../views/registrar/departments/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function store(): void
    {
        ensureRegistrar();

        if (!validate_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect(url('views/registrar/departments/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $educationLevel = trim($_POST['education_level'] ?? '');
        $status = trim($_POST['status'] ?? '');

        // Normalize education_level: allow empty -> null -> default college
        if ($educationLevel === '') {
            $educationLevel = 'college';
        }

        $errors = $this->validate($code, $description, $educationLevel, $status);

        if ($this->departmentService->codeExists($code)) {
            $errors[] = 'Code has already been taken.';
        }

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/departments/index.php'));
        }

        $this->departmentService->create([
            'code' => $code,
            'description' => $description,
            'education_level' => $educationLevel,
            'status' => $status,
        ]);

        unset($_SESSION['old']);
        flash('success', 'Department created successfully.');
        redirect(url('views/registrar/departments/index.php'));
    }

    public function update(): void
    {
        ensureRegistrar();

        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/departments/index.php'));
        }

        $id = (int) ($_POST['department_id'] ?? $_POST['id'] ?? 0);
        $department = $this->departmentService->getById($id);
        if (!$department) {
            flash('error', 'Department not found.');
            redirect(url('views/registrar/departments/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $educationLevel = trim($_POST['education_level'] ?? '');
        $status = trim($_POST['status'] ?? '');
        if ($educationLevel === '') {
            $educationLevel = 'college';
        }

        $errors = $this->validate($code, $description, $educationLevel, $status);

        if ($this->departmentService->codeExists($code, $id)) {
            $errors[] = 'Code has already been taken.';
        }

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/departments/index.php'));
        }

        $this->departmentService->update($id, [
            'code' => $code,
            'description' => $description,
            'education_level' => $educationLevel,
            'status' => $status,
        ]);

        flash('success', 'Department updated successfully.');
        redirect(url('views/registrar/departments/index.php'));
    }

    public function destroy(): void
    {
        ensureRegistrar();

        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/departments/index.php'));
        }

        $id = (int) ($_POST['department_id'] ?? $_POST['id'] ?? $_POST['account_id'] ?? 0);
        $department = $this->departmentService->getById($id);
        if (!$department) {
            flash('error', 'Department not found.');
            redirect(url('views/registrar/departments/index.php'));
        }

        $dependents = $this->departmentService->hasDependents($id);
        if (!empty($dependents)) {
            flash('error', 'Cannot delete department. It is still referenced by: ' . implode(', ', $dependents) . '.');
            redirect(url('views/registrar/departments/index.php'));
        }

        $this->departmentService->delete($id);
        flash('success', 'Department deleted successfully.');
        redirect(url('views/registrar/departments/index.php'));
    }

    private function validate(string $code, string $description, string $educationLevel, string $status): array
    {
        $errors = [];
        if ($code === '' || mb_strlen($code) > 255) {
            $errors[] = 'Code is required and may not exceed 255 characters.';
        }
        if ($description === '' || mb_strlen($description) > 255) {
            $errors[] = 'Description is required and may not exceed 255 characters.';
        }
        // Normalize K12 case-insensitive
        $normLevel = strtolower($educationLevel) === 'k12' ? 'K12' : $educationLevel;
        if ($normLevel !== '' && !in_array($normLevel, ['college', 'K12'], true) && !in_array($educationLevel, self::ALLOWED_EDUCATION_LEVELS, true)) {
            // Allow 'college' and 'K12' only; original enum is College='college', K12='K12'
            $errors[] = 'Invalid education level.';
        }
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $errors[] = 'Invalid status. Use active or inactive.';
        }
        return $errors;
    }
}
