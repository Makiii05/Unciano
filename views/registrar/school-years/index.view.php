<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">School Years</h3>
            <p class="text-sm text-slate-500"><?= count($schoolYears) ?> school year(s)</p>
        </div>
        <button onclick="document.getElementById('create-school-year-modal').showModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add School Year
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 text-left font-medium">Code</th>
                    <th class="px-6 py-4 text-left font-medium">Description</th>
                    <th class="px-6 py-4 text-left font-medium">Start Year</th>
                    <th class="px-6 py-4 text-left font-medium">End Year</th>
                    <th class="px-6 py-4 text-left font-medium">Status</th>
                    <th class="px-6 py-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schoolYears)): ?>
                    <tr><td colspan="6" class="py-12 text-center text-slate-400">No school years found.</td></tr>
                <?php else: ?>
                    <?php foreach ($schoolYears as $sy): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($sy['code']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($sy['description']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($sy['start_year']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($sy['end_year']) ?></td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($sy['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= e(ucfirst($sy['status'] ?? 'unknown')) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-1">
                                <button onclick="editSchoolYear(<?= (int) $sy['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                <button onclick="confirmDeleteSchoolYear(<?= (int) $sy['id'] ?>, <?= htmlspecialchars(json_encode($sy['code']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
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
    const schoolYears = <?= json_encode($schoolYears, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function editSchoolYear(id) {
        const sy = schoolYears.find(function(s) { return parseInt(s.id) === id; });
        if (!sy) return;
        document.getElementById('edit-school-year-form').action = "<?= url('views/registrar/school-years/actions/update.php') ?>";
        document.getElementById('edit-school-year-id').value = sy.id;
        document.getElementById('edit-school-year-code').value = sy.code || '';
        document.getElementById('edit-school-year-description').value = sy.description || '';
        document.getElementById('edit-school-year-start_year').value = sy.start_year || '';
        document.getElementById('edit-school-year-end_year').value = sy.end_year || '';
        document.getElementById('edit-school-year-status').value = sy.status || 'active';
        document.getElementById('edit-school-year-modal').showModal();
    }

    function confirmDeleteSchoolYear(id, code) {
        const form = document.getElementById('delete-school-year-form');
        form.action = "<?= url('views/registrar/school-years/actions/delete.php') ?>";
        document.getElementById('delete-school-year-id').value = id;
        document.getElementById('delete-school-year-target').textContent = '"' + code + '"';
        document.getElementById('delete-school-year-modal').showModal();
    }
</script>
