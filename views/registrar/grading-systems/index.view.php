<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">Grading Systems</h3>
            <p class="text-sm text-slate-500"><?= count($gradingSystems) ?> system(s)</p>
        </div>
        <button onclick="document.getElementById('create-grading-system-modal').showModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Grading System
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 text-left font-medium">Description</th>
                    <th class="px-6 py-4 text-left font-medium">Department</th>
                    <th class="px-6 py-4 text-left font-medium">Components</th>
                    <th class="px-6 py-4 text-left font-medium">Total %</th>
                    <th class="px-6 py-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($gradingSystems)): ?>
                    <tr><td colspan="5" class="py-12 text-center text-slate-400">No grading systems found.</td></tr>
                <?php else: ?>
                    <?php foreach ($gradingSystems as $gs): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($gs['description']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($gs['department_code'] ?? '—') ?></td>
                            <td class="px-6 py-3.5 text-slate-600">
                                <?php if (!empty($gs['components'])): ?>
                                    <?php foreach ($gs['components'] as $c): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 mr-1 mb-1"><?= e($c['code']) ?> (<?= e($c['percentage']) ?>%)</span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($gs['total_percentage'] ?? '0') ?>%</td>
                            <td class="px-6 py-3.5 text-right space-x-1">
                                <button onclick="editGradingSystem(<?= (int) $gs['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                <button onclick="confirmDeleteGradingSystem(<?= (int) $gs['id'] ?>, <?= htmlspecialchars(json_encode($gs['description']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
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
    const gradingSystems = <?= json_encode($gradingSystems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const componentsByDept = <?= json_encode($componentsByDept, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const allComponents = <?= json_encode($components, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function renderComponentCheckboxes(containerId, departmentId, selectedIds, totalInputId, warningId) {
        const container = document.getElementById(containerId);
        const totalInput = document.getElementById(totalInputId);
        const warning = document.getElementById(warningId);
        if (!container) return;
        container.innerHTML = '';
        const comps = componentsByDept[departmentId] || [];
        if (comps.length === 0) {
            container.innerHTML = '<p class="text-sm text-slate-400">No components for this department.</p>';
            if (totalInput) totalInput.value = '0';
            if (warning) warning.classList.add('hidden');
            return;
        }
        let total = 0;
        comps.forEach(function(c) {
            const isChecked = selectedIds.includes(String(c.id)) || selectedIds.includes(c.id);
            if (isChecked) total += parseFloat(c.percentage) || 0;
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2';
            div.innerHTML = '<label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer"><input type="checkbox" name="component_ids[]" value="' + c.id + '" data-percentage="' + c.percentage + '" ' + (isChecked ? 'checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"> ' + escapeHtml(c.code) + ' - ' + escapeHtml(c.description) + ' (' + c.percentage + '%)</label>';
            container.appendChild(div);
        });
        if (totalInput) totalInput.value = total.toFixed(2);
        if (warning) {
            if (total > 100) warning.classList.remove('hidden');
            else warning.classList.add('hidden');
        }
        // Add change listeners
        container.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
            cb.addEventListener('change', function() { updateTotal(containerId, totalInputId, warningId); });
        });
    }

    function updateTotal(containerId, totalInputId, warningId) {
        const container = document.getElementById(containerId);
        const totalInput = document.getElementById(totalInputId);
        const warning = document.getElementById(warningId);
        let total = 0;
        container.querySelectorAll('input[type="checkbox"]:checked').forEach(function(cb) {
            total += parseFloat(cb.getAttribute('data-percentage')) || 0;
        });
        if (totalInput) totalInput.value = total.toFixed(2);
        if (warning) {
            if (total > 100) warning.classList.remove('hidden');
            else warning.classList.add('hidden');
        }
    }

    function editGradingSystem(id) {
        const gs = gradingSystems.find(function(g) { return parseInt(g.id) === id; });
        if (!gs) return;
        document.getElementById('edit-grading-system-form').action = "<?= url('views/registrar/grading-systems/actions/update.php') ?>";
        document.getElementById('edit-grading-system-id').value = gs.id;
        document.getElementById('edit-grading-system-description').value = gs.description || '';
        document.getElementById('edit-grading-system-department_id').value = gs.department_id || '';
        const selected = (gs.components || []).map(function(c){ return String(c.component_id); });
        renderComponentCheckboxes('edit-components-container', gs.department_id, selected, 'edit-total-percentage', 'edit-warning');
        document.getElementById('edit-grading-system-modal').showModal();
    }

    function confirmDeleteGradingSystem(id, desc) {
        const form = document.getElementById('delete-grading-system-form');
        form.action = "<?= url('views/registrar/grading-systems/actions/delete.php') ?>";
        document.getElementById('delete-grading-system-id').value = id;
        document.getElementById('delete-grading-system-target').textContent = '"' + desc + '"';
        document.getElementById('delete-grading-system-modal').showModal();
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const createDept = document.getElementById('create-grading-system-department_id');
        const editDept = document.getElementById('edit-grading-system-department_id');
        if (createDept) {
            createDept.addEventListener('change', function() {
                renderComponentCheckboxes('create-components-container', this.value, [], 'create-total-percentage', 'create-warning');
            });
            // Initial
            if (createDept.value) renderComponentCheckboxes('create-components-container', createDept.value, [], 'create-total-percentage', 'create-warning');
        }
        if (editDept) {
            editDept.addEventListener('change', function() {
                renderComponentCheckboxes('edit-components-container', this.value, [], 'edit-total-percentage', 'edit-warning');
            });
        }
    });
</script>
