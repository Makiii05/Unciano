<?php
// expects $data from GradeController: student, enlistmentsByTerm, termsById
$enlistmentsByTerm = $data['enlistmentsByTerm'] ?? [];
$termsById = $data['termsById'] ?? [];
$student = $data['student'] ?? null;
?>

<?php if (empty($enlistmentsByTerm)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center text-slate-400">
        No grades recorded yet.
    </div>
<?php else: ?>
    <?php foreach ($enlistmentsByTerm as $termId => $enlistments): $term = $termsById[$termId] ?? null; ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-800"><?= e($term['code'] ?? 'Unknown Term') ?></h3>
                    <p class="text-xs text-slate-400"><?= e($term['type'] ?? '') ?> <?= isset($term['description']) ? ' - ' . e($term['description']) : '' ?></p>
                </div>
                <span class="px-3 py-1 bg-slate-100 rounded-full text-xs font-medium text-slate-600"><?= count($enlistments) ?> subject(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="px-6 py-3.5 text-left font-semibold w-28">Subject Code</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Description</th>
                            <th class="px-6 py-3.5 text-right font-semibold w-24">Units</th>
                            <th class="px-6 py-3.5 text-right font-semibold w-32">Final Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enlistments as $e): ?>
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="px-6 py-3.5 font-mono text-sm text-slate-800"><?= e($e['subject_code'] ?? '—') ?></td>
                                <td class="px-6 py-3.5 text-sm text-slate-800"><?= e($e['subject_description'] ?? '—') ?></td>
                                <td class="px-6 py-3.5 text-right font-medium text-slate-800"><?= e($e['unit'] ?? '—') ?></td>
                                <td class="px-6 py-3.5 text-right font-semibold text-slate-800">
                                    <?php if ($e['final_grade'] !== null && $e['final_grade'] !== ''): ?>
                                        <span class="px-2 py-1 rounded <?= ((float)$e['final_grade'] <= 3.00 && (float)$e['final_grade'] >= 1.00) ? 'bg-green-100 text-green-800' : ((float)$e['final_grade'] > 3.00 ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-600') ?>">
                                            <?= e(number_format((float)$e['final_grade'], 2)) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($student)): ?>
<div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-500">
    Student: <?= e($student['student_number'] ?? '') ?> - <?= e(trim(($student['last_name'] ?? '').', '.($student['first_name'] ?? ''))) ?> | Program: <?= e($student['program_code'] ?? '') ?> | Level: <?= e($student['level_description'] ?? '') ?>
</div>
<?php endif; ?>
