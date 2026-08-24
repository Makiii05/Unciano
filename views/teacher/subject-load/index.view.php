<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <form method="GET" action="<?= url('views/teacher/subject-load/index.php') ?>" class="flex flex-wrap items-end gap-4">
        <div class="w-64">
            <label class="block text-sm font-medium text-slate-700 mb-1">Academic Term</label>
            <select name="term_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-white" onchange="this.form.submit()">
                <option value="">Select academic term</option>
                <?php foreach ($data['terms'] as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= $data['term'] && (int)$t['id']===(int)$data['term']['id'] ? 'selected' : '' ?>><?= e($t['code']) ?> - <?= e($t['description'] ?? $t['code']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                <th class="px-6 py-3.5 text-left">Section</th>
                <th class="px-6 py-3.5 text-left">Subject Code</th>
                <th class="px-6 py-3.5 text-left">Description</th>
                <th class="px-6 py-3.5 text-left">Program</th>
                <th class="px-6 py-3.5 text-left">Level</th>
                <th class="px-6 py-3.5 text-right w-48">Actions</th>
            </tr></thead>
            <tbody>
                <?php if (empty($data['loadings'])): ?>
                    <tr class="border-t border-slate-100"><td colspan="6" class="px-6 py-12 text-center text-slate-400">No subject loadings for the selected academic term.</td></tr>
                <?php else: foreach ($data['loadings'] as $ld): ?>
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-6 py-3.5 font-mono text-sm text-slate-800"><?= e($ld['offering_code'] ?? '—') ?></td>
                        <td class="px-6 py-3.5 font-mono text-sm text-slate-800"><?= e($ld['subject_code'] ?? '—') ?></td>
                        <td class="px-6 py-3.5 text-slate-800"><?= e($ld['subject_description'] ?? '—') ?></td>
                        <td class="px-6 py-3.5 text-slate-800"><?= e($ld['program_code'] ?? '—') ?></td>
                        <td class="px-6 py-3.5 text-slate-800"><?= e($ld['level_description'] ?? '—') ?></td>
                        <td class="px-6 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= url('views/teacher/subject-load/grades.php?loading_id=' . (int)$ld['loading_id']) ?>" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded">Input Grade</a>
                                <a href="<?= url('views/teacher/subject-load/class-list.php?loading_id=' . (int)$ld['loading_id']) ?>" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-medium rounded">Class List</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
