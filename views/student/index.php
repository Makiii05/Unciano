<?php
// Student Dashboard - expects $currentUser/$user from DashboardController or portal
if (!isset($currentUser) && isset($user)) { $currentUser = $user; }
if (!isset($currentUser) && isset($student) && is_array($student)) { $currentUser = $student; }
$student = $student ?? $currentUser ?? $user ?? null;
if (!is_array($student)) { $student = []; }
// Direct access guard: if rendered without portal (no Tailwind), redirect to dashboard router
if (!isset($content) && php_sapi_name() !== 'cli') {
    $isDirect = basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'index.php' && str_contains($_SERVER['REQUEST_URI'] ?? '', '/views/student/index.php');
    if ($isDirect) {
        require_once __DIR__ . '/../../bootstrap.php';
        if (!headers_sent()) { header('Location: ' . url('views/dashboard.php')); exit; }
    }
}
$studentName = trim(($student['first_name'] ?? $student['name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-slate-500">Enrolled Subjects</p>
                <p class="text-2xl font-bold text-slate-800">--</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-slate-500">Balance</p>
                <p class="text-2xl font-bold text-slate-800">--</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h3 class="text-lg font-semibold text-slate-800 mb-2">Welcome, <?= e($studentName) ?></h3>
    <p class="text-slate-500">View your subjects, grades, financial ledger, and examination permit.</p>
</div>
