<?php
$teacherName = trim(($teacher['last_name'] ?? '') . ', ' . ($teacher['first_name'] ?? '') . ' ' . ($teacher['middle_name'] ?? ''));
?>
<div class="mb-4 flex items-center justify-between flex-wrap gap-2">
    <a href="<?= url('views/department/teacher-loadings/show.php?teacher_id=' . (int) $teacher['id'] . ($term['id'] ? '&term='.$term['id'] : '')) ?>" class="inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Loadings
    </a>
    <div class="flex gap-2 no-print">
        <a href="<?= url('views/department/teacher-loadings/pdf/grade-sheet.php?loading_id=' . (int) $loading['id']) ?>" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg">Print PDF</a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div><p class="text-slate-400 text-xs uppercase tracking-wider">Offering</p><p class="font-mono font-medium text-slate-800"><?= e($data['offering']['offering_code'] ?? $data['offering']['code'] ?? '') ?> — <?= e($data['offering']['subject_description'] ?? '') ?></p></div>
        <div><p class="text-slate-400 text-xs uppercase tracking-wider">Teacher</p><p class="font-medium text-slate-800"><?= e($teacherName) ?> (<?= e($teacher['code'] ?? '') ?>)</p></div>
        <div><p class="text-slate-400 text-xs uppercase tracking-wider">Academic Term</p><p class="font-medium text-slate-800"><?= e($term['description'] ?? '') ?> (<?= e($data['period'] ?? 'final') ?>)</p></div>
        <div><p class="text-slate-400 text-xs uppercase tracking-wider">Total Students</p><p class="font-bold text-slate-800"><?= array_sum(array_map(fn($g)=>count($g['students']), $data['groups'])) ?></p></div>
    </div>
    <p class="text-xs text-slate-400 mt-3">Hardcoded 4 periods: prelim, midterm, prefinal, final. Final grade is period_grade (approved).</p>
</div>

<?php foreach ($data['groups'] as $key => $group): if (empty($group['students'])) continue; ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
    <div class="px-6 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
        <h4 class="text-sm font-semibold text-slate-700"><?= e($group['label']) ?> (<?= count($group['students']) ?>)</h4>
        <span class="text-xs text-slate-400">Period: <?= e($data['period']) ?></span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                <th class="px-6 py-3 text-left">#</th>
                <th class="px-6 py-3 text-left">Student No</th>
                <th class="px-6 py-3 text-left">Name</th>
                <th class="px-6 py-3 text-center">Final Grade</th>
            </tr></thead>
            <tbody>
                <?php $i=1; foreach ($group['students'] as $s): ?>
                    <tr class="border-t border-slate-100">
                        <td class="px-6 py-2 text-sm text-slate-600"><?= $i++ ?></td>
                        <td class="px-6 py-2 font-mono text-sm text-slate-800"><?= e($s['number'] ?? $s['student_number'] ?? '') ?></td>
                        <td class="px-6 py-2 text-sm text-slate-800"><?= e($s['name'] ?? trim(($s['last_name']??'').', '.($s['first_name']??''))) ?></td>
                        <td class="px-6 py-2 text-center font-medium text-slate-800"><?= isset($s['grade']) && $s['grade']!==null ? e(number_format((float)$s['grade'],2)) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<style>@media print{ header, aside, .no-print{ display:none !important; } body{ background:white; } }</style>
