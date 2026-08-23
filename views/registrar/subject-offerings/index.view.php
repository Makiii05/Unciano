<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <form method="GET" action="<?= url('views/registrar/subject-offerings/index.php') ?>" id="subject-offering-filter-form" class="flex flex-wrap items-end gap-4">
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
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Apply</button>
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
                        <?php $grouped = []; foreach ($prospectus as $p) { $grouped[$p['level_id'] ?? 0][$p['term_id'] ?? 0][] = $p; } ?>
                        <?php foreach ($grouped as $levelItems): ?>
                            <?php $levelFirst = reset($levelItems)[0]; ?>
                            <details class="border border-slate-200 rounded-lg overflow-hidden" open>
                                <summary class="cursor-pointer px-4 py-3 bg-slate-50 text-sm font-semibold text-slate-800">
                                    <?= e(($levelFirst['program_code'] ?? '') . ' - ' . ($levelFirst['level_code'] ?? $levelFirst['level_description'] ?? 'Level')) ?>
                                </summary>
                                <?php foreach ($levelItems as $termItems): ?>
                                    <?php $termFirst = $termItems[0]; ?>
                                    <div class="border-t border-slate-200">
                                        <h5 class="px-4 py-3 text-sm font-medium text-slate-700"><?= e($termFirst['term_description'] ?? $termFirst['term_code'] ?? 'Term') ?></h5>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left">
                                                <thead class="bg-slate-50/70 text-xs uppercase tracking-wider text-slate-500">
                                                    <tr><th class="px-4 py-2 font-medium">Subject</th><th class="px-4 py-2 font-medium">Units</th><th class="px-4 py-2 text-right font-medium">Action</th></tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($termItems as $p): ?>
                                                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                                                            <td class="px-4 py-3"><p class="text-sm font-medium text-slate-800"><?= e($p['subject_code'] ?? '—') ?> - <?= e($p['subject_description'] ?? '—') ?></p></td>
                                                            <td class="px-4 py-3 text-sm text-slate-600"><?= e($p['unit'] ?? '—') ?></td>
                                                            <td class="px-4 py-3 text-right"><button onclick="openAddProspectusModal(<?= (int) $p['id'] ?>, <?= (int) $p['subject_id'] ?>)" class="px-3 py-1 text-xs font-medium bg-primary-600 hover:bg-primary-700 text-white rounded">Add</button></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </details>
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
                        <div data-offering-id="<?= (int) $off['id'] ?>" class="border border-slate-200 rounded-lg p-3 flex items-center justify-between">
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
    const departmentDataUrl = "<?= url('api/subject-offerings/department-data.php') ?>";
    const offeringsUrl = "<?= url('api/subject-offerings/offerings-by-term.php') ?>";
    const searchUrl = "<?= url('api/subject-offerings/search-subjects.php') ?>";
    const levelsUrl = "<?= url('api/subject-offerings/levels-by-program.php') ?>";
    let programs = <?= json_encode($programs ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    let gradingSystems = <?= json_encode($gradingSystems ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
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

    const filterForm = document.getElementById('subject-offering-filter-form');
    const departmentSelect = document.getElementById('filter-department');
    const termSelect = document.getElementById('filter-term');
    const curriculumSelect = document.getElementById('filter-curriculum');

    function setSelectMessage(select, message) {
        select.innerHTML = '<option value="">' + escapeHtml(message) + '</option>';
        select.disabled = true;
    }

    function updateFilterUrl() {
        const params = new URLSearchParams();
        if (departmentSelect.value) params.set('department_id', departmentSelect.value);
        if (termSelect.value) params.set('term', termSelect.value);
        if (curriculumSelect.value) params.set('curriculum', curriculumSelect.value);
        if (currentTab) params.set('tab', currentTab);
        window.history.replaceState({}, '', window.location.pathname + (params.toString() ? '?' + params : ''));
    }

    function populateSelect(select, items, label, formatter) {
        select.innerHTML = '<option value="">Select ' + label + '</option>';
        items.forEach(function(item) {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = formatter(item);
            select.appendChild(option);
        });
        select.disabled = false;
    }

    async function loadDepartmentData(departmentId) {
        if (!departmentId) {
            setSelectMessage(termSelect, 'Select department first');
            setSelectMessage(curriculumSelect, 'Select department first');
            renderProspectus([]);
            renderOfferings([]);
            return;
        }
        setSelectMessage(termSelect, 'Loading terms...');
        setSelectMessage(curriculumSelect, 'Loading curricula...');
        try {
            const response = await fetch(departmentDataUrl + '?department_id=' + encodeURIComponent(departmentId));
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Unable to load department data.');
            populateSelect(termSelect, result.data.terms || [], 'term', function(term) {
                return (term.code || term.description || 'Term') + ' - ' + (term.description || '');
            });
            populateSelect(curriculumSelect, result.data.curricula || [], 'curriculum', function(curriculum) {
                return curriculum.curriculum || 'Curriculum';
            });
            programs = result.data.programs || [];
            gradingSystems = result.data.grading_systems || [];
            renderProspectus([]);
            renderOfferings([]);
        } catch (error) {
            setSelectMessage(termSelect, 'Unable to load terms');
            setSelectMessage(curriculumSelect, 'Unable to load curricula');
            renderProspectusMessage(error.message);
            renderOfferingsMessage(error.message);
        }
    }

    function renderProspectusMessage(message) {
        document.getElementById('prospectus-results').innerHTML = '<p class="text-center text-slate-400 py-8">' + escapeHtml(message) + '</p>';
    }

    function renderProspectus(items) {
        if (!items.length) {
            renderProspectusMessage(curriculumSelect.value ? 'No prospectus subjects found.' : 'Select a curriculum to view prospectus.');
            return;
        }
        const grouped = {};
        items.forEach(function(item) {
            const levelKey = item.level_id || 'unknown-level';
            const termKey = item.term_id || 'unknown-term';
            if (!grouped[levelKey]) grouped[levelKey] = { label: (item.program_code ? item.program_code + ' - ' : '') + (item.level_code || item.level_description || 'Level'), terms: {} };
            if (!grouped[levelKey].terms[termKey]) grouped[levelKey].terms[termKey] = { label: item.term_description || item.term_code || 'Term', items: [] };
            grouped[levelKey].terms[termKey].items.push(item);
        });
        const html = Object.values(grouped).map(function(level) {
            const termsHtml = Object.values(level.terms).map(function(term) {
                const subjectsHtml = term.items.map(function(item) {
                    return '<tr class="border-t border-slate-100 hover:bg-slate-50"><td class="px-4 py-3"><p class="text-sm font-medium text-slate-800">' + escapeHtml(item.subject_code || item.subject_description || 'Subject') + ' - ' + escapeHtml(item.subject_description || '') + '</p></td><td class="px-4 py-3 text-sm text-slate-600">' + escapeHtml(item.unit == null ? '—' : String(item.unit)) + '</td><td class="px-4 py-3 text-right"><button onclick="openAddProspectusModal(' + Number(item.id) + ', ' + Number(item.subject_id) + ')" class="px-3 py-1 text-xs font-medium bg-primary-600 hover:bg-primary-700 text-white rounded">Add</button></td></tr>';
                }).join('');
                return '<div class="border-t border-slate-200"><h5 class="px-4 py-3 text-sm font-medium text-slate-700">' + escapeHtml(term.label) + '</h5><div class="overflow-x-auto"><table class="w-full text-left"><thead class="bg-slate-50/70 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-2 font-medium">Subject</th><th class="px-4 py-2 font-medium">Units</th><th class="px-4 py-2 text-right font-medium">Action</th></tr></thead><tbody>' + subjectsHtml + '</tbody></table></div></div>';
            }).join('');
            return '<details class="border border-slate-200 rounded-lg overflow-hidden" open><summary class="cursor-pointer px-4 py-3 bg-slate-50 text-sm font-semibold text-slate-800">' + escapeHtml(level.label) + '</summary>' + termsHtml + '</details>';
        }).join('');
        document.getElementById('prospectus-results').innerHTML = '<div id="prospectus-list" class="space-y-4">' + html + '</div>';
    }

    function renderOfferingsMessage(message) {
        document.getElementById('offering-list').innerHTML = '<p class="text-center text-slate-400 py-8">' + escapeHtml(message) + '</p>';
        document.getElementById('offering-count').textContent = '0 offering(s)';
    }

    function renderOfferings(items) {
        if (!items.length) {
            renderOfferingsMessage(termSelect.value ? 'No subject offerings for this term yet.' : 'Select an academic term to view offerings.');
            return;
        }
        document.getElementById('offering-list').innerHTML = items.map(function(item) {
            return '<div data-offering-id="' + Number(item.id) + '" class="border border-slate-200 rounded-lg p-3 flex items-center justify-between"><div><p class="text-sm font-medium text-slate-800">' + escapeHtml(item.code || '') + '</p><p class="text-xs text-slate-500">' + escapeHtml(item.subject_code || '') + ' - ' + escapeHtml(item.subject_description || '') + ' | ' + escapeHtml(item.program_code || '') + ' | Class: ' + escapeHtml(String(item.class_size || '')) + '</p></div><button onclick="confirmDeleteOffering(' + Number(item.id) + ', ' + escapeHtml(JSON.stringify(item.code || '')) + ')" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded">Delete</button></div>';
        }).join('');
        document.getElementById('offering-count').textContent = items.length + ' offering(s)';
    }

    async function loadProspectus(curriculumId) {
        if (!curriculumId) {
            renderProspectus([]);
            return;
        }
        renderProspectusMessage('Loading prospectus...');
        try {
            const response = await fetch(prospectusUrl + '?curriculum_id=' + encodeURIComponent(curriculumId));
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Unable to load prospectus.');
            renderProspectus(result.data || []);
        } catch (error) {
            renderProspectusMessage(error.message);
        }
    }

    async function loadOfferings(termId) {
        if (!termId) {
            renderOfferings([]);
            return;
        }
        renderOfferingsMessage('Loading offerings...');
        try {
            const query = '?term_id=' + encodeURIComponent(termId) + '&department_id=' + encodeURIComponent(departmentSelect.value);
            const response = await fetch(offeringsUrl + query);
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Unable to load offerings.');
            renderOfferings(result.data || []);
        } catch (error) {
            renderOfferingsMessage(error.message);
        }
    }

    departmentSelect?.addEventListener('change', async function() {
        termSelect.value = '';
        curriculumSelect.value = '';
        await loadDepartmentData(this.value);
        updateFilterUrl();
    });

    termSelect?.addEventListener('change', function() {
        loadOfferings(this.value);
        updateFilterUrl();
    });

    // Prospectus curriculum change -> fetch prospectus list
    curriculumSelect?.addEventListener('change', async function() {
        const curriculumId = this.value;
        await loadProspectus(curriculumId);
        updateFilterUrl();
    });

    filterForm?.addEventListener('submit', function(event) {
        event.preventDefault();
        updateFilterUrl();
        loadProspectus(curriculumSelect.value);
        loadOfferings(termSelect.value);
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
        let html = '<option value="">Select grading system</option>';
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

    document.getElementById('delete-offering-form')?.addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;
        const id = document.getElementById('delete-offering-id').value;
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Unable to delete offering.');
            const offering = document.querySelector('#offering-list [data-offering-id="' + id + '"]');
            if (offering) offering.remove();
            const remaining = document.querySelectorAll('#offering-list > .border').length;
            document.getElementById('offering-count').textContent = remaining + ' offering(s)';
            if (!remaining) renderOfferingsMessage('No subject offerings for this term yet.');
            document.getElementById('delete-offering-modal').close();
            loadOfferings(termSelect.value);
        } catch (error) {
            alert(error.message);
        } finally {
            submitButton.disabled = false;
        }
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    if (curriculumSelect.value) {
        loadProspectus(curriculumSelect.value);
    }
</script>
