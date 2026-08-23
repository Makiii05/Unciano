<?php

namespace App\Controllers;

use App\Services\ComponentService;
use App\Services\DepartmentService;
use App\Services\GradingSystemService;

class GradingSystemController
{
    private GradingSystemService $gradingSystemService;
    private ComponentService $componentService;
    private DepartmentService $departmentService;

    public function __construct()
    {
        $this->gradingSystemService = new GradingSystemService();
        $this->componentService = new ComponentService();
        $this->departmentService = new DepartmentService();
    }

    public function index(): void
    {
        ensureRegistrar();

        $pageTitle = 'Grading Systems';
        $pageSubheader = 'Manage grading systems and their components';

        $gradingSystems = $this->gradingSystemService->getAll();
        $components = $this->componentService->getAll();
        $departments = $this->departmentService->getForDropdown();

        // Group components by department for JS
        $componentsByDept = [];
        foreach ($components as $c) {
            $componentsByDept[$c['department_id']][] = $c;
        }

        ob_start();
        require __DIR__ . '/../../views/registrar/grading-systems/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        $description = trim($_POST['description'] ?? '');
        $departmentId = (int) ($_POST['department_id'] ?? 0);
        $componentIds = array_map('intval', $_POST['component_ids'] ?? []);

        $errors = $this->validate($description, $departmentId, $componentIds);

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        try {
            $this->gradingSystemService->create([
                'description' => $description,
                'department_id' => $departmentId,
            ], $componentIds);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        unset($_SESSION['old']);
        flash('success', 'Grading system created successfully.');
        redirect(url('views/registrar/grading-systems/index.php'));
    }

    public function update(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        $id = (int) ($_POST['grading_system_id'] ?? $_POST['id'] ?? 0);
        $row = $this->gradingSystemService->getById($id);
        if (!$row) {
            flash('error', 'Grading system not found.');
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        $description = trim($_POST['description'] ?? '');
        $departmentId = (int) ($_POST['department_id'] ?? 0);
        $componentIds = array_map('intval', $_POST['component_ids'] ?? []);

        $errors = $this->validate($description, $departmentId, $componentIds);

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        try {
            $this->gradingSystemService->update($id, [
                'description' => $description,
                'department_id' => $departmentId,
            ], $componentIds);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        flash('success', 'Grading system updated successfully.');
        redirect(url('views/registrar/grading-systems/index.php'));
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        $id = (int) ($_POST['grading_system_id'] ?? $_POST['id'] ?? 0);
        $row = $this->gradingSystemService->getById($id);
        if (!$row) {
            flash('error', 'Grading system not found.');
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        $dependents = $this->gradingSystemService->hasDependents($id);
        if (!empty($dependents)) {
            flash('error', 'Cannot delete grading system. It is still referenced by: ' . implode(', ', $dependents) . '.');
            redirect(url('views/registrar/grading-systems/index.php'));
        }

        $this->gradingSystemService->delete($id);
        flash('success', 'Grading system deleted successfully.');
        redirect(url('views/registrar/grading-systems/index.php'));
    }

    private function validate(string $description, int $departmentId, array $componentIds): array
    {
        $errors = [];
        if ($description === '' || mb_strlen($description) > 255) {
            $errors[] = 'Description is required and may not exceed 255 characters.';
        }
        if ($departmentId <= 0) {
            $errors[] = 'Department is required.';
        } else {
            $dept = $this->departmentService->getById($departmentId);
            if (!$dept) {
                $errors[] = 'Selected department is invalid.';
            }
        }
        if (empty($componentIds)) {
            $errors[] = 'At least one component must be selected.';
        } else {
            // Ensure components belong to same department
            foreach ($componentIds as $cid) {
                $comp = $this->componentService->getById($cid);
                if (!$comp) {
                    $errors[] = 'Selected component is invalid.';
                    break;
                }
                if ((int) $comp['department_id'] !== $departmentId) {
                    $errors[] = 'All selected components must belong to the selected department.';
                    break;
                }
            }
            // Check total percentage via service (will throw, but pre-validate for better message)
            $total = $this->gradingSystemService->calculateTotalPercentage($componentIds);
            if ($total > 100) {
                $errors[] = 'Total percentage cannot exceed 100. Current total: ' . $total . '%';
            }
        }
        return $errors;
    }
}
