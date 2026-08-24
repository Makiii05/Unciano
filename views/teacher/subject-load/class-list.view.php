<div class="mb-4">
    <a href="<?= url('views/teacher/subject-load/index.php') ?>" class="inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Subject Load
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div><p class="text-slate-400 text-xs uppercase tracking-wider">Section</p><p class="font-mono font-medium text-slate-800"><?= e($data['offering']['offering_code'] ?? '—') ?></p></div>
        <div><p class="text-slate-400 text-xs uppercase tracking-wider">Subject</p><p class="font-medium text-slate-800"><?= e($data['offering']['subject_code'] ?? '') ?> - <?= e($data['offering']['subject_description'] ?? '') ?></p></div>
        <div><p class="text-slate-400 text-xs uppercase tracking-wider">Total Students</p><p class="font-bold text-slate-800"><?= e($data['total']) ?></p></div>
    </div>
</div>

<?php foreach ($data['groups'] as $key=>$group): if (empty($group['students'])) continue; ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
    <div class="px-6 py-3 bg-slate-50 border-b border-slate-200">
        <h4 class="text-sm font-semibold text-slate-700"><?= e($group['label']) ?> (<?= count($group['students']) ?>)</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                <th class="px-6 py-3 text-left">#</th>
                <th class="px-6 py-3 text-left">Student No</th>
                <th class="px-6 py-3 text-left">Name</th>
                <th class="px-6 py-3 text-left">Sex</th>
            </tr></thead>
            <tbody>
                <?php $i=1; foreach ($group['students'] as $s): $name=trim(($s['last_name']??'').', '.($s['first_name']??'').' '.($s['middle_name']??'')); ?>
                    <tr class="border-t border-slate-100"><td class="px-6 py-2 text-sm text-slate-600"><?= $i++ ?></td><td class="px-6 py-2 font-mono text-sm text-slate-800"><?= e($s['student_number'] ?? '') ?></td><td class="px-6 py-2 text-sm text-slate-800"><?= e($name) ?></td><td class="px-6 py-2 text-sm text-slate-600"><?= e(ucfirst($s['sex']??'')) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<?php if ($data['total']===0): ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 py-12 text-center text-slate-400">No students enrolled.</div>
<?php endif; ?>
