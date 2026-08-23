<?php

namespace App\Controllers;

use App\Services\AcademicTermService;
use App\Services\CurriculumService;
use App\Services\DepartmentService;
use App\Services\LevelService;
use App\Services\ProspectusService;
use App\Services\SubjectService;

class ProspectusController
{
    private ProspectusService $prospectusService;
    private DepartmentService $departmentService;
    private CurriculumService $curriculumService;
    private LevelService $levelService;
    private AcademicTermService $academicTermService;
    private SubjectService $subjectService;

    private const ALLOWED_STATUSES = ['active', 'inactive'];

    public function __construct()
    {
        $this->prospectusService = new ProspectusService();
        $this->departmentService = new DepartmentService();
        $this->curriculumService = new CurriculumService();
        $this->levelService = new LevelService();
        $this->academicTermService = new AcademicTermService();
        $this->subjectService = new SubjectService();
    }

    public function index(): void
    {
        ensureRegistrar();

        $pageTitle = 'Prospectus';
        $pageSubheader = 'Manage curriculum subject mapping';

        $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== '' ? (int) $_GET['department_id'] : null;
        $curriculumId = isset($_GET['curriculum_id']) && $_GET['curriculum_id'] !== '' ? (int) $_GET['curriculum_id'] : null;

        $departments = $this->departmentService->getForDropdown();
        $curricula = [];
        $prospectus = null;
        $selectedCurriculum = null;
        $allLevels = $this->levelService->getForDropdown();
        $levels = [];
        $terms = $this->academicTermService->getForDropdown();
        // For dropdown display, fetch terms with school year info
        $termsFull = $this->academicTermService->getAll();
        $subjects = $this->subjectService->getForDropdown();

        if ($departmentId !== null) {
            $curricula = $this->getCurriculaByDepartmentId($departmentId);
            // Also filter levels by department for display? Use allLevels and let JS filter, but also provide filtered for grouping
            // Fetch levels that belong to programs of this department
            $levels = $this->filterLevelsByDepartment($allLevels, $departmentId);
        }

        if ($curriculumId !== null) {
            $selectedCurriculum = $this->curriculumService->getById($curriculumId);
            $prospectus = $this->prospectusService->getByCurriculum($curriculumId);
        }

        // For highlight after create
        $newProspectusId = $_SESSION['_new_prospectus_id'] ?? null;
        unset($_SESSION['_new_prospectus_id']);

        ob_start();
        require __DIR__ . '/../../views/registrar/prospectus/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function curriculaByDepartment(): void
    {
        ensureRegistrar();
        $departmentId = (int) ($_GET['department_id'] ?? 0);
        if ($departmentId <= 0) {
            $this->json(['success' => false, 'message' => 'Department is required.'], 422);
            return;
        }
        $dept = $this->departmentService->getById($departmentId);
        if (!$dept) {
            $this->json(['success' => false, 'message' => 'Department not found.'], 404);
            return;
        }
        $curricula = $this->getCurriculaByDepartmentId($departmentId);
        $this->json(['success' => true, 'data' => $curricula]);
    }

    public function levelsByDepartment(): void
    {
        ensureRegistrar();
        $departmentId = (int) ($_GET['department_id'] ?? 0);
        if ($departmentId <= 0) {
            $this->json(['success' => false, 'message' => 'Department is required.'], 422);
            return;
        }
        if (!$this->departmentService->getById($departmentId)) {
            $this->json(['success' => false, 'message' => 'Department not found.'], 404);
            return;
        }
        $this->json(['success' => true, 'data' => $this->filterLevelsByDepartment([], $departmentId)]);
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/prospectus/index.php'));
        }

        $data = $this->extractData($_POST);
        $errors = $this->validate($data);

        if ($this->prospectusService->existsComposite($data['curriculum_id'], $data['level_id'], $data['term_id'], $data['subject_id'])) {
            $errors[] = 'This subject is already assigned to this curriculum, level, and term.';
        }

        if (!empty($errors)) {
            $_SESSION['old'] = $_POST;
            flash('error', implode(' ', $errors));
            $this->redirectWithFilter($data);
            return;
        }

        $newId = $this->prospectusService->create($data);
        $_SESSION['_new_prospectus_id'] = $newId;
        unset($_SESSION['old']);
        flash('success', 'Prospectus entry created successfully.');
        $this->redirectWithFilter($data);
    }

    public function update(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/prospectus/index.php'));
        }

        $id = (int) ($_POST['prospectus_id'] ?? $_POST['id'] ?? 0);
        $row = $this->prospectusService->getById($id);
        if (!$row) {
            flash('error', 'Prospectus entry not found.');
            redirect(url('views/registrar/prospectus/index.php'));
        }

        $data = $this->extractData($_POST);
        $errors = $this->validate($data);

