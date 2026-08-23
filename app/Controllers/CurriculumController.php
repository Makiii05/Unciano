<?php

namespace App\Controllers;

use App\Services\CurriculumService;
use App\Services\DepartmentService;

class CurriculumController
{
    private CurriculumService $curriculumService;
    private DepartmentService $departmentService;

    private const ALLOWED_STATUSES = ['active', 'inactive'];

    public function __construct()
    {
        $this->curriculumService = new CurriculumService();
        $this->departmentService = new DepartmentService();
    }

    public function index(): void
    {
        ensureRegistrar();
        $pageTitle = 'Curricula';
        $pageSubheader = 'Manage curricula';
        $curricula = $this->curriculumService->getAll();
        $departments = $this->departmentService->getForDropdown();

        ob_start();
        require __DIR__ . '/../../views/registrar/curricula/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/curricula/index.php'));
        }

        $curriculum = trim($_POST['curriculum'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $departmentId = (int) ($_POST['department_id'] ?? 0);

        $errors = $this->validate($curriculum, $status, $departmentId);

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/curricula/index.php'));
        }

        $this->curriculumService->create([
            'curriculum' => $curriculum,
            'status' => $status,
            'department_id' => $departmentId,
        ]);

        unset($_SESSION['old']);
        flash('success', 'Curriculum created successfully.');
        redirect(url('views/registrar/curricula/index.php'));
    }

    public function update(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/curricula/index.php'));
        }

        $id = (int) ($_POST['curriculum_id'] ?? $_POST['id'] ?? 0);
        $row = $this->curriculumService->getById($id);
        if (!$row) {
            flash('error', 'Curriculum not found.');
            redirect(url('views/registrar/curricula/index.php'));
        }

        $curriculum = trim($_POST['curriculum'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $departmentId = (int) ($_POST['department_id'] ?? 0);

        $errors = $this->validate($curriculum, $status, $departmentId);

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/curricula/index.php'));
        }

        $this->curriculumService->update($id, [
            'curriculum' => $curriculum,
            'status' => $status,
            'department_id' => $departmentId,
        ]);

        flash('success', 'Curriculum updated successfully.');
        redirect(url('views/registrar/curricula/index.php'));
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/curricula/index.php'));
        }

        $id = (int) ($_POST['curriculum_id'] ?? $_POST['id'] ?? 0);
        $row = $this->curriculumService->getById($id);
        if (!$row) {
            flash('error', 'Curriculum not found.');
            redirect(url('views/registrar/curricula/index.php'));
        }

        $dependents = $this->curriculumService->hasDependents($id);
        if (!empty($dependents)) {
            flash('error', 'Cannot delete curriculum. It is still referenced by: ' . implode(', ', $dependents) . '.');
            redirect(url('views/registrar/curricula/index.php'));
        }

        $this->curriculumService->delete($id);
        flash('success', 'Curriculum deleted successfully.');
        redirect(url('views/registrar/curricula/index.php'));
    }

    private function validate(string $curriculum, string $status, int $departmentId): array
    {
        $errors = [];
        if ($curriculum === '' || mb_strlen($curriculum) > 255) {
            $errors[] = 'Curriculum is required and may not exceed 255 characters.';
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
