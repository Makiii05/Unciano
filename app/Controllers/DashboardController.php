<?php

namespace App\Controllers;

class DashboardController
{
    public function index(): void
    {
        // Support web (staff) and teacher guards for dashboard
        if (isset($_SESSION['teacher_id'])) {
            $user = require_auth('teacher');
            $type = 'teacher';
        } elseif (isset($_SESSION['student_id'])) {
            $user = require_auth('student');
            $type = 'student';
        } else {
            $user = require_auth();
            $type = $user['type'] ?? ($_SESSION['student_id'] ? 'student' : ($_SESSION['teacher_id'] ? 'teacher' : ''));
        }

        $pageTitle = 'Dashboard';
        $pageSubheader = match ($type) {
            'department' => 'Department overview',
            'registrar' => 'Registrar overview',
            'admin' => 'System overview',
            default => null,
        };

        // Department-specific stats (filtered by department_id)
        $department = null;
        $stats = null;
        $teacherStats = null;
        $teacher = null;
        if ($type === 'department' && !empty($user['department_id'])) {
            $deptId = (int) $user['department_id'];
            try {
                $db = \App\Core\Database::connection();
                $deptStmt = $db->prepare('SELECT * FROM departments WHERE id = ? LIMIT 1');
                $deptStmt->execute([$deptId]);
                $department = $deptStmt->fetch() ?: null;

                $stats = [
                    'programs' => (int) $db->query("SELECT COUNT(*) FROM programs WHERE department_id = {$deptId}")->fetchColumn(),
                    'levels' => (int) $db->query("SELECT COUNT(*) FROM levels l JOIN programs p ON p.id=l.program_id WHERE p.department_id={$deptId}")->fetchColumn(),
                    'students' => (int) $db->query("SELECT COUNT(*) FROM students WHERE department_id={$deptId}")->fetchColumn(),
                    'offerings' => (int) $db->query("SELECT COUNT(*) FROM subject_offerings WHERE department_id={$deptId}")->fetchColumn(),
                    'enlistments' => (int) $db->query("SELECT COUNT(*) FROM enlistments e JOIN subject_offerings so ON so.id=e.subject_offering_id WHERE so.department_id={$deptId}")->fetchColumn(),
                    'teachers_loading' => (int) $db->query("SELECT COUNT(DISTINCT teacher_id) FROM teacher_offerings to2 JOIN subject_offerings so2 ON so2.id=to2.offering_id WHERE so2.department_id={$deptId}")->fetchColumn(),
                ];
            } catch (\Throwable $e) {
                $stats = null;
            }
        }

        // Teacher stats when logged as teacher (fallback check session)
        if ($type === 'teacher' || isset($_SESSION['teacher_id'])) {
            try {
                $tAuth = auth('teacher');
                if ($tAuth) {
                    $teacher = $tAuth;
                    $teacherId = (int) ($tAuth['teacher_id'] ?? 0);
                    if ($teacherId) {
                        $db = \App\Core\Database::connection();
                        $teacherStats = [
                            'loadings' => (int) $db->query("SELECT COUNT(*) FROM teacher_offerings WHERE teacher_id={$teacherId}")->fetchColumn(),
                            'students' => (int) $db->query("SELECT COUNT(DISTINCT e.student_id) FROM enlistments e JOIN teacher_offerings to2 ON to2.offering_id=e.subject_offering_id AND to2.academic_term_id=e.academic_term_id WHERE to2.teacher_id={$teacherId}")->fetchColumn(),
                            'terms' => (int) $db->query("SELECT COUNT(DISTINCT academic_term_id) FROM teacher_offerings WHERE teacher_id={$teacherId}")->fetchColumn(),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                $teacherStats = null;
            }
        }

        // Provide $currentUser for view compatibility (teacher fragment expects it)
        $currentUser = $user;
        // Also ensure $teacher is set for teacher dashboard fallback
        if ($type === 'teacher' && !isset($teacher) && isset($currentUser)) {
            $teacher = $currentUser;
        }

        ob_start();
        require __DIR__ . '/../../views/' . $type . '/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../views/layouts/portal.php';
    }
}
