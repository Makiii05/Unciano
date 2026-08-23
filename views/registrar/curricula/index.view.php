<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">Curricula</h3>
            <p class="text-sm text-slate-500"><?= count($curricula) ?> curricula</p>
        </div>
        <button onclick="document.getElementById('create-curriculum-modal').showModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Curriculum
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 text-left font-medium">Curriculum</th>
                    <th class="px-6 py-4 text-left font-medium">Department</th>
                    <th class="px-6 py-4 text-left font-medium">Status</th>
                    <th class="px-6 py-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($curricula)): ?>
                    <tr><td colspan="4" class="py-12 text-center text-slate-400">No curricula found.</td></tr>
                <?php else: ?>
                    <?php foreach ($curricula as $curr): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($curr['curriculum']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($curr['department_code'] ?? '—') ?></td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($curr['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= e(ucfirst($curr['status'] ?? 'unknown')) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-1">
                                <button onclick="editCurriculum(<?= (int) $curr['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                <button onclick="confirmDeleteCurriculum(<?= (int) $curr['id'] ?>, <?= htmlspecialchars(json_encode($curr['curriculum']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
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
    const curricula = <?= json_encode($curricula, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function editCurriculum(id) {
        const curr = curricula.find(function(c) { return parseInt(c.id) === id; });
        if (!curr) return;
        document.getElementById('edit-curriculum-form').action = "<?= url('views/registrar/curricula/actions/update.php') ?>";
        document.getElementById('edit-curriculum-id').value = curr.id;
        document.getElementById('edit-curriculum-curriculum').value = curr.curriculum || '';
        document.getElementById('edit-curriculum-department_id').value = curr.department_id || '';
        document.getElementById('edit-curriculum-status').value = curr.status || 'active';
        document.getElementById('edit-curriculum-modal').showModal();
    }

    function confirmDeleteCurriculum(id, name) {
        const form = document.getElementById('delete-curriculum-form');
        form.action = "<?= url('views/registrar/curricula/actions/delete.php') ?>";
        document.getElementById('delete-curriculum-id').value = id;
        document.getElementById('delete-curriculum-target').textContent = '"' + name + '"';
        document.getElementById('delete-curriculum-modal').showModal();
    }
</script>
