<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <form method="GET" action="<?= url('views/registrar/subject-offerings/index.php') ?>" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
            <select name="department_id" id="filter-department" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                <option value="">Select department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= e($dept['id']) ?>" <?= ($departmentId ?? '') == $dept['id'] ? 'selected' : '' ?>><?= e($dept['code']) ?> - <?= e($dept['description']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-sm font-medium text-slate-700 mb-1">Academic Term</label>
            <select name="term" id="filter-term" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                <option value="">Select term</option>
                <?php foreach ($terms as $t): ?>
                    <option value="<?= e($t['id']) ?>" <?= ($termId ?? '') == $t['id'] ? 'selected' : '' ?>><?= e($t['code'] ?? $t['description']) ?> - <?= e($t['description'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-sm font-medium text-slate-700 mb-1">Curriculum</label>
            <select name="curriculum" id="filter-curriculum" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                <option value="">Select curriculum</option>
                <?php foreach ($curricula as $c): ?>
                    <option value="<?= e($c['id']) ?>" <?= ($curriculumId ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['curriculum']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="tab" id="filter-tab" value="<?= e($tab ?? 'prospectus') ?>">
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Filter</button>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Left: Subject Picker -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="border-b border-slate-200">
            <div class="flex">
                <button id="tab-prospectus-btn" onclick="setTab('prospectus')" class="px-6 py-4 text-sm font-medium transition-colors border-b-2 border-primary-600 text-primary-600">By Prospectus</button>
                <button id="tab-subject-btn" onclick="setTab('subject')" class="px-6 py-4 text-sm font-medium transition-colors text-slate-500 hover:text-slate-700">By Subject</button>
            </div>
        </div>

        <!-- Prospectus Tab -->
        <div id="tab-prospectus" class="p-6">
            <div id="prospectus-results">
                <?php if (empty($prospectus)): ?>
                    <p class="text-center text-slate-400 py-8">Select a curriculum to view prospectus.</p>
                <?php else: ?>
                    <div id="prospectus-list" class="space-y-4">
                        <?php
                        // Group by program -> level -> term for display similar to Laravel
                        $grouped = [];
                        foreach ($prospectus as $p) {
                            $progId = $p['program_id'] ?? $p['level_program_id'] ?? 0;
                            $grouped[$progId][] = $p;
                        }
                        ?>
                        <?php foreach ($prospectus as $p): ?>
                            <div class="border border-slate-200 rounded-lg p-3 flex items-center justify-between hover:bg-slate-50">
                                <div>
                                    <p class="text-sm font-medium text-slate-800"><?= e($p['subject_code'] ?? $p['subject_description'] ?? 'Subject') ?> - <?= e($p['subject_description'] ?? '') ?></p>
                                    <p class="text-xs text-slate-500">Level: <?= e($p['level_description'] ?? $p['level_code'] ?? '') ?> | Term: <?= e($p['term_description'] ?? $p['term_code'] ?? '') ?></p>
                                </div>
                                <button onclick="openAddProspectusModal(<?= (int) $p['id'] ?>, <?= (int) $p['subject_id'] ?>)" class="px-3 py-1 text-xs font-medium bg-primary-600 hover:bg-primary-700 text-white rounded">Add</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Subject Tab -->
        <div id="tab-subject" class="p-6 hidden">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Search Subject</label>
                <input type="text" id="subject-search" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" placeholder="Type code or description...">
            </div>
            <div id="subject-results" class="space-y-2">
                <p class="text-center text-slate-400 py-4">Type to search subjects.</p>
            </div>
        </div>
    </div>

    <!-- Right: Offerings List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-800">Offerings</h3>
            <span id="offering-count" class="text-sm text-slate-500"><?= count($offerings) ?> offering(s)</span>
        </div>
        <div id="offering-results" class="p-6">
            <div id="offering-list" class="space-y-2">
                <?php if (empty($offerings)): ?>
                    <p class="text-center text-slate-400 py-8">No subject offerings for this term yet.</p>
                <?php else: ?>
                    <?php foreach ($offerings as $off): ?>
                        <div class="border border-slate-200 rounded-lg p-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-800"><?= e($off['code']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($off['subject_code']) ?> - <?= e($off['subject_description']) ?> | <?= e($off['program_code'] ?? '') ?> | Class: <?= e($off['class_size']) ?></p>
                            </div>
                            <button onclick="confirmDeleteOffering(<?= (int) $off['id'] ?>, <?= htmlspecialchars(json_encode($off['code']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded">Delete</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/add-modal.php'; ?>
<?php include __DIR__ . '/partials/delete-modal.php'; ?>

<script>
    const prospectusUrl = "<?= url('api/subject-offerings/prospectus-subjects.php') ?>";
    const searchUrl = "<?= url('api/subject-offerings/search-subjects.php') ?>";
    const levelsUrl = "<?= url('api/subject-offerings/levels-by-program.php') ?>";
    const programs = <?= json_encode($programs ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const gradingSystems = <?= json_encode($gradingSystems ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    let currentTab = "<?= e($tab ?? 'prospectus') ?>";

    function setTab(tab) {
        currentTab = tab;
        document.getElementById('filter-tab').value = tab;
        document.getElementById('tab-prospectus').classList.toggle('hidden', tab !== 'prospectus');
        document.getElementById('tab-subject').classList.toggle('hidden', tab !== 'subject');
        document.getElementById('tab-prospectus-btn').className = tab === 'prospectus' ? 'px-6 py-4 text-sm font-medium transition-colors border-b-2 border-primary-600 text-primary-600' : 'px-6 py-4 text-sm font-medium transition-colors text-slate-500 hover:text-slate-700';
        document.getElementById('tab-subject-btn').className = tab === 'subject' ? 'px-6 py-4 text-sm font-medium transition-colors border-b-2 border-primary-600 text-primary-600' : 'px-6 py-4 text-sm font-medium transition-colors text-slate-500 hover:text-slate-700';
    }
    setTab(currentTab);

    // Department change: reload page with filter (simpler than AJAX for now)
    // Alternatively, we could fetch terms/curricula via API, but form submit is simpler
    document.getElementById('filter-department')?.addEventListener('change', function() {
        // Clear term and curriculum when department changes, let user re-select
        // We could auto-submit, but keep manual Filter button
    });

    // Prospectus curriculum change -> fetch prospectus list
    document.getElementById('filter-curriculum')?.addEventListener('change', async function() {
        const curriculumId = this.value;
        const container = document.getElementById('prospectus-list') || document.getElementById('prospectus-results');
        if (!curriculumId) {
            if (container) container.innerHTML = '<p class="text-center text-slate-400 py-8">Select a curriculum to view prospectus.</p>';
            return;
        }
        try {
            const res = await fetch(prospectusUrl + "?curriculum_id=" + encodeURIComponent(curriculumId));
            const data = await res.json();
            if (data.success && data.data) {
                let html = '<div class="space-y-2">';
                data.data.forEach(function(p) {
                    html += '<div class="border border-slate-200 rounded-lg p-3 flex items-center justify-between hover:bg-slate-50"><div><p class="text-sm font-medium text-slate-800">' + escapeHtml(p.subject_code || p.subject_description || 'Subject') + ' - ' + escapeHtml(p.subject_description || '') + '</p><p class="text-xs text-slate-500">Level: ' + escapeHtml(p.level_description || p.level_code || '') + ' | Term: ' + escapeHtml(p.term_description || p.term_code || '') + '</p></div><button onclick="openAddProspectusModal(' + p.id + ', ' + p.subject_id + ')" class="px-3 py-1 text-xs font-medium bg-primary-600 hover:bg-primary-700 text-white rounded">Add</button></div>';
                });
                html += '</div>';
                if (container) container.innerHTML = html;
            }
        } catch (e) {
            console.error(e);
        }
    });

    let searchTimer = null;
    document.getElementById('subject-search')?.addEventListener('input', function(e) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() { doSubjectSearch(e.target.value); }, 300);
    });

    async function doSubjectSearch(q) {
        q = q.trim();
        const container = document.getElementById('subject-results');
        if (q === '') {
            container.innerHTML = '<p class="text-center text-slate-400 py-4">Type to search subjects.</p>';
            return;
        }
        try {
            const res = await fetch(searchUrl + "?q=" + encodeURIComponent(q));
            const data = await res.json();
            if (data.success && data.data.length > 0) {
                let html = '<div class="space-y-2">';
                data.data.forEach(function(item) {
                    html += '<div class="border border-slate-200 rounded-lg p-3 flex items-center justify-between hover:bg-slate-50"><div><p class="text-sm font-medium text-slate-800">' + escapeHtml(item.code) + '</p><p class="text-xs text-slate-500">' + escapeHtml(item.description) + '</p></div><button onclick="openAddSubjectModal(' + item.id + ')" class="px-3 py-1 text-xs font-medium bg-primary-600 hover:bg-primary-700 text-white rounded">Add</button></div>';
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-center text-slate-400 py-4">No subjects found.</p>';
            }
        } catch (e) {
            container.innerHTML = '<p class="text-center text-red-500 py-4">Search failed.</p>';
        }
    }

    function gradingSelectOptions() {
        let html = '<option value="">Select grading system (optional)</option>';
        gradingSystems.forEach(function(gs) {
            html += '<option value="' + gs.id + '">' + escapeHtml(gs.description) + ' (' + gs.total_percentage + '%)</option>';
        });
        return html;
    }

    function openAddProspectusModal(prospectusId, subjectId) {
        const deptId = document.getElementById('filter-department').value;
        const termId = document.getElementById('filter-term').value;
        if (!deptId || !termId) {
            alert('Please select Department and Academic Term first.');
            return;
        }
        document.getElementById('add-offering-form').reset();
        document.getElementById('add-offering-department_id').value = deptId;
        document.getElementById('add-offering-academic_term_id').value = termId;
        document.getElementById('add-offering-subject_id').value = subjectId;
        document.getElementById('add-offering-prospectus_id').value = prospectusId;
        document.getElementById('add-offering-program_id').value = '';
        document.getElementById('add-offering-level_id').value = '';
        document.getElementById('add-program-field').classList.add('hidden');
        document.getElementById('add-level-field').classList.add('hidden');
        document.getElementById('add-context-display').textContent = 'Prospectus ID: ' + prospectusId + ', Subject ID: ' + subjectId;
        document.getElementById('add-offering-grading_id').innerHTML = gradingSelectOptions();
        document.getElementById('add-offering-class_size').value = 40;
        document.getElementById('add-error-alert').classList.add('hidden');
        document.getElementById('add-offering-modal').showModal();
    }

    function openAddSubjectModal(subjectId) {
        const deptId = document.getElementById('filter-department').value;
        const termId = document.getElementById('filter-term').value;
        if (!deptId || !termId) {
            alert('Please select Department and Academic Term first.');
            return;
        }
        document.getElementById('add-offering-form').reset();
        document.getElementById('add-offering-department_id').value = deptId;
        document.getElementById('add-offering-academic_term_id').value = termId;
        document.getElementById('add-offering-subject_id').value = subjectId;
        document.getElementById('add-offering-prospectus_id').value = '';
        document.getElementById('add-program-field').classList.remove('hidden');
        document.getElementById('add-level-field').classList.remove('hidden');
        // Populate programs
        const progSelect = document.getElementById('add-offering-program_id');
        progSelect.innerHTML = '<option value="">Select program</option>';
        programs.forEach(function(p) {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.code + ' - ' + p.description;
            progSelect.appendChild(opt);
        });
        document.getElementById('add-offering-level_id').innerHTML = '<option value="">Select level</option>';
        document.getElementById('add-offering-level_id').disabled = true;
        document.getElementById('add-context-display').textContent = 'Subject ID: ' + subjectId;
        document.getElementById('add-offering-grading_id').innerHTML = gradingSelectOptions();
        document.getElementById('add-offering-class_size').value = 40;
        document.getElementById('add-error-alert').classList.add('hidden');
        document.getElementById('add-offering-modal').showModal();
    }

    document.getElementById('add-offering-program_id')?.addEventListener('change', async function() {
        const programId = this.value;
        const levelSelect = document.getElementById('add-offering-level_id');
        levelSelect.innerHTML = '<option value="">Select level</option>';
        levelSelect.disabled = true;
        if (!programId) return;
        try {
            const res = await fetch(levelsUrl + "?program_id=" + encodeURIComponent(programId));
            const data = await res.json();
            if (data.success && data.data) {
                data.data.forEach(function(l) {
                    const opt = document.createElement('option');
                    opt.value = l.id;
                    opt.textContent = l.code + ' - ' + l.description;
                    levelSelect.appendChild(opt);
                });
                levelSelect.disabled = false;
            }
        } catch (e) {}
    });

    document.getElementById('add-offering-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                // Add to offering list
                const list = document.getElementById('offering-list');
                const countEl = document.getElementById('offering-count');
                if (list) {
                    // If empty message, clear
                    if (list.innerHTML.includes('No subject offerings')) list.innerHTML = '';
                    const div = document.createElement('div');
                    div.className = 'border border-slate-200 rounded-lg p-3 flex items-center justify-between';
                    div.innerHTML = '<div><p class="text-sm font-medium text-slate-800">' + escapeHtml(data.data.code) + '</p><p class="text-xs text-slate-500">' + escapeHtml(data.data.subject_code || '') + ' | Class: ' + escapeHtml(String(data.data.class_size)) + '</p></div><button onclick="confirmDeleteOffering(' + data.data.id + ', \'' + escapeHtml(data.data.code) + '\')" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded">Delete</button>';
                    list.prepend(div);
                }
                if (countEl) {
                    const current = parseInt(countEl.textContent) || 0;
                    countEl.textContent = (current + 1) + ' offering(s)';
                }
                document.getElementById('add-offering-modal').close();
            } else {
                const alertEl = document.getElementById('add-error-alert');
                alertEl.textContent = data.message || 'Failed to create offering.';
                alertEl.classList.remove('hidden');
            }
        } catch (err) {
            const alertEl = document.getElementById('add-error-alert');
            alertEl.textContent = 'Failed to create offering.';
            alertEl.classList.remove('hidden');
        }
    });

    function confirmDeleteOffering(id, code) {
        const form = document.getElementById('delete-offering-form');
        form.action = "<?= url('views/registrar/subject-offerings/actions/delete.php') ?>";
        document.getElementById('delete-offering-id').value = id;
        document.getElementById('delete-offering-target').textContent = '"' + code + '"';
        document.getElementById('delete-offering-modal').showModal();
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
</script>
