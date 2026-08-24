<?php

namespace App\Controllers\Student;

use App\Services\StudentPortalService;

class GradeController
{
    private StudentPortalService $svc;
    public function __construct(){ $this->svc = new StudentPortalService(); }

    public function index(): void
    {
        ensureStudent();
        $studentAccount = auth('student');
        $studentId = $this->svc->getStudentId();
        if (!$studentId) {
            flash('error','Student not found.');
            redirect(url('views/login/student.php'));
        }
        $data = $this->svc->getGrades($studentId);

        $pageTitle = 'My Grades';
        $pageSubheader = 'Grades per academic term';

        ob_start();
        require __DIR__ . '/../../../views/student/grades.view.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../../views/layouts/portal.php';
    }
}
