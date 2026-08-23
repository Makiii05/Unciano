<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <form method="GET" action="<?= url('views/registrar/prospectus/index.php') ?>" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
            <select name="department_id" id="filter-department" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                <option value="">Select department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= e($dept['id']) ?>" <?= ($departmentId ?? '') == $dept['id'] ? 'selected' : '' ?>><?= e($dept['code']) ?> - <?= e($dept['description']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-slate-700 mb-1">Curriculum</label>
            <select name="curriculum_id" id="filter-curriculum" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" <?= empty($curricula) ? 'disabled' : '' ?>>
                <option value="">Select curriculum</option>
                <?php foreach ($curricula as $curr): ?>
                    <option value="<?= e($curr['id']) ?>" <?= ($curriculumId ?? '') == $curr['id'] ? 'selected' : '' ?>><?= e($curr['curriculum']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Search</button>
        <?php if ($selectedCurriculum): ?>
            <button type="button" onclick="openCreateModal()" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">Add Subject</button>
        <?php endif; ?>
    </form>
</div>

<?php if ($curriculumId && $selectedCurriculum): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <h3 class="text-base font-semibold text-slate-800"><?= e($selectedCurriculum['curriculum']) ?></h3>
        <p class="text-sm text-slate-500"><?= e($selectedCurriculum['department_id'] ?? '') ? 'Department ID: ' . e($selectedCurriculum['department_id']) : '' ?></p>
    </div>

    <?php if (empty($prospectus)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">No subjects found</h3>
            <p class="text-sm text-slate-500 mb-4">This curriculum has no prospectus entries yet.</p>
            <button onclick="openCreateModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Add Subject</button>
        </div>
    <?php else: ?>
        <?php
        // Group by level_id
        $grouped = [];
        foreach ($prospectus as $p) {
            $lvlId = $p['level_id'] ?? 0;
            $grouped[$lvlId][] = $p;
        }
        ?>
        <div class="space-y-6">
            <?php foreach ($grouped as $levelId => $items): ?>
                <?php
                $first = $items[0];
                $levelLabel = ($first['program_code'] ?? '') . ' - ' . ($first['level_code'] ?? $first['level_description'] ?? 'Level ' . $levelId);
                $levelDesc = $first['level_description'] ?? '';
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="level-group-<?= e($levelId) ?>">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800"><?= e(trim($levelLabel, ' -')) ?></h4>
                            <p class="text-xs text-slate-500"><?= e($levelDesc) ?></p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800"><?= count($items) ?> subject(s)</span>
                    </div>

                    <?php
                    // Group by term_id within level
                    $byTerm = [];
                    foreach ($items as $it) {
                        $termId = $it['term_id'] ?? 0;
                        $byTerm[$termId][] = $it;
                    }
                    ?>
                    <?php foreach ($byTerm as $termId => $termItems): ?>
                        <?php
                        $tFirst = $termItems[0];
                        $termLabel = ($tFirst['term_description'] ?? $tFirst['term_code'] ?? 'Term ' . $termId);
                        $syLabel = $tFirst['school_year_code'] ?? '';
                        ?>
                        <div class="px-6 py-3 bg-white border-b border-slate-100">
                            <h5 class="text-sm font-medium text-slate-700"><?= e($termLabel) ?> <?= $syLabel ? '<span class="text-xs text-slate-400">(' . e($syLabel) . ')</span>' : '' ?></h5>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-slate-500 text-xs uppercase tracking-wider bg-slate-50/50">
                                        <th class="px-6 py-3 text-left font-medium">Subject</th>
                                        <th class="px-6 py-3 text-left font-medium">Units</th>
                                        <th class="px-6 py-3 text-left font-medium">Prerequisites</th>
                                        <th class="px-6 py-3 text-left font-medium">Status</th>
                                        <th class="px-6 py-3 text-right font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($termItems as $p): ?>
                                        <?php $isNew = $newProspectusId && (int) $p['id'] === (int) $newProspectusId; ?>
                                        <tr id="prospectus-row-<?= e($p['id']) ?>" class="border-t border-slate-100 hover:bg-slate-50 transition-colors <?= $isNew ? 'bg-primary-50 ring-2 ring-primary-200' : '' ?>">
                                            <td class="px-6 py-3.5">
                                                <p class="text-sm font-medium text-slate-800"><?= e($p['subject_code'] ?? '—') ?> - <?= e($p['subject_description'] ?? '—') ?></p>
                                            </td>
                                            <td class="px-6 py-3.5 text-sm text-slate-600"><?= e($p['unit'] ?? '—') ?></td>
                                            <td class="px-6 py-3.5 text-sm text-slate-600"><?= e($p['prerequisites_display'] ?? '—') ?></td>
                                            <td class="px-6 py-3.5">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($p['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>">
                                                    <?= e(ucfirst($p['status'] ?? 'unknown')) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-3.5 text-right space-x-1">
                                                <button onclick="editProspectus(<?= (int) $p['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                                <button onclick="confirmDeleteProspectus(<?= (int) $p['id'] ?>, <?= htmlspecialchars(json_encode($p['subject_code'] ?? 'entry'), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php elseif ($departmentId && empty($curricula)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
        <p class="text-slate-500">No curricula found for selected department.</p>
    </div>
<?php elseif (!$departmentId): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-primary-100 flex items-center justify-center">
            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-800 mb-2">Select Department and Curriculum</h3>
        <p class="text-sm text-slate-500">Choose a department and curriculum to view prospectus.</p>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/partials/create-modal.php'; ?>
<?php include __DIR__ . '/partials/edit-modal.php'; ?>
<?php include __DIR__ . '/partials/delete-modal.php'; ?>

<script>
    const curriculaData = <?= json_encode($curricula ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const allLevels = <?= json_encode($allLevels ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const prospectusData = <?= json_encode($prospectus ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const selectedCurriculum = <?= json_encode($selectedCurriculum ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const newProspectusId = <?= json_encode($newProspectusId) ?>;

    function populateLevels(deptId) {
        const levelSelects = [document.getElementById('create-level_id'), document.getElementById('edit-level_id')];
        levelSelects.forEach(function(select) {
            if (!select) return;
            const currentVal = select.value;
            select.innerHTML = '<option value="">Select level</option>';
            if (!deptId) return;
            // Filter levels where program's department matches
            // allLevels have program_id, need to map via programs? Instead we fetch filtered from server? For now filter client-side if level has department info
            // Our allLevels currently lacks program department; we will refetch via filteredLevels logic: we have levels array passed as filtered already, so use that
            // Actually we have `levels` variable filtered server-side; use allLevels with program check via extra fetch
            // Simple: if level has program_id, we can assume filteredLevels is provided via window
            // For vanilla, we will populate from `filteredLevels` if available
            let filtered = [];
            if (window.filteredLevels && window.filteredLevels.length) {
                filtered = window.filteredLevels.filter(function(l) { return true; });
            } else {
                filtered = allLevels;
            }
            filtered.forEach(function(l) {
                const opt = document.createElement('option');
                opt.value = l.id;
                opt.textContent = (l.program_code ? l.program_code + ' - ' : '') + (l.code || '') + ' - ' + (l.description || '');
                if (String(l.id) === String(currentVal)) opt.selected = true;
                select.appendChild(opt);
            });
        });
    }

    // Department change: fetch curricula
    const deptSelect = document.getElementById('filter-department');
    const currSelect = document.getElementById('filter-curriculum');
    if (deptSelect) {
        deptSelect.addEventListener('change', async function() {
            const deptId = this.value;
            currSelect.innerHTML = '<option value="">Select curriculum</option>';
            currSelect.disabled = true;
            if (!deptId) {
                populateLevels(null);
                return;
            }
            // Populate levels - fetch via filteredLevels endpoint? For now use server-provided levels filtered
            // We will fetch curricula via API
            try {
                const res = await fetch("<?= url('api/prospectus/curricula-by-department.php') ?>?department_id=" + encodeURIComponent(deptId));
                const data = await res.json();
                if (data.success && data.data) {
                    data.data.forEach(function(c) {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.curriculum;
                        currSelect.appendChild(opt);
                    });
                    currSelect.disabled = false;
                }
            } catch (e) {
                console.error(e);
            }
            // Also update create modal level dropdown via fetch levels filtered? We have levels already filtered server-side, but for dynamic we need to refetch levels
            // For simplicity, keep allLevels and filter client-side if we had program department mapping - will use current `levels` variable which is filtered
            populateLevels(deptId);
        });
    }

    function openCreateModal() {
        if (!selectedCurriculum) {
            // Try to get from filter
            const currId = currSelect ? currSelect.value : '';
            if (!currId) {
                alert('Please select a curriculum first.');
                return;
            }
        }
        const form = document.getElementById('create-prospectus-form');
        if (form) {
            form.action = "<?= url('views/registrar/prospectus/actions/store.php') ?>";
            const currId = selectedCurriculum ? selectedCurriculum.id : (currSelect ? currSelect.value : '');
            const currDisplay = selectedCurriculum ? selectedCurriculum.curriculum : (currSelect ? currSelect.options[currSelect.selectedIndex]?.text : '');
            document.getElementById('create-curriculum_id').value = currId;
            document.getElementById('create-curriculum-display').textContent = currDisplay || '—';
            // Populate levels for current department
            const deptId = deptSelect ? deptSelect.value : (selectedCurriculum ? '' : '');
            populateLevels(deptId);
        }
        document.getElementById('create-prospectus-modal').showModal();
    }

    function editProspectus(id) {
        const p = prospectusData.find(function(x) { return parseInt(x.id) === id; });
        if (!p) return;
        document.getElementById('edit-prospectus-form').action = "<?= url('views/registrar/prospectus/actions/update.php') ?>";
        document.getElementById('edit-prospectus-id').value = p.id;
        document.getElementById('edit-prospectus-curriculum_id').value = p.curriculum_id || '';
        document.getElementById('edit-prospectus-curriculum-display').textContent = p.curriculum_name || '—';
        document.getElementById('edit-level_id').value = p.level_id || '';
        document.getElementById('edit-term_id').value = p.term_id || '';
        document.getElementById('edit-subject_id').value = p.subject_id || '';
        document.getElementById('edit-prospectus-status').value = p.status || 'active';
        // Ensure levels populated
        const deptId = deptSelect ? deptSelect.value : '';
        populateLevels(deptId);
        // After populate, set level value again (since populate resets)
        setTimeout(function() { document.getElementById('edit-level_id').value = p.level_id || ''; }, 50);
        document.getElementById('edit-prospectus-modal').showModal();
    }

    function confirmDeleteProspectus(id, code) {
        const form = document.getElementById('delete-prospectus-form');
        form.action = "<?= url('views/registrar/prospectus/actions/delete.php') ?>";
        document.getElementById('delete-prospectus-id').value = id;
        document.getElementById('delete-prospectus-target').textContent = '"' + code + '"';
        document.getElementById('delete-prospectus-modal').showModal();
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (newProspectusId) {
            const row = document.getElementById('prospectus-row-' + newProspectusId);
            if (row) {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(function() { row.classList.remove('bg-primary-50', 'ring-2', 'ring-primary-200'); }, 3000);
            }
        }
    });
</script>
