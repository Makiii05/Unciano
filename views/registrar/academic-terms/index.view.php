<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">Academic Terms</h3>
            <p class="text-sm text-slate-500"><?= count($academicTerms) ?> term(s)</p>
        </div>
        <button onclick="document.getElementById('create-academic-term-modal').showModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Academic Term
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 text-left font-medium">Code</th>
                    <th class="px-6 py-4 text-left font-medium">Description</th>
                    <th class="px-6 py-4 text-left font-medium">Type</th>
                    <th class="px-6 py-4 text-left font-medium">School Year</th>
                    <th class="px-6 py-4 text-left font-medium">Department</th>
                    <th class="px-6 py-4 text-left font-medium">Start Date</th>
                    <th class="px-6 py-4 text-left font-medium">End Date</th>
                    <th class="px-6 py-4 text-left font-medium">Status</th>
                    <th class="px-6 py-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($academicTerms)): ?>
                    <tr><td colspan="9" class="py-12 text-center text-slate-400">No academic terms found.</td></tr>
                <?php else: ?>
                    <?php foreach ($academicTerms as $term): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($term['code']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($term['description']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600 capitalize"><?= e($term['type'] ?? 'semester') ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($term['school_year_code'] ?? '—') ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($term['department_code'] ?? 'All') ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($term['start_date'] ?? '') ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($term['end_date'] ?? '') ?></td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($term['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= e(ucfirst($term['status'] ?? 'unknown')) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-1">
                                <button onclick="editAcademicTerm(<?= (int) $term['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                <button onclick="confirmDeleteAcademicTerm(<?= (int) $term['id'] ?>, <?= htmlspecialchars(json_encode($term['code']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/partials/create-modal.php'; ?>
<?php include __DIR__ . '/partials/edit-modal.php'; ?>
<?php include __DIR__ . '/partials/delete-modal.php'; ?>

<script>
    const academicTerms = <?= json_encode($academicTerms, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function editAcademicTerm(id) {
        const term = academicTerms.find(function(t) { return parseInt(t.id) === id; });
        if (!term) return;
        document.getElementById('edit-academic-term-form').action = "<?= url('views/registrar/academic-terms/actions/update.php') ?>";
        document.getElementById('edit-academic-term-id').value = term.id;
        document.getElementById('edit-academic-term-code').value = term.code || '';
        document.getElementById('edit-academic-term-description').value = term.description || '';
        document.getElementById('edit-academic-term-type').value = term.type || 'semester';
        document.getElementById('edit-academic-term-school_year_id').value = term.school_year_id || '';
        document.getElementById('edit-academic-term-department_id').value = term.department_id || '';
        // Dates may be "2024-06-01 00:00:00" or "2024-06-01" – trim to YYYY-MM-DD
        let sd = (term.start_date || '').split(' ')[0].split('T')[0];
        let ed = (term.end_date || '').split(' ')[0].split('T')[0];
        document.getElementById('edit-academic-term-start_date').value = sd;
        document.getElementById('edit-academic-term-end_date').value = ed;
        document.getElementById('edit-academic-term-status').value = term.status || 'active';
        document.getElementById('edit-academic-term-modal').showModal();
    }

    function confirmDeleteAcademicTerm(id, code) {
        const form = document.getElementById('delete-academic-term-form');
        form.action = "<?= url('views/registrar/academic-terms/actions/delete.php') ?>";
        document.getElementById('delete-academic-term-id').value = id;
        document.getElementById('delete-academic-term-target').textContent = '"' + code + '"';
        document.getElementById('delete-academic-term-modal').showModal();
    }
</script>
