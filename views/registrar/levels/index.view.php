<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">Levels</h3>
            <p class="text-sm text-slate-500"><?= count($levels) ?> level(s)</p>
        </div>
        <button onclick="document.getElementById('create-level-modal').showModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Level
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 text-left font-medium">Code</th>
                    <th class="px-6 py-4 text-left font-medium">Description</th>
                    <th class="px-6 py-4 text-left font-medium">Program</th>
                    <th class="px-6 py-4 text-left font-medium">Order</th>
                    <th class="px-6 py-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($levels)): ?>
                    <tr><td colspan="5" class="py-12 text-center text-slate-400">No levels found.</td></tr>
                <?php else: ?>
                    <?php foreach ($levels as $lvl): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($lvl['code']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($lvl['description']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($lvl['program_code'] ?? '—') ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($lvl['order'] ?? 0) ?></td>
                            <td class="px-6 py-3.5 text-right space-x-1">
                                <button onclick="editLevel(<?= (int) $lvl['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                <button onclick="confirmDeleteLevel(<?= (int) $lvl['id'] ?>, <?= htmlspecialchars(json_encode($lvl['code']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
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
    const levels = <?= json_encode($levels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function editLevel(id) {
        const lvl = levels.find(function(l) { return parseInt(l.id) === id; });
        if (!lvl) return;
        document.getElementById('edit-level-form').action = "<?= url('views/registrar/levels/actions/update.php') ?>";
        document.getElementById('edit-level-id').value = lvl.id;
        document.getElementById('edit-level-code').value = lvl.code || '';
        document.getElementById('edit-level-description').value = lvl.description || '';
        document.getElementById('edit-level-program_id').value = lvl.program_id || '';
        document.getElementById('edit-level-order').value = lvl.order || 0;
        document.getElementById('edit-level-modal').showModal();
    }

    function confirmDeleteLevel(id, code) {
        const form = document.getElementById('delete-level-form');
        form.action = "<?= url('views/registrar/levels/actions/delete.php') ?>";
        document.getElementById('delete-level-id').value = id;
        document.getElementById('delete-level-target').textContent = '"' + code + '"';
        document.getElementById('delete-level-modal').showModal();
    }
</script>
