<?php

namespace App\Controllers;

use App\Services\DepartmentService;
use App\Services\ProgramService;

class ProgramController
{
    private ProgramService $programService;
    private DepartmentService $departmentService;

    private const ALLOWED_STATUSES = ['active', 'inactive'];

    public function __construct()
    {
        $this->programService = new ProgramService();
        $this->departmentService = new DepartmentService();
    }

    public function index(): void
    {
        ensureRegistrar();
        $pageTitle = 'Programs';
        $pageSubheader = 'Manage academic programs';
        $programs = $this->programService->getAll();
        $departments = $this->departmentService->getForDropdown();

        ob_start();
        require __DIR__ . '/../../views/registrar/programs/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/programs/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $departmentId = (int) ($_POST['department_id'] ?? 0);

        $errors = $this->validate($code, $description, $status, $departmentId);
        if ($this->programService->codeExists($code)) {
            $errors[] = 'Code has already been taken.';
        }

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/programs/index.php'));
        }

        $this->programService->create([
            'code' => $code,
            'description' => $description,
            'status' => $status,
            'department_id' => $departmentId,
        ]);

        unset($_SESSION['old']);
        flash('success', 'Program created successfully.');
        redirect(url('views/registrar/programs/index.php'));
    }

    public function update(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/programs/index.php'));
        }

        $id = (int) ($_POST['program_id'] ?? $_POST['id'] ?? 0);
        $row = $this->programService->getById($id);
        if (!$row) {
            flash('error', 'Program not found.');
            redirect(url('views/registrar/programs/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $departmentId = (int) ($_POST['department_id'] ?? 0);

        $errors = $this->validate($code, $description, $status, $departmentId);
        if ($this->programService->codeExists($code, $id)) {
            $errors[] = 'Code has already been taken.';
        }

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/programs/index.php'));
        }

        $this->programService->update($id, [
            'code' => $code,
            'description' => $description,
            'status' => $status,
            'department_id' => $departmentId,
        ]);

        flash('success', 'Program updated successfully.');
        redirect(url('views/registrar/programs/index.php'));
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/programs/index.php'));
        }

        $id = (int) ($_POST['program_id'] ?? $_POST['id'] ?? 0);
        $row = $this->programService->getById($id);
        if (!$row) {
            flash('error', 'Program not found.');
            redirect(url('views/registrar/programs/index.php'));
        }

        $dependents = $this->programService->hasDependents($id);
        if (!empty($dependents)) {
            flash('error', 'Cannot delete program. It is still referenced by: ' . implode(', ', $dependents) . '.');
            redirect(url('views/registrar/programs/index.php'));
        }

        $this->programService->delete($id);
        flash('success', 'Program deleted successfully.');
        redirect(url('views/registrar/programs/index.php'));
    }

    private function validate(string $code, string $description, string $status, int $departmentId): array
    {
        $errors = [];
        if ($code === '' || mb_strlen($code) > 255) {
            $errors[] = 'Code is required and may not exceed 255 characters.';
        }
        if ($description === '' || mb_strlen($description) > 255) {
            $errors[] = 'Description is required and may not exceed 255 characters.';
        }
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $errors[] = 'Invalid status.';
        }
        if ($departmentId <= 0) {
            $errors[] = 'Department is required.';
        } else {
            $dept = $this->departmentService->getById($departmentId);
            if (!$dept) {
                $errors[] = 'Selected department is invalid.';
            }
        }
        return $errors;
    }
}
