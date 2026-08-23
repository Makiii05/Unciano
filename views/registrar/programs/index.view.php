<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">Programs</h3>
            <p class="text-sm text-slate-500"><?= count($programs) ?> program(s)</p>
        </div>
        <button onclick="document.getElementById('create-program-modal').showModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Program
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 text-left font-medium">Code</th>
                    <th class="px-6 py-4 text-left font-medium">Description</th>
                    <th class="px-6 py-4 text-left font-medium">Department</th>
                    <th class="px-6 py-4 text-left font-medium">Status</th>
                    <th class="px-6 py-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($programs)): ?>
                    <tr><td colspan="5" class="py-12 text-center text-slate-400">No programs found.</td></tr>
                <?php else: ?>
                    <?php foreach ($programs as $prog): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($prog['code']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($prog['description']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($prog['department_code'] ?? '—') ?></td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($prog['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= e(ucfirst($prog['status'] ?? 'unknown')) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-1">
                                <button onclick="editProgram(<?= (int) $prog['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                <button onclick="confirmDeleteProgram(<?= (int) $prog['id'] ?>, <?= htmlspecialchars(json_encode($prog['code']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
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
    const programs = <?= json_encode($programs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function editProgram(id) {
        const prog = programs.find(function(p) { return parseInt(p.id) === id; });
        if (!prog) return;
        document.getElementById('edit-program-form').action = "<?= url('views/registrar/programs/actions/update.php') ?>";
        document.getElementById('edit-program-id').value = prog.id;
        document.getElementById('edit-program-code').value = prog.code || '';
        document.getElementById('edit-program-description').value = prog.description || '';
        document.getElementById('edit-program-department_id').value = prog.department_id || '';
        document.getElementById('edit-program-status').value = prog.status || 'active';
        document.getElementById('edit-program-modal').showModal();
    }

    function confirmDeleteProgram(id, code) {
        const form = document.getElementById('delete-program-form');
        form.action = "<?= url('views/registrar/programs/actions/delete.php') ?>";
        document.getElementById('delete-program-id').value = id;
        document.getElementById('delete-program-target').textContent = '"' + code + '"';
        document.getElementById('delete-program-modal').showModal();
    }
</script>