        if ($this->prospectusService->existsComposite($data['curriculum_id'], $data['level_id'], $data['term_id'], $data['subject_id'], $id)) {
            $errors[] = 'This subject is already assigned to this curriculum, level, and term.';
        }

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            $this->redirectWithFilter($data, $row);
            return;
        }

        $this->prospectusService->update($id, $data);
        flash('success', 'Prospectus entry updated successfully.');
        $this->redirectWithFilter($data, $row);
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/prospectus/index.php'));
        }

        $id = (int) ($_POST['prospectus_id'] ?? $_POST['id'] ?? 0);
        $row = $this->prospectusService->getById($id);
        if (!$row) {
            flash('error', 'Prospectus entry not found.');
            redirect(url('views/registrar/prospectus/index.php'));
        }

        // Preserve filter before delete
        $filter = [
            'curriculum_id' => $row['curriculum_id'],
            'department_id' => null,
        ];
        // Try to get department from curriculum
        $curr = $this->curriculumService->getById((int) $row['curriculum_id']);
        if ($curr) {
            $filter['department_id'] = $curr['department_id'];
        }

        $this->prospectusService->delete($id);
        flash('success', 'Prospectus entry deleted successfully.');
        $qs = http_build_query(array_filter($filter));
        redirect(url('views/registrar/prospectus/index.php' . ($qs ? '?' . $qs : '')));
    }

    private function extractData(array $post): array
    {
        return [
            'curriculum_id' => (int) ($post['curriculum_id'] ?? 0),
            'level_id' => (int) ($post['level_id'] ?? 0),
            'term_id' => (int) ($post['term_id'] ?? 0),
            'subject_id' => (int) ($post['subject_id'] ?? 0),
            'status' => trim($post['status'] ?? 'active'),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['curriculum_id'] <= 0 || !$this->curriculumService->getById($data['curriculum_id'])) {
            $errors[] = 'Valid curriculum is required.';
        }
        if ($data['level_id'] <= 0 || !$this->levelService->getById($data['level_id'])) {
            $errors[] = 'Valid level is required.';
        } elseif (!$this->levelBelongsToCurriculumDepartment($data['level_id'], $data['curriculum_id'])) {
            $errors[] = 'Selected level does not belong to the curriculum department.';
        }
        if ($data['term_id'] <= 0 || !$this->academicTermService->getById($data['term_id'])) {
            $errors[] = 'Valid academic term is required.';
        }
        if ($data['subject_id'] <= 0 || !$this->subjectService->getById($data['subject_id'])) {
            $errors[] = 'Valid subject is required.';
        }
        if (!in_array($data['status'], self::ALLOWED_STATUSES, true)) {
            $errors[] = 'Invalid status.';
        }
        return $errors;
    }

    private function redirectWithFilter(array $data, ?array $fallbackRow = null): void
    {
        $curriculumId = $data['curriculum_id'] ?? ($fallbackRow['curriculum_id'] ?? null);
        $departmentId = null;
        if ($curriculumId) {
            $curr = $this->curriculumService->getById((int) $curriculumId);
            if ($curr) $departmentId = $curr['department_id'];
        } elseif (isset($_POST['department_id'])) {
            $departmentId = (int) $_POST['department_id'];
        }
        $qs = http_build_query(array_filter(['department_id' => $departmentId, 'curriculum_id' => $curriculumId]));
        redirect(url('views/registrar/prospectus/index.php' . ($qs ? '?' . $qs : '')));
    }

    private function getCurriculaByDepartmentId(int $departmentId): array
    {
        // Use direct query via service if available, else filter
        // CurriculumService has no direct method, so query via DB
        $all = $this->curriculumService->getAll();
        $filtered = [];
        foreach ($all as $c) {
            if ((int) ($c['department_id'] ?? $c['curriculum_department_id'] ?? 0) === $departmentId || (int) $c['department_id'] === $departmentId) {
                $filtered[] = $c;
            }
        }
        // Fallback to DB query if none
        if (empty($filtered)) {
            try {
                $db = \App\Core\Database::connection();
                $stmt = $db->prepare('SELECT id, curriculum, department_id FROM curricula WHERE department_id = ? ORDER BY curriculum');
                $stmt->execute([$departmentId]);
                $filtered = $stmt->fetchAll();
            } catch (\Throwable $e) {}
        }
        return $filtered;
    }

    private function filterLevelsByDepartment(array $allLevels, int $departmentId): array
    {
        // Need to map levels to department via programs
        // Fetch programs for department then filter
        try {
            $db = \App\Core\Database::connection();
            $stmt = $db->prepare('SELECT l.id, l.code, l.description, l.program_id, l.`order` FROM levels l JOIN programs p ON p.id = l.program_id WHERE p.department_id = ? ORDER BY l.`order`, l.code');
            $stmt->execute([$departmentId]);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function levelBelongsToCurriculumDepartment(int $levelId, int $curriculumId): bool
    {
        try {
            $db = \App\Core\Database::connection();
            $stmt = $db->prepare(
                'SELECT 1 FROM levels l JOIN programs p ON p.id = l.program_id JOIN curricula c ON c.department_id = p.department_id WHERE l.id = ? AND c.id = ? LIMIT 1'
            );
            $stmt->execute([$levelId, $curriculumId]);
            return (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
