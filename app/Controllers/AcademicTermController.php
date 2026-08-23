<?php

namespace App\Controllers;

use App\Services\AcademicTermService;
use App\Services\DepartmentService;
use App\Services\SchoolYearService;

class AcademicTermController
{
    private AcademicTermService $academicTermService;
    private SchoolYearService $schoolYearService;
    private DepartmentService $departmentService;

    private const ALLOWED_STATUSES = ['active', 'inactive'];
    private const ALLOWED_TYPES = ['semester', 'trimestral', 'summer', 'trimester'];

    public function __construct()
    {
        $this->academicTermService = new AcademicTermService();
        $this->schoolYearService = new SchoolYearService();
        $this->departmentService = new DepartmentService();
    }

    public function index(): void
    {
        ensureRegistrar();
        $pageTitle = 'Academic Terms';
        $pageSubheader = 'Manage academic terms';
        $academicTerms = $this->academicTermService->getAll();
        $schoolYears = $this->schoolYearService->getForDropdown();
        $departments = $this->departmentService->getForDropdown();

        ob_start();
        require __DIR__ . '/../../views/registrar/academic-terms/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/academic-terms/index.php'));
        }

        $data = $this->extractData($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/academic-terms/index.php'));
        }

        $this->academicTermService->create($data);
        unset($_SESSION['old']);
        flash('success', 'Academic term created successfully.');
        redirect(url('views/registrar/academic-terms/index.php'));
    }

    public function update(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/academic-terms/index.php'));
        }

        $id = (int) ($_POST['academic_term_id'] ?? $_POST['id'] ?? 0);
        $row = $this->academicTermService->getById($id);
        if (!$row) {
            flash('error', 'Academic term not found.');
            redirect(url('views/registrar/academic-terms/index.php'));
        }

        $data = $this->extractData($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/academic-terms/index.php'));
        }

        $this->academicTermService->update($id, $data);
        flash('success', 'Academic term updated successfully.');
        redirect(url('views/registrar/academic-terms/index.php'));
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/academic-terms/index.php'));
        }

        $id = (int) ($_POST['academic_term_id'] ?? $_POST['id'] ?? 0);
        $row = $this->academicTermService->getById($id);
        if (!$row) {
            flash('error', 'Academic term not found.');
            redirect(url('views/registrar/academic-terms/index.php'));
        }

        $dependents = $this->academicTermService->hasDependents($id);
        if (!empty($dependents)) {
            flash('error', 'Cannot delete academic term. It is still referenced by: ' . implode(', ', $dependents) . '.');
            redirect(url('views/registrar/academic-terms/index.php'));
        }

        $this->academicTermService->delete($id);
        flash('success', 'Academic term deleted successfully.');
        redirect(url('views/registrar/academic-terms/index.php'));
    }

    private function extractData(array $post): array
    {
        $departmentId = trim($post['department_id'] ?? '');
        return [
            'code' => trim($post['code'] ?? ''),
            'description' => trim($post['description'] ?? ''),
            'type' => trim($post['type'] ?? 'semester'),
            'department_id' => $departmentId === '' ? null : (int) $departmentId,
            'school_year_id' => (int) ($post['school_year_id'] ?? 0),
            'start_date' => trim($post['start_date'] ?? ''),
            'end_date' => trim($post['end_date'] ?? ''),
            'status' => trim($post['status'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['code'] === '' || mb_strlen($data['code']) > 255) {
            $errors[] = 'Code is required and may not exceed 255 characters.';
        }
        if ($data['description'] === '' || mb_strlen($data['description']) > 255) {
            $errors[] = 'Description is required and may not exceed 255 characters.';
        }
        if (!in_array($data['type'], self::ALLOWED_TYPES, true)) {
            $errors[] = 'Invalid term type. Use semester, trimestral or summer.';
        }
        if ($data['school_year_id'] <= 0) {
            $errors[] = 'School year is required.';
        } else {
            $sy = $this->schoolYearService->getById($data['school_year_id']);
            if (!$sy) {
                $errors[] = 'Selected school year is invalid.';
            }
        }
        if ($data['department_id'] !== null) {
            $dept = $this->departmentService->getById($data['department_id']);
            if (!$dept) {
                $errors[] = 'Selected department is invalid.';
            }
        }
        if ($data['start_date'] === '' || !strtotime($data['start_date'])) {
            $errors[] = 'Valid start date is required.';
        }
        if ($data['end_date'] === '' || !strtotime($data['end_date'])) {
            $errors[] = 'Valid end date is required.';
        } elseif (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
            $errors[] = 'End date must be after start date.';
        }
        if (!in_array($data['status'], self::ALLOWED_STATUSES, true)) {
            $errors[] = 'Invalid status.';
        }
        return $errors;
    }
}
