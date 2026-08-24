<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="GET" action="<?= url('views/department/enlistment/index.php') ?>">
            <div class="mb-4 flex justify-end">
                <div>
                    <label for="academic-term-select" class="block text-sm font-medium text-slate-700 mb-1">Academic Term</label>
                    <select name="term" id="academic-term-select" class="select select-bordered w-72 border border-slate-300 rounded-lg px-3 py-2 bg-white" onchange="this.form.submit()">
                        <option value="">Select academic term</option>
                        <?php foreach ($terms as $term): ?>
                            <option value="<?= (int) $term['id'] ?>" <?= (int) $termId === (int) $term['id'] ? 'selected' : '' ?>>
                                <?= e($term['description']) ?> <?= !empty($term['sy_description']) ? '(' . e($term['sy_description']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label for="student-search" class="block text-sm font-medium text-slate-700 mb-1">Search Students</label>
                <input type="text" id="student-search" placeholder="Search by student number, name, program, or level..." class="input input-bordered w-full border border-slate-300 rounded-lg px-3 py-2" autocomplete="off">
            </div>
        </form>
        <p class="text-xs text-slate-400 mt-2">Start typing to search enrolled students in this department.</p>
    </div>
</div>

<div id="results" class="hidden mt-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <p id="results-count" class="text-sm text-slate-500"></p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-4 py-4 text-left">Student No</th>
                        <th class="px-4 py-4 text-left">Name</th>
                        <th class="px-4 py-4 text-left">Level</th>
                        <th class="px-4 py-4 text-left">Program</th>
                        <th class="px-4 py-4 text-left">Department</th>
                        <th class="px-4 py-4 text-left">Gender</th>
                        <th class="px-4 py-4 text-left">Status</th>
                        <th class="px-4 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="results-body"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="no-results" class="hidden mt-6 bg-white rounded-xl shadow-sm border border-slate-200 py-12 text-center text-slate-400"></div>

<script>
    const searchInput = document.getElementById('student-search');
    const termSelect = document.getElementById('academic-term-select');
    const results = document.getElementById('results');
    const resultsBody = document.getElementById('results-body');
    const resultsCount = document.getElementById('results-count');
    const noResults = document.getElementById('no-results');
    const searchUrl = "<?= url('api/department/enlistment/search-students.php') ?>";
    let debounceTimer;

    function statusLabel(status) { return status ? status.charAt(0).toUpperCase() + status.slice(1) : '—'; }
    function sexLabel(sex) { return sex ? sex.charAt(0).toUpperCase() + sex.slice(1) : '—'; }

    function renderStudents(list) {
        if (!list.length) {
            results.classList.add('hidden');
            noResults.textContent = `No students found for "${searchInput.value.trim()}".`;
            noResults.classList.remove('hidden');
            return;
        }
        noResults.classList.add('hidden');
        resultsCount.textContent = `${list.length} student(s) found`;
        results.classList.remove('hidden');
        resultsBody.innerHTML = '';
        const term = termSelect.value;
        list.forEach(s => {
            const name = [s.last_name, s.first_name, s.middle_name].filter(Boolean).join(', ');
            const showUrl = "<?= url('views/department/enlistment/show.php') ?>?student_id=" + s.id + (term ? `&term=${term}` : '');
            const tr = document.createElement('tr');
            tr.className = 'border-t border-slate-100 hover:bg-slate-50 transition-colors';
            tr.innerHTML = `
                <td class="px-4 py-3.5 font-mono text-sm text-slate-800">${s.student_number || '—'}</td>
                <td class="px-4 py-3.5 font-medium text-slate-800">${name || '—'}</td>
                <td class="px-4 py-3.5 text-slate-600">${s.level ? s.level.description : '—'}</td>
                <td class="px-4 py-3.5 text-slate-600">${s.program ? s.program.code : '—'}</td>
                <td class="px-4 py-3.5 text-slate-600">${s.department ? s.department.code : '—'}</td>
                <td class="px-4 py-3.5 text-slate-600">${sexLabel(s.sex)}</td>
                <td class="px-4 py-3.5 text-slate-600">${statusLabel(s.status)}</td>
                <td class="px-4 py-3.5 text-right"><a href="${showUrl}" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded">View Subject</a></td>
            `;
            resultsBody.appendChild(tr);
        });
    }

    function performSearch() {
        const q = searchInput.value.trim();
        if (!q) { results.classList.add('hidden'); noResults.classList.add('hidden'); return; }
        fetch(`${searchUrl}?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(renderStudents)
            .catch(()=>{});
    }
    searchInput.addEventListener('input', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(performSearch, 300); });
</script>
