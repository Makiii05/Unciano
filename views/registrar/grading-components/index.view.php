<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">Grading Components</h3>
            <p class="text-sm text-slate-500"><?= count($components) ?> component(s)</p>
        </div>
        <button onclick="document.getElementById('create-component-modal').showModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Component
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 text-left font-medium">Code</th>
                    <th class="px-6 py-4 text-left font-medium">Description</th>
                    <th class="px-6 py-4 text-left font-medium">Percentage</th>
                    <th class="px-6 py-4 text-left font-medium">Department</th>
                    <th class="px-6 py-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($components)): ?>
                    <tr><td colspan="5" class="py-12 text-center text-slate-400">No grading components found.</td></tr>
                <?php else: ?>
                    <?php foreach ($components as $comp): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($comp['code']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($comp['description']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($comp['percentage']) ?>%</td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($comp['department_code'] ?? '—') ?></td>
                            <td class="px-6 py-3.5 text-right space-x-1">
                                <button onclick="editComponent(<?= (int) $comp['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                <button onclick="confirmDeleteComponent(<?= (int) $comp['id'] ?>, <?= htmlspecialchars(json_encode($comp['code']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
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
    const components = <?= json_encode($components, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function editComponent(id) {
        const c = components.find(function(x) { return parseInt(x.id) === id; });
        if (!c) return;
        document.getElementById('edit-component-form').action = "<?= url('views/registrar/grading-components/actions/update.php') ?>";
        document.getElementById('edit-component-id').value = c.id;
        document.getElementById('edit-component-code').value = c.code || '';
        document.getElementById('edit-component-description').value = c.description || '';
        document.getElementById('edit-component-percentage').value = c.percentage || 0;
        document.getElementById('edit-component-department_id').value = c.department_id || '';
        document.getElementById('edit-component-modal').showModal();
    }

    function confirmDeleteComponent(id, code) {
        const form = document.getElementById('delete-component-form');
        form.action = "<?= url('views/registrar/grading-components/actions/delete.php') ?>";
        document.getElementById('delete-component-id').value = id;
        document.getElementById('delete-component-target').textContent = '"' + code + '"';
        document.getElementById('delete-component-modal').showModal();
    }
</script>
