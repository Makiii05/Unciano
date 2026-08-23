<?php

namespace App\Controllers;

use App\Services\LevelService;
use App\Services\ProgramService;

class LevelController
{
    private LevelService $levelService;
    private ProgramService $programService;

    public function __construct()
    {
        $this->levelService = new LevelService();
        $this->programService = new ProgramService();
    }

    public function index(): void
    {
        ensureRegistrar();
        $pageTitle = 'Levels';
        $pageSubheader = 'Manage year levels';
        $levels = $this->levelService->getAll();
        $programs = $this->programService->getForDropdown();

        ob_start();
        require __DIR__ . '/../../views/registrar/levels/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/levels/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $programId = (int) ($_POST['program_id'] ?? 0);
        $order = (int) ($_POST['order'] ?? 0);

        $errors = $this->validate($code, $description, $programId, $order);

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/levels/index.php'));
        }

        $this->levelService->create([
            'code' => $code,
            'description' => $description,
            'program_id' => $programId,
            'order' => $order,
        ]);

        unset($_SESSION['old']);
        flash('success', 'Level created successfully.');
        redirect(url('views/registrar/levels/index.php'));
    }

    public function update(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/levels/index.php'));
        }

        $id = (int) ($_POST['level_id'] ?? $_POST['id'] ?? 0);
        $row = $this->levelService->getById($id);
        if (!$row) {
            flash('error', 'Level not found.');
            redirect(url('views/registrar/levels/index.php'));
        }

        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $programId = (int) ($_POST['program_id'] ?? 0);
        $order = (int) ($_POST['order'] ?? 0);

        $errors = $this->validate($code, $description, $programId, $order);

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect(url('views/registrar/levels/index.php'));
        }

        $this->levelService->update($id, [
            'code' => $code,
            'description' => $description,
            'program_id' => $programId,
            'order' => $order,
        ]);

        flash('success', 'Level updated successfully.');
        redirect(url('views/registrar/levels/index.php'));
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/levels/index.php'));
        }

        $id = (int) ($_POST['level_id'] ?? $_POST['id'] ?? 0);
        $row = $this->levelService->getById($id);
        if (!$row) {
            flash('error', 'Level not found.');
            redirect(url('views/registrar/levels/index.php'));
        }

        $dependents = $this->levelService->hasDependents($id);
        if (!empty($dependents)) {
            flash('error', 'Cannot delete level. It is still referenced by: ' . implode(', ', $dependents) . '.');
            redirect(url('views/registrar/levels/index.php'));
        }

        $this->levelService->delete($id);
        flash('success', 'Level deleted successfully.');
        redirect(url('views/registrar/levels/index.php'));
    }

    private function validate(string $code, string $description, int $programId, int $order): array
    {
        $errors = [];
        if ($code === '' || mb_strlen($code) > 255) {
            $errors[] = 'Code is required and may not exceed 255 characters.';
        }
        if ($description === '' || mb_strlen($description) > 255) {
            $errors[] = 'Description is required and may not exceed 255 characters.';
        }
        if ($programId <= 0) {
            $errors[] = 'Program is required.';
        } else {
            $prog = $this->programService->getById($programId);
            if (!$prog) {
                $errors[] = 'Selected program is invalid.';
            }
        }
        if ($order < 0) {
            $errors[] = 'Order must be 0 or greater.';
        }
        return $errors;
    }
}
