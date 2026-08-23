<?php

namespace App\Controllers;

use App\Services\AcademicTermService;
use App\Services\CurriculumService;
use App\Services\DepartmentService;
use App\Services\GradingSystemService;
use App\Services\LevelService;
use App\Services\ProgramService;
use App\Services\ProspectusService;
use App\Services\SubjectOfferingService;
use App\Services\SubjectService;

class SubjectOfferingController
{
    private SubjectOfferingService $subjectOfferingService;
    private ProspectusService $prospectusService;
    private SubjectService $subjectService;
    private CurriculumService $curriculumService;
    private AcademicTermService $academicTermService;
    private ProgramService $programService;
    private LevelService $levelService;
    private GradingSystemService $gradingSystemService;
    private DepartmentService $departmentService;

    public function __construct()
    {
        $this->subjectOfferingService = new SubjectOfferingService();
        $this->prospectusService = new ProspectusService();
        $this->subjectService = new SubjectService();
        $this->curriculumService = new CurriculumService();
        $this->academicTermService = new AcademicTermService();
        $this->programService = new ProgramService();
        $this->levelService = new LevelService();
        $this->gradingSystemService = new GradingSystemService();
        $this->departmentService = new DepartmentService();
    }

    public function index(): void
    {
        ensureRegistrar();

        $pageTitle = 'Subject Offerings';
        $pageSubheader = 'Manage subject offerings per term';

        $departments = $this->departmentService->getForDropdown();
        $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== '' ? (int) $_GET['department_id'] : null;
        $termId = isset($_GET['term']) && $_GET['term'] !== '' ? (int) $_GET['term'] : null;
        $curriculumId = isset($_GET['curriculum']) && $_GET['curriculum'] !== '' ? (int) $_GET['curriculum'] : null;
        $tab = $_GET['tab'] ?? 'prospectus';

        $terms = $departmentId ? $this->getTermsByDepartment($departmentId) : [];
        $curricula = [];
        if ($departmentId) {
            $allCurr = $this->curriculumService->getAll();
            $curricula = array_values(array_filter($allCurr, fn($c) => (int)($c['department_id'] ?? 0) === $departmentId));
        }

        $prospectus = [];
        if ($curriculumId) {
            $prospectus = $this->prospectusService->getByCurriculum($curriculumId);
        }

        $offerings = [];
        if ($termId) {
            $offerings = $this->subjectOfferingService->getByTerm($termId);
            // If department filter, filter offerings by department
            if ($departmentId) {
                $offerings = array_filter($offerings, fn($o) => (int)($o['department_id'] ?? 0) === $departmentId);
                $offerings = array_values($offerings);
            }
        }

        $programs = $departmentId ? $this->programService->getByDepartment($departmentId) : [];
        $gradingSystems = $departmentId ? $this->gradingSystemService->getByDepartment($departmentId) : [];

        // For JS: need all departments for dropdowns
        $allTermsForJs = $terms;

        ob_start();
        require __DIR__ . '/../../views/registrar/subject-offerings/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    private function getTermsByDepartment(int $departmentId): array
    {
        // AcademicTermService has getAll, filter by department_id
        $all = $this->academicTermService->getAll();
        $filtered = [];
        foreach ($all as $t) {
            if ((int)($t['department_id'] ?? 0) === $departmentId || $t['department_id'] === null) {
                $filtered[] = $t;
            }
        }
        // If none with null, try direct query
        if (empty($filtered)) {
            try {
                $db = \App\Core\Database::connection();
                $stmt = $db->prepare('SELECT * FROM academic_terms WHERE department_id = ? OR department_id IS NULL ORDER BY start_date DESC');
                $stmt->execute([$departmentId]);
                $filtered = $stmt->fetchAll();
            } catch (\Throwable $e) {}
        }
        return $filtered;
    }

    public function prospectusSubjects(): void
    {
        ensureRegistrar();
        $curriculumId = (int) ($_GET['curriculum_id'] ?? 0);
        if ($curriculumId <= 0) {
            $this->json(['success' => false, 'message' => 'Curriculum is required.'], 422);
            return;
        }
        $curriculum = $this->curriculumService->getById($curriculumId);
        if (!$curriculum) {
            $this->json(['success' => false, 'message' => 'Curriculum not found.'], 404);
            return;
        }
        $prospectus = $this->prospectusService->getByCurriculum($curriculumId);
        $this->json(['success' => true, 'data' => $prospectus]);
    }

    public function departmentData(): void
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

        $curricula = array_values(array_filter(
            $this->curriculumService->getAll(),
            fn($curriculum) => (int) ($curriculum['department_id'] ?? 0) === $departmentId
        ));

        $this->json([
            'success' => true,
            'data' => [
                'terms' => $this->getTermsByDepartment($departmentId),
                'curricula' => $curricula,
                'programs' => $this->programService->getByDepartment($departmentId),
                'grading_systems' => $this->gradingSystemService->getByDepartment($departmentId),
            ],
        ]);
    }

