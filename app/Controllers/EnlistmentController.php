<?php

namespace App\Controllers;

use App\Services\AcademicTermService;
use App\Services\EnlistmentService;
use App\Services\LevelService;
use App\Services\StudentService;

class EnlistmentController
{
    private EnlistmentService $enlistmentService;
    private StudentService $studentService;
    private AcademicTermService $academicTermService;
    private LevelService $levelService;

    public function __construct()
    {
        $this->enlistmentService = new EnlistmentService();
        $this->studentService = new StudentService();
        $this->academicTermService = new AcademicTermService();
        $this->levelService = new LevelService();
    }

    public function index(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int) $user['department_id'];
        $terms = $this->academicTermService->getForDropdownByDepartment($deptId);
        $termId = isset($_GET['term']) ? (int) $_GET['term'] : 0;

        $pageTitle = 'Enlistment';
        $pageSubheader = 'Assign subject offerings to enrolled students';

        ob_start();
        require __DIR__ . '/../../views/department/enlistment/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function show(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int) $user['department_id'];
        $studentId = (int) ($_GET['student_id'] ?? $_GET['id'] ?? 0);
        $termId = (int) ($_GET['term'] ?? 0);
        if (!$studentId || !$termId) {
            flash('error', 'Missing student or term.');
            redirect(url('views/department/enlistment/index.php'));
        }
        $student = $this->studentService->getById($studentId);
        if (!$student || (int)$student['department_id'] !== $deptId) {
            flash('error', 'This student does not belong to your department.');
            redirect(url('views/department/enlistment/index.php'));
        }
        // verify term belongs to dept
        $terms = $this->academicTermService->getForDropdownByDepartment($deptId);
        $term = null;
        foreach ($terms as $t) if ((int)$t['id'] === $termId) { $term = $t; break; }
        if (!$term) {
            flash('error', 'Invalid academic term.');
            redirect(url('views/department/enlistment/index.php'));
        }
        $levels = $this->levelService->getByProgram((int) $student['program_id']);
        $enlistments = $this->enlistmentService->getEnlistmentsByTerm($studentId, $termId);

        $pageTitle = 'Enlistment';
        $pageSubheader = 'Subject offerings assigned to this student';

        ob_start();
        require __DIR__ . '/../../views/department/enlistment/show.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    // JSON handlers for api/department/*

