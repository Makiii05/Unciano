<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">Subjects</h3>
            <p class="text-sm text-slate-500"><?= count($subjects) ?> subject(s)</p>
        </div>
        <button onclick="document.getElementById('create-subject-modal').showModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Subject
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 text-left font-medium">Code</th>
                    <th class="px-6 py-4 text-left font-medium">Description</th>
                    <th class="px-6 py-4 text-left font-medium">Unit</th>
                    <th class="px-6 py-4 text-left font-medium">Type</th>
                    <th class="px-6 py-4 text-left font-medium">Education Level</th>
                    <th class="px-6 py-4 text-left font-medium">Status</th>
                    <th class="px-6 py-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                    <tr><td colspan="7" class="py-12 text-center text-slate-400">No subjects found.</td></tr>
                <?php else: ?>
                    <?php foreach ($subjects as $subj): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($subj['code']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($subj['description']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($subj['unit']) ?></td>
                            <td class="px-6 py-3.5 text-slate-600 capitalize"><?= e(str_replace('_', ' ', $subj['type'] ?? '')) ?></td>
                            <td class="px-6 py-3.5 text-slate-600"><?= e($subj['education_level'] ?? 'college') ?></td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($subj['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= e(ucfirst($subj['status'] ?? 'unknown')) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-1">
                                <button onclick="showPrerequisites(<?= (int) $subj['id'] ?>, <?= htmlspecialchars(json_encode($subj['code']), ENT_QUOTES) ?>)" class="px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded transition-colors">Prerequisites</button>
                                <button onclick="editSubject(<?= (int) $subj['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                <button onclick="confirmDeleteSubject(<?= (int) $subj['id'] ?>, <?= htmlspecialchars(json_encode($subj['code']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
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
<?php include __DIR__ . '/partials/prerequisites-modal.php'; ?>

<script>
    const subjects = <?= json_encode($subjects, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    let prereqContextSubjectId = null;
    let prereqSearchTimer = null;

    function editSubject(id) {
        const s = subjects.find(function(x) { return parseInt(x.id) === id; });
        if (!s) return;
        document.getElementById('edit-subject-form').action = "<?= url('views/registrar/subjects/actions/update.php') ?>";
        document.getElementById('edit-subject-id').value = s.id;
        document.getElementById('edit-subject-code').value = s.code || '';
        document.getElementById('edit-subject-description').value = s.description || '';
        document.getElementById('edit-subject-unit').value = s.unit || 0;
        document.getElementById('edit-subject-lech').value = s.lech || 0;
        document.getElementById('edit-subject-lecu').value = s.lecu || 0;
        document.getElementById('edit-subject-labh').value = s.labh || 0;
        document.getElementById('edit-subject-labu').value = s.labu || 0;
        document.getElementById('edit-subject-type').value = s.type || 'lecture';
        document.getElementById('edit-subject-education_level').value = s.education_level || 'college';
        document.getElementById('edit-subject-status').value = s.status || 'active';
        document.getElementById('edit-subject-modal').showModal();
    }

    function confirmDeleteSubject(id, code) {
        const form = document.getElementById('delete-subject-form');
        form.action = "<?= url('views/registrar/subjects/actions/delete.php') ?>";
        document.getElementById('delete-subject-id').value = id;
        document.getElementById('delete-subject-target').textContent = '"' + code + '"';
        document.getElementById('delete-subject-modal').showModal();
    }

    async function showPrerequisites(subjectId, code) {
        prereqContextSubjectId = subjectId;
        document.getElementById('prerequisites-subject-label').textContent = code;
        document.getElementById('prerequisites-message').classList.add('hidden');
        document.getElementById('prerequisites-message').textContent = '';
        document.getElementById('prerequisite-search').value = '';
        document.getElementById('prerequisite-results').innerHTML = '';
        document.getElementById('prerequisite-results').classList.add('hidden');
        document.getElementById('prerequisites-list').innerHTML = '<p class="text-center text-slate-400 py-6">Loading...</p>';
        document.getElementById('prerequisites-modal').showModal();
        try {
            const res = await fetch("<?= url('api/subjects/prerequisites.php') ?>?subject_id=" + subjectId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (data.success) {
                document.getElementById('prerequisites-list').innerHTML = data.html;
            } else {
                document.getElementById('prerequisites-list').innerHTML = '<p class="text-center text-red-500 py-4">' + (data.message || 'Failed to load.') + '</p>';
            }
        } catch (e) {
            document.getElementById('prerequisites-list').innerHTML = '<p class="text-center text-red-500 py-4">Failed to load.</p>';
        }
    }

    function showPrereqMessage(msg, isError) {
        const el = document.getElementById('prerequisites-message');
        el.textContent = msg;
        el.className = 'mb-3 p-2 rounded text-sm ' + (isError ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200');
        el.classList.remove('hidden');
    }

    async function searchPrerequisites(q) {
        if (!prereqContextSubjectId) return;
        q = q.trim();
        const resultsEl = document.getElementById('prerequisite-results');
        if (q === '') {
            resultsEl.innerHTML = '';
            resultsEl.classList.add('hidden');
            return;
        }
        try {
            const res = await fetch("<?= url('api/subjects/search-prerequisites.php') ?>?subject_id=" + prereqContextSubjectId + "&q=" + encodeURIComponent(q));
            const data = await res.json();
            if (data.success && data.data.length > 0) {
                let html = '<div class="border border-slate-200 rounded-lg divide-y divide-slate-100 max-h-40 overflow-y-auto">';
                data.data.forEach(function(item) {
                    html += '<div class="flex items-center justify-between px-3 py-2 hover:bg-slate-50"><div><p class="text-sm font-medium text-slate-800">' + escapeHtml(item.code) + '</p><p class="text-xs text-slate-500">' + escapeHtml(item.description) + '</p></div><button onclick="addPrerequisite(' + item.id + ')" class="px-3 py-1 text-xs font-medium bg-primary-600 hover:bg-primary-700 text-white rounded">Add</button></div>';
                });
                html += '</div>';
                resultsEl.innerHTML = html;
                resultsEl.classList.remove('hidden');
            } else {
                resultsEl.innerHTML = '<p class="text-sm text-slate-400 py-2">No subjects found.</p>';
                resultsEl.classList.remove('hidden');
            }
        } catch (e) {
            resultsEl.innerHTML = '<p class="text-sm text-red-500">Search failed.</p>';
            resultsEl.classList.remove('hidden');
        }
    }

    async function addPrerequisite(prereqId) {
        if (!prereqContextSubjectId) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const res = await fetch("<?= url('api/subjects/store-prerequisite.php') ?>", {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token },
                body: "_token=" + encodeURIComponent(token) + "&subject_id=" + prereqContextSubjectId + "&prerequisite_subject_id=" + prereqId
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('prerequisites-list').innerHTML = data.html;
                document.getElementById('prerequisite-results').innerHTML = '';
                document.getElementById('prerequisite-results').classList.add('hidden');
                document.getElementById('prerequisite-search').value = '';
                showPrereqMessage(data.message, false);
            } else {
                showPrereqMessage(data.message || 'Failed.', true);
            }
        } catch (e) {
            showPrereqMessage('Failed to add.', true);
        }
    }

    async function removePrerequisite(btn) {
        if (!prereqContextSubjectId) return;
        const prereqRowId = btn.getAttribute('data-prereq-id');
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const res = await fetch("<?= url('api/subjects/destroy-prerequisite.php') ?>", {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token },
                body: "_token=" + encodeURIComponent(token) + "&subject_id=" + prereqContextSubjectId + "&prerequisite_id=" + prereqRowId
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('prerequisites-list').innerHTML = data.html;
                showPrereqMessage(data.message, false);
            } else {
                showPrereqMessage(data.message || 'Failed.', true);
            }
        } catch (e) {
            showPrereqMessage('Failed to remove.', true);
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchEl = document.getElementById('prerequisite-search');
        if (searchEl) {
            searchEl.addEventListener('input', function(e) {
                clearTimeout(prereqSearchTimer);
                prereqSearchTimer = setTimeout(function() { searchPrerequisites(e.target.value); }, 300);
            });
        }
    });
</script>
