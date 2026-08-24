<?php
// Teacher Dashboard - expects $teacher, $teacherStats from DashboardController
// Safe fallback for direct access or undefined variables (avoids Undefined variable warning)
if (!isset($teacher) && isset($currentUser)) { $teacher = $currentUser; }
if (!isset($teacher) && isset($user)) { $teacher = $user; }
if (!isset($teacher)) { $teacher = null; }
if (!is_array($teacher)) { $teacher = []; }
$teacherName = trim(($teacher['first_name'] ?? $teacher['name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''));
$code = $teacher['code'] ?? '';
// If accessed directly without portal layout, delegate to dashboard controller for proper layout + Tailwind
if (!isset($teacherStats) && !isset($content) && php_sapi_name() !== 'cli') {
    // Check if we are in a direct HTTP request to this file (not included via DashboardController)
    $isDirect = basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'index.php' && str_contains($_SERVER['REQUEST_URI'] ?? '', '/views/teacher/index.php');
    if ($isDirect) {
        require_once __DIR__ . '/../../bootstrap.php';
        // Avoid recursion if already in dashboard
        if (!headers_sent()) { header('Location: ' . url('views/dashboard.php')); exit; }
    }
}
?>
<?php if (!empty($teacher['email']) || !empty($code)): ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-primary-600 text-white flex items-center justify-center text-lg font-bold"><?= strtoupper(substr($teacherName ?: 'T',0,1)) ?></div>
        <div>
            <p class="text-lg font-semibold text-slate-800"><?= e($teacherName ?: 'Teacher') ?> <?= $code ? '(' . e($code) . ')' : '' ?></p>
            <p class="text-sm text-slate-500"><?= e($teacher['email'] ?? '') ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-500">Subject Loads</p>
            <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800"><?= isset($teacherStats['loadings']) ? e($teacherStats['loadings']) : '--' ?></p>
        <p class="text-xs text-slate-400 mt-1">Assigned subjects</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-500">Students</p>
            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800"><?= isset($teacherStats['students']) ? e($teacherStats['students']) : '--' ?></p>
        <p class="text-xs text-slate-400 mt-1">Enrolled in your loads</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-500">Academic Terms</p>
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800"><?= isset($teacherStats['terms']) ? e($teacherStats['terms']) : '--' ?></p>
        <p class="text-xs text-slate-400 mt-1">Active terms</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h3 class="text-lg font-semibold text-slate-800 mb-2">Welcome, <?= e($teacherName ?: 'Teacher') ?></h3>
    <p class="text-slate-500 mb-4">View your subject load, class lists, and input grades. Use the sidebar to navigate.</p>
    <a href="<?= url('views/teacher/subject-load/index.php') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Go to Subject Load</a>
</div>
