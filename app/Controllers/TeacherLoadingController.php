<?php

namespace App\Controllers;

use App\Services\AcademicTermService;
use App\Services\TeacherOfferingService;
use App\Services\TeacherService;

class TeacherLoadingController
{
    private TeacherOfferingService $teacherOfferingService;
    private TeacherService $teacherService;
    private AcademicTermService $academicTermService;

    public function __construct()
    {
        $this->teacherOfferingService = new TeacherOfferingService();
        $this->teacherService = new TeacherService();
        $this->academicTermService = new AcademicTermService();
    }

    public function index(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int) $user['department_id'];
        $teachers = $this->teacherOfferingService->getTeachersWithLoadings($deptId);
        $terms = $this->academicTermService->getForDropdownByDepartment($deptId);
        $termId = isset($_GET['term']) ? (int) $_GET['term'] : 0;

        $pageTitle = 'Teacher Loadings';
        $pageSubheader = 'Teachers with subject offerings assigned under your department';

        ob_start();
        require __DIR__ . '/../../views/department/teacher-loadings/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function show(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int) $user['department_id'];
        $teacherId = (int) ($_GET['teacher_id'] ?? $_GET['id'] ?? 0);
        if (!$teacherId) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (preg_match('#/views/department/teacher-loadings/show\.php#', $uri) && isset($_GET['id'])) $teacherId = (int)$_GET['id'];
        }
        // Try path ?teacher_id
        if (!$teacherId && isset($_GET['teacher'])) $teacherId = (int)$_GET['teacher'];
        if (!$teacherId) {
            flash('error','Missing teacher.');
            redirect(url('views/department/teacher-loadings/index.php'));
        }
        $teacher = $this->teacherService->getById($teacherId);
        if (!$teacher) {
            flash('error','Teacher not found.');
            redirect(url('views/department/teacher-loadings/index.php'));
        }
        $terms = $this->academicTermService->getForDropdownByDepartment($deptId);
        $termId = isset($_GET['term']) ? (int)$_GET['term'] : 0;
        $loadings = $termId ? $this->teacherOfferingService->getByTeacherAndTerm($teacherId, $termId, $deptId) : [];

        $pageTitle = 'Teacher Loadings';
        $pageSubheader = 'Subject offerings assigned to this teacher per academic term';

        // For display name
        $fullName = trim(($teacher['last_name'] ?? '') . ', ' . ($teacher['first_name'] ?? '') . ' ' . ($teacher['middle_name'] ?? ''));

        ob_start();
        require __DIR__ . '/../../views/department/teacher-loadings/show.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function classList(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int)$user['department_id'];
        $loadingId = (int)($_GET['loading_id'] ?? $_GET['id'] ?? 0);
        if (!$loadingId) { http_response_code(404); echo 'Missing loading'; exit; }
        $loading = $this->teacherOfferingService->getById($loadingId);
        if (!$loading) { http_response_code(404); echo 'Loading not found'; exit; }
        // Verify dept ownership via offering
        $stmt = \App\Core\Database::connection()->prepare('SELECT department_id, code FROM subject_offerings WHERE id=? LIMIT 1');
        $stmt->execute([$loading['offering_id']]);
        $off = $stmt->fetch();
        if (!$off || (int)$off['department_id'] !== $deptId) { http_response_code(403); echo 'Forbidden'; exit; }

        $teacher = $this->teacherService->getById((int)$loading['teacher_id']);
        $students = $this->teacherOfferingService->getEnrolledStudents((int)$loading['offering_id'], (int)$loading['academic_term_id']);
        // Group by sex
        $groups = ['male'=>['label'=>'Male','students'=>[]], 'female'=>['label'=>'Female','students'=>[]]];
        foreach ($students as $s) {
            $sex = strtolower($s['sex'] ?? 'male');
            if (!isset($groups[$sex])) $groups[$sex]=['label'=>ucfirst($sex),'students'=>[]];
            $groups[$sex]['students'][] = $s;
        }
        $total = count($students);
        // Resolve term and offering details for header
        $termStmt = \App\Core\Database::connection()->prepare('SELECT * FROM academic_terms WHERE id=? LIMIT 1');
        $termStmt->execute([$loading['academic_term_id']]);
        $term = $termStmt->fetch();
        $offStmt = \App\Core\Database::connection()->prepare('SELECT so.*, s.code AS subject_code, s.description AS subject_description, p.code AS program_code FROM subject_offerings so LEFT JOIN subjects s ON s.id=so.subject_id LEFT JOIN programs p ON p.id=so.program_id WHERE so.id=? LIMIT 1');
        $offStmt->execute([$loading['offering_id']]);
        $offeringFull = $offStmt->fetch();

        $pageTitle = 'Class List';
        $pageSubheader = $offeringFull['code'] ?? $off['code'] ?? 'Class List';
        ob_start();
        require __DIR__ . '/../../views/department/teacher-loadings/class-list.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    public function gradeSheet(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int)$user['department_id'];
        $loadingId = (int)($_GET['loading_id'] ?? $_GET['id'] ?? 0);
        if (!$loadingId) { http_response_code(404); echo 'Missing loading'; exit; }
        $loading = $this->teacherOfferingService->getById($loadingId);
        if (!$loading) { http_response_code(404); echo 'Loading not found'; exit; }
        // Verify dept
        $stmt = \App\Core\Database::connection()->prepare('SELECT department_id FROM subject_offerings WHERE id=? LIMIT 1');
        $stmt->execute([$loading['offering_id']]);
        $off = $stmt->fetch();
        if (!$off || (int)$off['department_id'] !== $deptId) { http_response_code(403); echo 'Forbidden'; exit; }
        $data = $this->teacherOfferingService->getGradeSheetData($loadingId);
        $teacher = $this->teacherService->getById((int) $loading['teacher_id']);
        $termStmt = \App\Core\Database::connection()->prepare('SELECT * FROM academic_terms WHERE id=? LIMIT 1');
        $termStmt->execute([$loading['academic_term_id']]);
        $term = $termStmt->fetch();
        // Provide $loading for view's back links (class-list.view uses $loading, grade-sheet.view uses $loading inside data)
        $loading = $loading; // keep

        $pageTitle = 'Grade Sheet';
        $pageSubheader = $data['offering']['offering_code'] ?? $off['code'] ?? 'Grade Sheet';
        ob_start();
        require __DIR__ . '/../../views/department/teacher-loadings/grade-sheet.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../views/layouts/portal.php';
    }

    // API JSON

    public function searchTeachersJson(): void
    {
        ensureDepartment();
        $q = trim($_GET['q'] ?? '');
        $teachers = $this->teacherService->searchTeachers($q);
        header('Content-Type: application/json');
        echo json_encode($teachers);
        exit;
    }

    public function offeringsByTermJson(): void
    {
        ensureDepartment();
        $user = auth();
        $deptId = (int)$user['department_id'];
        $termId = (int)($_GET['academic_term_id'] ?? 0);
        $teacherId = isset($_GET['teacher_id']) && $_GET['teacher_id']!=='' ? (int)$_GET['teacher_id'] : null;
        if (!$termId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'academic_term_id required']);
            exit;
        }
        $offerings = $this->teacherOfferingService->getOfferingsByTerm($termId, $deptId, $teacherId);
        header('Content-Type: application/json');
        echo json_encode($offerings);
        exit;
    }

    public function storeJson(): void
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
        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        $termId = (int)($_POST['academic_term_id'] ?? 0);
        $offeringId = (int)($_POST['offering_id'] ?? 0);
        if (!$teacherId || !$termId || !$offeringId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'teacher_id, academic_term_id and offering_id are required.']);
            exit;
        }
        // Verify offering belongs to dept
        $stmt = \App\Core\Database::connection()->prepare('SELECT department_id FROM subject_offerings WHERE id=? LIMIT 1');
        $stmt->execute([$offeringId]);
        $off = $stmt->fetch();
        if (!$off || (int)$off['department_id'] !== $deptId) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'The selected subject offering does not belong to your department.']);
            exit;
        }
        try {
            $loading = $this->teacherOfferingService->create(['teacher_id'=>$teacherId,'offering_id'=>$offeringId,'academic_term_id'=>$termId]);
        } catch (\Throwable $e) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>$e->getMessage()]);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['message'=>'Teacher loading created successfully.','loading'=>$loading]);
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
        if (!$id && preg_match('#/api/department/teacher-loadings/(\d+)#', $_SERVER['REQUEST_URI'] ?? '', $m)) $id = (int)$m[1];
        if (!$id) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'id required']);
            exit;
        }
        $row = $this->teacherOfferingService->getById($id);
        if (!$row) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Loading not found']);
            exit;
        }
        // verify dept
        $stmt = \App\Core\Database::connection()->prepare('SELECT department_id FROM subject_offerings WHERE id=? LIMIT 1');
        $stmt->execute([$row['offering_id']]);
        $off = $stmt->fetch();
        $user = auth();
        if ($off && (int)$off['department_id'] !== (int)$user['department_id']) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['message'=>'Forbidden']);
            exit;
        }
        $this->teacherOfferingService->delete($id);
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'message'=>'Teacher loading deleted successfully.']);
        exit;
    }
}
