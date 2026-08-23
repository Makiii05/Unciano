<?php
$authUser = require_auth();
$currentUser = $authUser;
$type = $currentUser['type'] ?? ($_SESSION['student_id'] ? 'student' : ($_SESSION['teacher_id'] ? 'teacher' : ''));

$portalTitle = match($type) {
    'admin' => 'Admin Portal',
    'registrar' => 'Registrar Portal',
    'accounting' => 'Accounting Portal',
    'admission' => 'Admission Portal',
    'department' => 'Department Portal',
    'student' => 'Student Portal',
    'teacher' => 'Teacher Portal',
    default => 'Portal',
};

$displayName = $currentUser['name'] ?? $currentUser['first_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Dashboard') ?> - UCA Nexus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe',
                            300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6',
                            600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a'
                        },
                        sidebar: { DEFAULT: '#0f172a', hover: '#1e293b', active: '#1e40af' }
                    }
                }
            }
        }
    </script>
    <style>
        dialog.modal[open] {
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            padding: 1rem;
            margin: 0;
            width: 100%;
            height: 100%;
            max-width: 100vw;
            max-height: 100vh;
        }
        dialog.modal::backdrop {
            background: rgba(0, 0, 0, 0.4);
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-sidebar text-white flex flex-col shrink-0">
            <div class="p-5 border-b border-white/10">
                <h1 class="text-xl font-bold tracking-tight">
                    <span class="text-primary-400">UCA</span>
                    <span class="text-white/80">Nexus</span>
                </h1>
                <p class="text-xs text-white/50 mt-1 capitalize"><?= e($portalTitle) ?></p>
            </div>

            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <?php
                $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
                $base = '/laravel_project/unciano/views';

                $sidebar = match($type) {
                    'admin' => [
                        ['label' => 'Dashboard', 'url' => '/dashboard.php', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['label' => 'Accounts', 'url' => '/admin/accounts/index.php', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'],
                    ],
                    'registrar' => [
                        ['section' => 'General', 'items' => [
                            ['label' => 'Dashboard', 'url' => '/dashboard.php', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ]],
                        ['section' => 'Academic', 'items' => [
                            ['label' => 'Departments', 'url' => '/registrar/departments/index.php', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                            ['label' => 'Programs', 'url' => '/registrar/programs/index.php', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
                            ['label' => 'Levels', 'url' => '/registrar/levels/index.php', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                            ['label' => 'Curricula', 'url' => '/registrar/curricula/index.php', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                            ['label' => 'School Years', 'url' => '/registrar/school-years/index.php', 'icon' => 'M9 12l2 2 4-6M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                            ['label' => 'Academic Terms', 'url' => '/registrar/academic-terms/index.php', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                            ['label' => 'Subjects', 'url' => '/registrar/subjects/index.php', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                            ['label' => 'Prospectus', 'url' => '/registrar/prospectus/index.php', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ]],
                        ['section' => 'Grading', 'items' => [
                            ['label' => 'Grading System', 'url' => '/registrar/grading-systems/index.php', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                            ['label' => 'Grading Components', 'url' => '/registrar/grading-components/index.php', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ]],
                        // ['section' => 'People', 'items' => [
                        //     ['label' => 'Teachers', 'url' => '/registrar/teachers/index.php', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        //     ['label' => 'Students', 'url' => '/registrar/students/index.php', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                        // ]],
                        // ['section' => 'Enrollment', 'items' => [
                        //     ['label' => 'Class List', 'url' => '/registrar/class-list/index.php', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                        // ]],
                        // ['section' => 'Grading', 'items' => [
                        //     ['label' => 'Grade Approval', 'url' => '/registrar/grade-approval/index.php', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        //     ['label' => 'Grade Report', 'url' => '/registrar/grade-report/index.php', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        // ]],
                    ],
                    'accounting' => [
                        ['section' => 'General', 'items' => [
                            ['label' => 'Dashboard', 'url' => '/dashboard.php', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ]],
                        ['section' => 'Payments', 'items' => [
                            ['label' => 'Cashier', 'url' => '/accounting/cashier/index.php', 'icon' => 'M3 10h18M7 15h2m4 0h2M7 7h10a2 2 0 012 2v8a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2zM5 13h14'],
                            // ['label' => 'Payment Details', 'url' => '/accounting/payment-details/index.php', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                        ]],
                        // ['section' => 'Billing', 'items' => [
                        //     ['label' => 'Fees', 'url' => '/accounting/fees/index.php', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        //     ['label' => 'Assessment', 'url' => '/accounting/assessment/index.php', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        // ]],
                    ],
                    'admission' => [
                        ['section' => 'General', 'items' => [
                            ['label' => 'Dashboard', 'url' => '/dashboard.php', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                            // ['label' => 'Applicants', 'url' => '/admission/applicants/index.php', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                            // ['label' => 'Schedules', 'url' => '/admission/schedules/index.php', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ]],
                        // ['section' => 'Admission Process', 'items' => [
                        //     ['label' => 'Interviews', 'url' => '/admission/interviews/index.php', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                        //     ['label' => 'Examinations', 'url' => '/admission/examinations/index.php', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        //     ['label' => 'Final Evaluations', 'url' => '/admission/evaluations/index.php', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        //     ['label' => 'Admitted Students', 'url' => '/admission/admitted/index.php', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222'],
                        // ]],
                    ],
                    'department' => [
                        ['section' => 'General', 'items' => [
                            ['label' => 'Dashboard', 'url' => '/dashboard.php', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ]],
                        ['section' => 'Academic', 'items' => [
                            ['label' => 'Subject Offerings', 'url' => '/department/subject-offerings/index.php', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                            ['label' => 'Teacher Loadings', 'url' => '/department/teacher-loadings/index.php', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                        ]],
                        ['section' => 'People', 'items' => [
                            ['label' => 'Students', 'url' => '/department/students/index.php', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                        ]],
                        ['section' => 'Enrollment', 'items' => [
                            ['label' => 'Enlistment', 'url' => '/department/enlistment/index.php', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                        ]],
                    ],
                    'student' => [
                        ['section' => 'General', 'items' => [
                            ['label' => 'Dashboard', 'url' => '/student/index.php', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ]],
                        ['section' => 'Academic', 'items' => [
                            // ['label' => 'Subjects', 'url' => '/student/subjects.php', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                            ['label' => 'Grades', 'url' => '/student/grades.php', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        ]],
                        // ['section' => 'Finance', 'items' => [
                        //     ['label' => 'Ledger', 'url' => '/student/ledger.php', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        // ]],
                        ['section' => 'Account', 'items' => [
                            // ['label' => 'Examination Permit', 'url' => '/student/permit.php', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                            ['label' => 'Change Password', 'url' => '/student/password.php', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                        ]],
                    ],
                    'teacher' => [
                        ['section' => 'General', 'items' => [
                            ['label' => 'Subject Load', 'url' => '/teacher/index.php', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                        ]],
                        ['section' => 'Account', 'items' => [
                            ['label' => 'Change Password', 'url' => '/teacher/password.php', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                        ]],
                    ],
                    default => [],
                };
                ?>

                <?php foreach ($sidebar as $group): ?>
                    <?php if (isset($group['section'])): ?>
                        <div class="pt-3 pb-1">
                            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-white/40"><?= e($group['section']) ?></p>
                        </div>
                        <?php foreach ($group['items'] as $item): ?>
                            <?php
                            $itemUrl = $base . $item['url'];
                            $isActive = str_contains($currentUrl, $item['url']);
                            ?>
                            <a href="<?= e($itemUrl) ?>"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 <?= $isActive ? 'bg-sidebar-active text-white' : 'text-white/70 hover:bg-sidebar-hover hover:text-white' ?>">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= e($item['icon']) ?>"/>
                                </svg>
                                <span><?= e($item['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php
                        $itemUrl = $base . $group['url'];
                        $isActive = str_contains($currentUrl, $group['url']);
                        ?>
                        <a href="<?= e($itemUrl) ?>"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 <?= $isActive ? 'bg-sidebar-active text-white' : 'text-white/70 hover:bg-sidebar-hover hover:text-white' ?>">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= e($group['icon']) ?>"/>
                            </svg>
                            <span><?= e($group['label']) ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <div class="p-3 border-t border-white/10">
                <form method="POST" action="<?= url('views/login/logout.php') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/50 hover:bg-sidebar-hover hover:text-white transition-colors duration-150 w-full">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800"><?= e($pageTitle ?? 'Dashboard') ?></h2>
                    <?php if (!empty($pageSubheader)): ?>
                        <p class="text-sm text-slate-500"><?= e($pageSubheader) ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm font-medium text-slate-700 capitalize"><?= e($displayName) ?></p>
                        <p class="text-xs text-slate-400 capitalize"><?= e($type) ?></p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-bold">
                        <?= strtoupper(substr($displayName, 0, 1)) ?>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6">
                <?php require __DIR__ . '/../includes/flash.php'; ?>
                <?= $content ?>
            </div>
        </main>
    </div>

    <script>
        document.querySelectorAll('dialog.modal').forEach(d => {
            d.addEventListener('click', function(e) {
                if (e.target === this) this.close();
            });
        });
    </script>
</body>
</html>
