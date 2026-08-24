<?php
// Department Dashboard - expects $department, $stats from DashboardController
?>
<?php if (!empty($department)): ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">My Department</p>
            <p class="text-lg font-semibold text-slate-800"><?= e($department['code'] ?? '') ?> - <?= e($department['description'] ?? '') ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-500">Programs</p>
            <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800"><?= isset($stats['programs']) ? e($stats['programs']) : '--' ?></p>
        <p class="text-xs text-slate-400 mt-1">In this department</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-500">Subject Offerings</p>
            <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800"><?= isset($stats['offerings']) ? e($stats['offerings']) : '--' ?></p>
        <p class="text-xs text-slate-400 mt-1">Offered subjects</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-500">Students</p>
            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800"><?= isset($stats['students']) ? e($stats['students']) : '--' ?></p>
        <p class="text-xs text-slate-400 mt-1">Enrolled students</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-500">Teacher Loadings</p>
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8zM9 16a5 5 0 015 5H4a5 5 0 015-5z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-800"><?= isset($stats['teachers_loading']) ? e($stats['teachers_loading']) : '--' ?></p>
        <p class="text-xs text-slate-400 mt-1">Active loadings</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h3 class="text-base font-semibold text-slate-800 mb-1">Department Overview</h3>
    <p class="text-sm text-slate-500">Manage your department's teacher loadings, student enlistment, class lists and grade sheets.</p>
    <div class="mt-4 flex flex-wrap gap-2">
        <a href="<?= url('views/department/teacher-loadings/index.php') ?>" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Teacher Loadings</a>
        <a href="<?= url('views/department/enlistment/index.php') ?>" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg">Enlistment</a>
    </div>
</div>
