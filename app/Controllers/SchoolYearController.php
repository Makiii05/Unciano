<?php

namespace App\Controllers;

use App\Services\SchoolYearService;

class SchoolYearController
{
    private SchoolYearService $schoolYearService;

    private const ALLOWED_STATUSES = ['active', 'inactive'];

    public function __construct()
    {
        $this->schoolYearService = new SchoolYearService();
    }

    public function index(): void
    {
        ensureRegistrar();
        $pageTitle = 'School Years';
        $pageSubheader = 'Manage school years';
        $schoolYears = $this->schoolYearService->getAll();

        ob_start();
        require __DIR__ . '/../../views/registrar/school-years/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/school-years/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startYear = trim($_POST['start_year'] ?? '');
        $endYear = trim($_POST['end_year'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        $errors = $this->validate($code, $description, $startYear, $endYear, $status);
        if ($this->schoolYearService->codeExists($code)) {
            $errors[] = 'Code has already been taken.';
        }

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/school-years/index.php'));
        }

        $this->schoolYearService->create([
            'code' => $code,
            'description' => $description,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'status' => $status,
        ]);

        unset($_SESSION['old']);
        flash('success', 'School year created successfully.');
        redirect(url('views/registrar/school-years/index.php'));
    }

    public function update(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/school-years/index.php'));
        }

        $id = (int) ($_POST['school_year_id'] ?? $_POST['id'] ?? 0);
        $row = $this->schoolYearService->getById($id);
        if (!$row) {
            flash('error', 'School year not found.');
            redirect(url('views/registrar/school-years/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startYear = trim($_POST['start_year'] ?? '');
        $endYear = trim($_POST['end_year'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $errors = $this->validate($code, $description, $startYear, $endYear, $status);
        if ($this->schoolYearService->codeExists($code, $id)) {
            $errors[] = 'Code has already been taken.';
        }

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/school-years/index.php'));
        }

        $this->schoolYearService->update($id, [
            'code' => $code,
            'description' => $description,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'status' => $status,
        ]);

        flash('success', 'School year updated successfully.');
        redirect(url('views/registrar/school-years/index.php'));
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/school-years/index.php'));
        }

        $id = (int) ($_POST['school_year_id'] ?? $_POST['id'] ?? 0);
        $row = $this->schoolYearService->getById($id);
        if (!$row) {
            flash('error', 'School year not found.');
            redirect(url('views/registrar/school-years/index.php'));
        }

        $dependents = $this->schoolYearService->hasDependents($id);
        if (!empty($dependents)) {
            flash('error', 'Cannot delete school year. It is still referenced by: ' . implode(', ', $dependents) . '.');
            redirect(url('views/registrar/school-years/index.php'));
        }

        $this->schoolYearService->delete($id);
        flash('success', 'School year deleted successfully.');
        redirect(url('views/registrar/school-years/index.php'));
    }

    private function validate(string $code, string $description, string $startYear, string $endYear, string $status): array
    {
        $errors = [];
        if ($code === '' || mb_strlen($code) > 255) {
            $errors[] = 'Code is required and may not exceed 255 characters.';
        }
        if ($description === '' || mb_strlen($description) > 255) {
            $errors[] = 'Description is required and may not exceed 255 characters.';
        }
        if ($startYear === '' || mb_strlen($startYear) > 255) {
            $errors[] = 'Start year is required.';
        }
        if ($endYear === '' || mb_strlen($endYear) > 255) {
            $errors[] = 'End year is required.';
        }
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $errors[] = 'Invalid status.';
        }
        return $errors;
    }
}