    public function offeringsByTerm(): void
    {
        ensureRegistrar();
        $termId = (int) ($_GET['term_id'] ?? 0);
        $departmentId = (int) ($_GET['department_id'] ?? 0);
        if ($termId <= 0) {
            $this->json(['success' => false, 'message' => 'Academic term is required.'], 422);
            return;
        }
        if (!$this->academicTermService->getById($termId)) {
            $this->json(['success' => false, 'message' => 'Academic term not found.'], 404);
            return;
        }

        $offerings = $this->subjectOfferingService->getByTerm($termId);
        if ($departmentId > 0) {
            $offerings = array_values(array_filter(
                $offerings,
                fn($offering) => (int) ($offering['department_id'] ?? 0) === $departmentId
            ));
        }
        $this->json(['success' => true, 'data' => $offerings]);
    }

    public function searchSubjects(): void
    {
        ensureRegistrar();
        $q = trim($_GET['q'] ?? $_GET['query'] ?? '');
        if ($q === '') {
            $this->json(['success' => true, 'data' => []]);
            return;
        }
        $results = $this->searchSubjectsLike($q);
        $this->json(['success' => true, 'data' => $results]);
    }

    private function searchSubjectsLike(string $q): array
    {
        $db = \App\Core\Database::connection();
        $like = "%$q%";
        $stmt = $db->prepare('SELECT id, code, description FROM subjects WHERE code LIKE ? OR description LIKE ? ORDER BY code LIMIT 10');
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public function levelsByProgram(): void
    {
        ensureRegistrar();
        $programId = (int) ($_GET['program_id'] ?? 0);
        if ($programId <= 0) {
            $this->json(['success' => false, 'message' => 'Program is required.'], 422);
            return;
        }
        $program = $this->programService->getById($programId);
        if (!$program) {
            $this->json(['success' => false, 'message' => 'Program not found.'], 404);
            return;
        }
        $levels = $this->levelService->getForDropdown();
        $filtered = array_filter($levels, fn($l) => (int)($l['program_id'] ?? 0) === $programId);
        $this->json(['success' => true, 'data' => array_values($filtered)]);
    }

    public function store(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 419);
            return;
        }