    public function searchStudentsJson(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int) $user['department_id'];
        $q = trim($_GET['q'] ?? '');
        $students = $this->studentService->searchInDepartment($deptId, $q);
        header('Content-Type: application/json');
        echo json_encode($students);
        exit;
    }

    public function offeringsByTermJson(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int) $user['department_id'];
        $termId = (int) ($_GET['academic_term_id'] ?? 0);
        $studentId = (int) ($_GET['student_id'] ?? 0);
        if (!$termId || !$studentId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'academic_term_id and student_id are required.']);
            exit;
        }
        $offerings = $this->enlistmentService->getOfferingsForTerm($termId, $deptId, $studentId);
        header('Content-Type: application/json');
        echo json_encode($offerings);
        exit;
    }

    public function sectionsByTermJson(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int) $user['department_id'];
        $termId = (int) ($_GET['academic_term_id'] ?? 0);
        if (!$termId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'academic_term_id is required.']);
            exit;
        }
        $sections = $this->enlistmentService->getSectionsByTerm($termId, $deptId);
        header('Content-Type: application/json');
        echo json_encode($sections);
        exit;
    }

    public function storeJson(): void
    {
        ensureDepartment();
        if (!validate_csrf()) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Invalid CSRF token.']);
            exit;
        }
        $user = auth();
        $deptId = (int) $user['department_id'];
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $termId = (int) ($_POST['academic_term_id'] ?? 0);
        $offeringId = (int) ($_POST['subject_offering_id'] ?? 0);
        if (!$studentId || !$termId || !$offeringId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'All fields are required.']);
            exit;
        }
        $student = $this->studentService->getById($studentId);
        if (!$student || (int)$student['department_id'] !== $deptId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'This student does not belong to your department.']);
            exit;
        }
        $offeringStmt = \App\Core\Database::connection()->prepare('SELECT * FROM subject_offerings WHERE id=? LIMIT 1');
        $offeringStmt->execute([$offeringId]);
        $offering = $offeringStmt->fetch();
        if (!$offering || (int)$offering['department_id'] !== $deptId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'This subject offering does not belong to your department.']);
            exit;
        }
        try {
            $result = $this->enlistmentService->enlist(['student_id'=>$studentId,'academic_term_id'=>$termId,'subject_offering_id'=>$offeringId]);
        } catch (\RuntimeException $e) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>$e->getMessage()]);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['message'=>'Subject enlisted successfully.','enlistment'=>$result['enlistment'],'student_type_changed'=>$result['student_type_changed'],'student_type'=>$result['student_type']]);
        exit;
    }

    public function bulkStoreJson(): void
    {
        ensureDepartment();
        if (!validate_csrf()) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Invalid CSRF token.']);
            exit;
        }
        $user = auth();
        $deptId = (int)$user['department_id'];
        $studentId = (int)($_POST['student_id'] ?? 0);
        $termId = (int)($_POST['academic_term_id'] ?? 0);
        $section = trim($_POST['section'] ?? '');
        if (!$studentId || !$termId || $section==='') {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'student_id, academic_term_id and section are required.']);
            exit;
        }
        $student = $this->studentService->getById($studentId);
        if (!$student || (int)$student['department_id'] !== $deptId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'This student does not belong to your department.']);
            exit;
        }
        try {
            $result = $this->enlistmentService->enlistBySection(['student_id'=>$studentId,'academic_term_id'=>$termId,'section'=>$section,'department_id'=>$deptId]);
        } catch (\RuntimeException $e) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>$e->getMessage()]);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['message'=>$result['enlistments'] ? count($result['enlistments']).' subject(s) enlisted from section '.$section.'.' : 'No subjects enlisted.','enlistments'=>$result['enlistments'],'student_type_changed'=>$result['student_type_changed'],'student_type'=>$result['student_type']]);
        exit;
    }

    public function updateStudentJson(): void
    {
        ensureDepartment();
        // Accept JSON or POST
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = $_POST;
        // CSRF check via header
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if ($token === '') $token = $input['_token'] ?? '';
        if (!hash_equals(csrf_token(), $token)) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Invalid CSRF token.']);
            exit;
        }
        $user = auth();
        $deptId = (int)$user['department_id'];
        $studentId = (int)($_GET['student_id'] ?? $input['student_id'] ?? 0);
        if (!$studentId) {
            // Try URL path extraction
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (preg_match('#/api/department/enlistment/(\d+)#',$uri,$m)) $studentId = (int)$m[1];
        }
        if (!$studentId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'student_id required']);
            exit;
        }
        $student = $this->studentService->getById($studentId);
        if (!$student || (int)$student['department_id'] !== $deptId) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'This student does not belong to your department.']);
            exit;
        }
        $levelId = $input['level_id'] ?? null;
        $status = $input['status'] ?? null;
        if ($status !== null && !in_array($status, ['regular','irregular'], true)) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Invalid status.']);
            exit;
        }
        // Validate level belongs to student's program if provided
        if ($levelId !== null && $levelId !== '') {
            $lvl = $this->levelService->getById((int)$levelId);
            if (!$lvl || (int)$lvl['program_id'] !== (int)$student['program_id']) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode(['message'=>'Selected level does not belong to student program.']);
                exit;
            }
        }
        $this->studentService->updateDetails($studentId, ['level_id'=>$levelId,'status'=>$status]);
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'message'=>'Student details updated successfully.']);
        exit;
    }

    public function destroyJson(): void
    {
        ensureDepartment();
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? '';
        if (!hash_equals(csrf_token(), $token)) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Invalid CSRF token.']);
            exit;
        }
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if (!$id && isset($_SERVER['REQUEST_URI']) && preg_match('#/api/department/enlistment/(\d+)#', $_SERVER['REQUEST_URI'], $m)) $id = (int)$m[1];
        if (!$id) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'enlistment id required']);
            exit;
        }
        $row = $this->enlistmentService->getById($id);
        if (!$row) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Enlistment not found.']);
            exit;
        }
        // Verify dept ownership via offering
        $stmt = \App\Core\Database::connection()->prepare('SELECT department_id FROM subject_offerings WHERE id=? LIMIT 1');
        $stmt->execute([$row['subject_offering_id']]);
        $off = $stmt->fetch();
        $user = auth();
        if ($off && (int)$off['department_id'] !== (int)$user['department_id']) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Forbidden.']);
            exit;
        }
        $this->enlistmentService->remove($id);
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'message'=>'Enlistment removed successfully.']);
        exit;
    }
}
