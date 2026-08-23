<?php

namespace App\Controllers;

use App\Services\ComponentService;
use App\Services\DepartmentService;

class ComponentController
{
    private ComponentService $componentService;
    private DepartmentService $departmentService;

    public function __construct()
    {
        $this->componentService = new ComponentService();
        $this->departmentService = new DepartmentService();
    }

    public function index(): void
    {
        ensureRegistrar();

        $pageTitle = 'Grading Components';
        $pageSubheader = 'Manage grading component definitions';

        $components = $this->componentService->getAll();
        $departments = $this->departmentService->getForDropdown();

        ob_start();
        require __DIR__ . '/../../views/registrar/grading-components/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/grading-components/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $percentage = trim($_POST['percentage'] ?? '');
        $departmentId = (int) ($_POST['department_id'] ?? 0);

        $errors = $this->validate($code, $description, $percentage, $departmentId);

        if ($this->componentService->codeExists($code, $departmentId)) {
            $errors[] = 'Code already exists for this department.';
        }

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/grading-components/index.php'));
        }

        $this->componentService->create([
            'code' => $code,
            'description' => $description,
            'percentage' => (float) $percentage,
            'department_id' => $departmentId,
        ]);

        unset($_SESSION['old']);
        flash('success', 'Grading component created successfully.');
        redirect(url('views/registrar/grading-components/index.php'));
    }

    public function update(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/grading-components/index.php'));
        }

        $id = (int) ($_POST['component_id'] ?? $_POST['id'] ?? 0);
        $row = $this->componentService->getById($id);
        if (!$row) {
            flash('error', 'Component not found.');
            redirect(url('views/registrar/grading-components/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $percentage = trim($_POST['percentage'] ?? '');
        $departmentId = (int) ($_POST['department_id'] ?? 0);

        $errors = $this->validate($code, $description, $percentage, $departmentId);

        if ($this->componentService->codeExists($code, $departmentId, $id)) {
            $errors[] = 'Code already exists for this department.';
        }

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/grading-components/index.php'));
        }

        $this->componentService->update($id, [
            'code' => $code,
            'description' => $description,
            'percentage' => (float) $percentage,
            'department_id' => $departmentId,
        ]);

        flash('success', 'Grading component updated successfully.');
        redirect(url('views/registrar/grading-components/index.php'));
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/grading-components/index.php'));
        }

        $id = (int) ($_POST['component_id'] ?? $_POST['id'] ?? 0);
        $row = $this->componentService->getById($id);
        if (!$row) {
            flash('error', 'Component not found.');
            redirect(url('views/registrar/grading-components/index.php'));
        }

        $dependents = $this->componentService->hasDependents($id);
        if (!empty($dependents)) {
            flash('error', 'Cannot delete component. It is still referenced by: ' . implode(', ', $dependents) . '.');
            redirect(url('views/registrar/grading-components/index.php'));
        }

        $this->componentService->delete($id);
        flash('success', 'Grading component deleted successfully.');
        redirect(url('views/registrar/grading-components/index.php'));
    }

    private function validate(string $code, string $description, string $percentage, int $departmentId): array
    {
        $errors = [];
        if ($code === '' || mb_strlen($code) > 255) {
            $errors[] = 'Code is required and may not exceed 255 characters.';
        }
        if ($description === '' || mb_strlen($description) > 255) {
            $errors[] = 'Description is required and may not exceed 255 characters.';
        }
        if ($percentage === '' || !is_numeric($percentage)) {
            $errors[] = 'Percentage is required and must be numeric.';
        } else {
            $p = (float) $percentage;
            if ($p < 0 || $p > 100) {
                $errors[] = 'Percentage must be between 0 and 100.';
            }
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