        $departmentId = (int) ($_POST['department_id'] ?? 0);
        $academicTermId = (int) ($_POST['academic_term_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $prospectusId = isset($_POST['prospectus_id']) && $_POST['prospectus_id'] !== '' ? (int) $_POST['prospectus_id'] : null;
        $programId = isset($_POST['program_id']) && $_POST['program_id'] !== '' ? (int) $_POST['program_id'] : null;
        $levelId = isset($_POST['level_id']) && $_POST['level_id'] !== '' ? (int) $_POST['level_id'] : null;
        $gradingId = isset($_POST['grading_id']) && $_POST['grading_id'] !== '' ? (int) $_POST['grading_id'] : null;
        $classSize = isset($_POST['class_size']) && $_POST['class_size'] !== '' ? (int) $_POST['class_size'] : 40;

        $errors = $this->validateStore($departmentId, $academicTermId, $subjectId, $prospectusId, $programId, $levelId, $gradingId, $classSize);
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => implode(' ', $errors)], 422);
            return;
        }

        try {
            if ($prospectusId) {
                $offering = $this->subjectOfferingService->createFromProspectus([
                    'academic_term_id' => $academicTermId,
                    'prospectus_id' => $prospectusId,
                    'grading_id' => $gradingId,
                    'class_size' => $classSize,
                    'department_id' => $departmentId,
                ]);
            } else {
                $offering = $this->subjectOfferingService->createFromSearch([
                    'academic_term_id' => $academicTermId,
                    'subject_id' => $subjectId,
                    'program_id' => $programId,
                    'level_id' => $levelId,
                    'grading_id' => $gradingId,
                    'class_size' => $classSize,
                    'department_id' => $departmentId,
                ]);
            }
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            return;
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Failed to create offering: ' . $e->getMessage()], 500);
            return;
        }

        $this->json(['success' => true, 'message' => 'Subject offering created.', 'data' => $offering], 201);
    }

    public function destroy(): void
    {
        ensureRegistrar();
        if (!validate_csrf()) {
            if ($this->isJsonRequest()) {
                $this->json(['success' => false, 'message' => 'Invalid request.'], 419);
                return;
            }
            flash('error', 'Invalid request.');
            redirect(url('views/registrar/subject-offerings/index.php'));
            return;
        }

        $id = (int) ($_POST['subject_offering_id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);
        $row = $this->subjectOfferingService->getById($id);
        if (!$row) {
            if ($this->isJsonRequest()) {
                $this->json(['success' => false, 'message' => 'Offering not found.'], 404);
                return;
            }
            flash('error', 'Offering not found.');
            redirect(url('views/registrar/subject-offerings/index.php'));
            return;
        }

        $dependents = $this->subjectOfferingService->hasDependents($id);
        if (!empty($dependents)) {
            if ($this->isJsonRequest()) {
                $this->json(['success' => false, 'message' => 'Cannot delete. Referenced by: ' . implode(', ', $dependents)], 422);
                return;
            }
            flash('error', 'Cannot delete. Referenced by: ' . implode(', ', $dependents));
            redirect(url('views/registrar/subject-offerings/index.php'));
            return;
        }

        $this->subjectOfferingService->delete($id);
        if ($this->isJsonRequest()) {
            $this->json(['success' => true, 'message' => 'Offering deleted.']);
            return;
        }
        flash('success', 'Subject offering deleted.');
        redirect(url('views/registrar/subject-offerings/index.php'));
    }

    private function validateStore(int $departmentId, int $academicTermId, int $subjectId, ?int $prospectusId, ?int $programId, ?int $levelId, ?int $gradingId, int $classSize): array
    {
        $errors = [];
        if ($departmentId <= 0) {
            $errors[] = 'Department is required.';
        } else {
            $dept = $this->departmentService->getById($departmentId);
            if (!$dept) $errors[] = 'Invalid department.';
        }
        if ($academicTermId <= 0) {
            $errors[] = 'Academic term is required.';
        } else {
            $term = $this->academicTermService->getById($academicTermId);
            if (!$term) $errors[] = 'Invalid academic term.';
        }
        if ($prospectusId) {
            $stmt = \App\Core\Database::connection()->prepare('SELECT id FROM prospectuses WHERE id = ? LIMIT 1');
            $stmt->execute([$prospectusId]);
            if (!$stmt->fetch()) $errors[] = 'Invalid prospectus.';
            // subject_id still required for offering, but prospectus provides it; we check subjectId
            if ($subjectId <= 0) $errors[] = 'Subject is required.';
        } else {
            if ($subjectId <= 0) $errors[] = 'Subject is required.';
            if ($programId === null || $programId <= 0) $errors[] = 'Program is required.';
            if ($levelId === null || $levelId <= 0) $errors[] = 'Level is required.';
            // Validate program belongs to department
            if ($programId) {
                $prog = $this->programService->getById($programId);
                if (!$prog) $errors[] = 'Invalid program.';
                elseif ((int) $prog['department_id'] !== $departmentId) $errors[] = 'Program does not belong to selected department.';
            }
            if ($levelId) {
                $lvl = $this->levelService->getById($levelId);
                if (!$lvl) $errors[] = 'Invalid level.';
                elseif ($programId && (int) $lvl['program_id'] !== $programId) $errors[] = 'Level does not belong to selected program.';
            }
        }
        if ($gradingId !== null && $gradingId > 0) {
            $gs = $this->gradingSystemService->getById($gradingId);
            if (!$gs) $errors[] = 'Invalid grading system.';
            elseif ((int) $gs['department_id'] !== $departmentId) $errors[] = 'Grading system does not belong to selected department.';
        } else {
            $errors[] = 'Grading system is required.';
        }
        if ($classSize < 1 || $classSize > 500) {
            $errors[] = 'Class size must be between 1 and 500.';
        }
        return $errors;
    }

    private function isJsonRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || $requestedWith === 'XMLHttpRequest';
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
