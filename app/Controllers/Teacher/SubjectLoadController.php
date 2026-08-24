<?php

namespace App\Controllers\Teacher;

use App\Services\TeacherPortalService;

class SubjectLoadController
{
    private TeacherPortalService $svc;
    public function __construct(){ $this->svc = new TeacherPortalService(); }

    public function index(): void
    {
        ensureTeacher();
        $teacherId = $this->svc->getTeacherId();
        if (!$teacherId) { flash('error','Teacher not found.'); redirect(url('views/login/teacher.php')); }
        $termId = isset($_GET['term_id']) ? (int)$_GET['term_id'] : null;
        if (!$termId && isset($_GET['term'])) $termId = (int)$_GET['term'];
        $data = $this->svc->getSubjectLoad($teacherId, $termId);
        $pageTitle = 'Subject Load';
        $pageSubheader = $data['teacher'] ? trim(($data['teacher']['last_name'] ?? '').', '.($data['teacher']['first_name'] ?? '')) : 'Your assigned subjects';
        ob_start();
        require __DIR__ . '/../../../views/teacher/subject-load/index.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../../views/layouts/portal.php';
    }

    public function classList(): void
    {
        ensureTeacher();
        $teacherId = $this->svc->getTeacherId();
        $loadingId = (int) ($_GET['loading_id'] ?? $_GET['id'] ?? 0);
        if (isset($_GET['teacher_offering'])) $loadingId = (int)$_GET['teacher_offering'];
        if (!$loadingId) { http_response_code(404); echo 'Missing loading'; exit; }
        $data = $this->svc->getClassList($loadingId, $teacherId);
        $pageTitle = 'Class List';
        $pageSubheader = $data['offering']['offering_code'] ?? 'Enrolled students';
        ob_start();
        require __DIR__ . '/../../../views/teacher/subject-load/class-list.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../../views/layouts/portal.php';
    }

    public function inputGrade(): void
    {
        ensureTeacher();
        $teacherId = $this->svc->getTeacherId();
        $loadingId = (int) ($_GET['loading_id'] ?? $_GET['id'] ?? 0);
        if (isset($_GET['teacher_offering'])) $loadingId = (int)$_GET['teacher_offering'];
        if (!$loadingId) { http_response_code(404); echo 'Missing loading'; exit; }

        // verify ownership and load data
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT * FROM teacher_offerings WHERE id=? AND teacher_id=? LIMIT 1');
        $stmt->execute([$loadingId, $teacherId]);
        $to = $stmt->fetch();
        if (!$to) { flash('error','Unauthorized.'); redirect(url('views/teacher/subject-load/index.php')); }

        $period = $_GET['period'] ?? 'prelim';
        $allowed = ['prelim','midterm','prefinal','final'];
        if (!in_array($period, $allowed, true)) $period = 'prelim';

        // Load offering + subject education_level for period options (hardcode 4)
        $stmt2 = $db->prepare("SELECT to2.*, so.code AS offering_code, so.description AS offering_desc, s.code AS subject_code, s.description AS subject_description, s.education_level, p.code AS program_code, l.description AS level_description, at.code AS term_code, so.department_id FROM teacher_offerings to2 JOIN subject_offerings so ON so.id=to2.offering_id LEFT JOIN subjects s ON s.id=so.subject_id LEFT JOIN programs p ON p.id=so.program_id LEFT JOIN levels l ON l.id=so.level_id LEFT JOIN academic_terms at ON at.id=to2.academic_term_id WHERE to2.id=? LIMIT 1");
        $stmt2->execute([$loadingId]);
        $offering = $stmt2->fetch();
        $deptId = $offering['department_id'] ?? null;

        // Grading systems for dept
        $gradingSystems = [];
        $components = [];
        if ($deptId) {
            $gsSvc = new \App\Services\GradingSystemService();
            $gradingSystems = $gsSvc->getByDepartment((int)$deptId);
            $compSvc = new \App\Services\ComponentService();
            $components = $compSvc->getByDepartment((int)$deptId);
        }

        // Seed data for grades: ensure grade rows exist per period? We'll create minimal seed
        // Ensure student list exists
        $studentsStmt = $db->prepare("SELECT s.id, s.student_number, s.last_name, s.first_name, s.middle_name FROM students s JOIN enlistments e ON e.student_id=s.id WHERE e.subject_offering_id=? AND e.academic_term_id=? ORDER BY s.last_name, s.first_name");
        $studentsStmt->execute([$to['offering_id'], $to['academic_term_id']]);
        $enrolledStudents = $studentsStmt->fetchAll();

        // Check if grades input exists to determine guard
        $hasGrades = $this->svc->hasGradesForOffering($loadingId);

        // Load columns for this period
        $colStmt = $db->prepare("SELECT gc.*, c.code AS component_code, c.description AS comp_desc, c.percentage FROM grade_column gc JOIN components c ON c.id=gc.component_id WHERE gc.teacher_offering_id=? AND gc.period=? ORDER BY gc.column_number");
        $colStmt->execute([$loadingId, $period]);
        $columns = $colStmt->fetchAll();

        // Load grades for period
        $gradeStmt = $db->prepare("SELECT g.*, s.student_number, s.last_name, s.first_name, s.middle_name FROM grade g JOIN students s ON s.id=g.student_id WHERE g.teacher_offering_id=? AND g.period=? ORDER BY s.last_name");
        $gradeStmt->execute([$loadingId, $period]);
        $grades = $gradeStmt->fetchAll();
        // If no grades yet but students enrolled, create draft rows (like ensureGradeRows) but don't auto-create to avoid guard false positive. We'll leave creation on input.

        // Effective grading system
        $effectiveId = $to['grading_id'] ?? $offering['grading_id'] ?? null;
        // Need to fetch components for effective system
        $effectiveComponents = [];
        if ($effectiveId) {
            $gs = $db->prepare('SELECT gs.*, c.id AS cid, c.code, c.description, c.percentage FROM grading_systems gs JOIN grading_components gc ON gc.grading_id=gs.id JOIN components c ON c.id=gc.component_id WHERE gs.id=?');
            $gs->execute([$effectiveId]);
            $effectiveComponents = $gs->fetchAll();
        }

        $data = [
            'teacherOffering' => $to,
            'offering' => $offering,
            'period' => $period,
            'periods' => $allowed,
            'gradingSystems' => $gradingSystems,
            'components' => $components,
            'effectiveComponents' => $effectiveComponents,
            'students' => $enrolledStudents,
            'grades' => $grades,
            'columns' => $columns,
            'hasGrades' => $hasGrades,
            'gradeInputLocked' => ($to['grade_input_status'] ?? 'unlocked') !== 'unlocked',
        ];

        $pageTitle = $offering['offering_code'] ?? 'Input Grade';
        $pageSubheader = ($offering['subject_code'] ?? '') . ' - ' . ($offering['subject_description'] ?? '') . ' | ' . ($offering['program_code'] ?? '') . ' | ' . ($offering['level_description'] ?? '');
        ob_start();
        require __DIR__ . '/../../../views/teacher/subject-load/grades.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../../views/layouts/portal.php';
    }
}
