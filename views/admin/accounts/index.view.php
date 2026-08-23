<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="border-b border-slate-200">
        <div class="flex">
            <button id="tab-faculty-btn" onclick="switchTab('faculty')" class="tab-btn px-6 py-4 text-sm font-medium transition-colors border-b-2 border-primary-600 text-primary-600">Faculty Accounts (<?= count($users) ?>)</button>
            <button id="tab-teacher-btn" onclick="switchTab('teacher')" class="tab-btn px-6 py-4 text-sm font-medium transition-colors text-slate-500 hover:text-slate-700">Teacher Accounts (<?= count($teacherAccounts) ?>)</button>
            <button id="tab-student-btn" onclick="switchTab('student')" class="tab-btn px-6 py-4 text-sm font-medium transition-colors text-slate-500 hover:text-slate-700">Student Accounts (<?= count($studentAccounts) ?>)</button>
        </div>
    </div>

    <!-- Faculty Tab -->
    <div id="tab-faculty">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <p class="text-sm text-slate-500"><?= count($users) ?> faculty account(s)</p>
            <button onclick="document.getElementById('create-user-modal').showModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Faculty Account
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 text-left font-medium">Name</th>
                        <th class="px-6 py-4 text-left font-medium">Email</th>
                        <th class="px-6 py-4 text-left font-medium">Type</th>
                        <th class="px-6 py-4 text-left font-medium">Status</th>
                        <th class="px-6 py-4 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="5" class="py-12 text-center text-slate-400">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($user['name']) ?></td>
                                <td class="px-6 py-3.5 text-slate-600"><?= e($user['email']) ?></td>
                                <td class="px-6 py-3.5 capitalize text-slate-600"><?= e($user['type'] ?? 'N/A') ?></td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($user['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>">
                                        <?= e(ucfirst($user['status'] ?? 'unknown')) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right space-x-1">
                                    <button onclick="editUser(<?= (int) $user['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                    <?php if (($user['type'] ?? '') !== 'admin'): ?>
                                        <button onclick="confirmDelete('user', <?= (int) $user['id'] ?>, <?= htmlspecialchars(json_encode($user['name']), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Teacher Tab -->
    <div id="tab-teacher" class="hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 text-left font-medium">Teacher</th>
                        <th class="px-6 py-4 text-left font-medium">Email</th>
                        <th class="px-6 py-4 text-left font-medium">Status</th>
                        <th class="px-6 py-4 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teacherAccounts)): ?>
                        <tr><td colspan="4" class="py-12 text-center text-slate-400">No teacher accounts found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($teacherAccounts as $account): ?>
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5 font-medium text-slate-800">
                                    <?= e(trim(($account['first_name'] ?? '') . ' ' . ($account['last_name'] ?? '')) ?: 'N/A') ?>
                                </td>
                                <td class="px-6 py-3.5 text-slate-600"><?= e($account['teacher_email'] ?? 'N/A') ?></td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($account['status'] ?? '') === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= e(ucfirst($account['status'] ?? 'unknown')) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right space-x-1">
                                    <button onclick="editTeacherAccount(<?= (int) $account['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                    <button onclick="confirmDelete('teacher', <?= (int) $account['id'] ?>, <?= htmlspecialchars(json_encode(trim(($account['first_name'] ?? '') . ' ' . ($account['last_name'] ?? ''))), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Student Tab -->
    <div id="tab-student" class="hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-4 text-left font-medium">Student</th>
                        <th class="px-6 py-4 text-left font-medium">Student Number</th>
                        <th class="px-6 py-4 text-left font-medium">Level</th>
                        <th class="px-6 py-4 text-left font-medium">Program</th>
                        <th class="px-6 py-4 text-left font-medium">Department</th>
                        <th class="px-6 py-4 text-left font-medium">Gender</th>
                        <th class="px-6 py-4 text-left font-medium">Account Status</th>
                        <th class="px-6 py-4 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($studentAccounts)): ?>
                        <tr><td colspan="8" class="py-12 text-center text-slate-400">No student accounts found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($studentAccounts as $account): ?>
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5 font-medium text-slate-800"><?= e(trim(($account['first_name'] ?? '') . ' ' . ($account['last_name'] ?? '')) ?: 'N/A') ?></td>
                                <td class="px-6 py-3.5 text-slate-600"><?= e($account['student_number'] ?? 'N/A') ?></td>
                                <td class="px-6 py-3.5 text-slate-600"><?= e($account['level_description'] ?? '—') ?></td>
                                <td class="px-6 py-3.5 text-slate-600"><?= e($account['program_code'] ?? '—') ?></td>
                                <td class="px-6 py-3.5 text-slate-600"><?= e($account['department_code'] ?? '—') ?></td>
                                <td class="px-6 py-3.5 text-slate-600"><?= e(isset($account['sex']) ? ucfirst($account['sex']) : '—') ?></td>
                                <td class="px-6 py-3.5">
                                    <?php $isActive = in_array($account['account_status'] ?? '', ['on', 'active'], true); ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $isActive ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>">
                                        <?= e(ucfirst($account['account_status'] ?? 'unknown')) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right space-x-1">
                                    <button onclick="editStudentAccount(<?= (int) $account['id'] ?>)" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded transition-colors">Edit</button>
                                    <button onclick="confirmDelete('student', <?= (int) $account['id'] ?>, <?= htmlspecialchars(json_encode(trim(($account['first_name'] ?? '') . ' ' . ($account['last_name'] ?? ''))), ENT_QUOTES) ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/create-user-modal.php'; ?>
<?php include __DIR__ . '/partials/edit-user-modal.php'; ?>
<?php include __DIR__ . '/partials/edit-teacher-modal.php'; ?>
<?php include __DIR__ . '/partials/edit-student-modal.php'; ?>
<?php include __DIR__ . '/partials/change-password-modal.php'; ?>
<?php include __DIR__ . '/partials/delete-modal.php'; ?>

<script>
    const users = <?= json_encode($users, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const teacherAccounts = <?= json_encode($teacherAccounts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const studentAccounts = <?= json_encode($studentAccounts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function switchTab(tab) {
        document.getElementById('tab-faculty').classList.toggle('hidden', tab !== 'faculty');
        document.getElementById('tab-teacher').classList.toggle('hidden', tab !== 'teacher');
        document.getElementById('tab-student').classList.toggle('hidden', tab !== 'student');

        const active = 'border-b-2 border-primary-600 text-primary-600';
        const inactive = 'text-slate-500 hover:text-slate-700';
        ['faculty','teacher','student'].forEach(function(t) {
            const btn = document.getElementById('tab-' + t + '-btn');
            if (t === tab) {
                btn.className = 'tab-btn px-6 py-4 text-sm font-medium transition-colors ' + active;
            } else {
                btn.className = 'tab-btn px-6 py-4 text-sm font-medium transition-colors ' + inactive;
            }
        });
    }

    function editUser(id) {
        const user = users.find(function(u) { return parseInt(u.id) === id; });
        if (!user) return;
        document.getElementById('edit-user-form').action = "<?= url('views/admin/accounts/actions/update-user.php') ?>";
        document.getElementById('edit-user-id').value = user.id;
        document.getElementById('edit-user-name').value = user.name || '';
        document.getElementById('edit-user-email').value = user.email || '';
        document.getElementById('edit-user-status').value = user.status || 'active';
        document.getElementById('change-password-btn').onclick = function() {
            document.getElementById('change-password-form').action = "<?= url('views/admin/accounts/actions/change-password-user.php') ?>";
            document.getElementById('change-password-account-id').value = user.id;
            document.getElementById('edit-modal').close();
            document.getElementById('change-password-modal').showModal();
        };
        document.getElementById('edit-modal').showModal();
    }

    function editTeacherAccount(id) {
        const account = teacherAccounts.find(function(a) { return parseInt(a.id) === id; });
        if (!account) return;
        document.getElementById('edit-teacher-form').action = "<?= url('views/admin/accounts/actions/update-teacher.php') ?>";
        document.getElementById('edit-teacher-id').value = account.id;
        document.getElementById('edit-teacher-status').value = account.status || 'open';
        document.getElementById('change-password-btn-teacher').onclick = function() {
            document.getElementById('change-password-form').action = "<?= url('views/admin/accounts/actions/change-password-teacher.php') ?>";
            document.getElementById('change-password-account-id').value = account.id;
            document.getElementById('edit-teacher-modal').close();
            document.getElementById('change-password-modal').showModal();
        };
        document.getElementById('edit-teacher-modal').showModal();
    }

    function editStudentAccount(id) {
        const account = studentAccounts.find(function(a) { return parseInt(a.id) === id; });
        if (!account) return;
        document.getElementById('edit-student-form').action = "<?= url('views/admin/accounts/actions/update-student.php') ?>";
        document.getElementById('edit-student-id').value = account.id;
        document.getElementById('edit-student-account_status').value = account.account_status || 'off';
        document.getElementById('change-password-btn-student').onclick = function() {
            document.getElementById('change-password-form').action = "<?= url('views/admin/accounts/actions/change-password-student.php') ?>";
            document.getElementById('change-password-account-id').value = account.id;
            document.getElementById('edit-student-modal').close();
            document.getElementById('change-password-modal').showModal();
        };
        document.getElementById('edit-student-modal').showModal();
    }

    function confirmDelete(type, id, name) {
        const form = document.getElementById('delete-form');
        if (type === 'user') form.action = "<?= url('views/admin/accounts/actions/delete-user.php') ?>";
        else if (type === 'teacher') form.action = "<?= url('views/admin/accounts/actions/delete-teacher.php') ?>";
        else if (type === 'student') form.action = "<?= url('views/admin/accounts/actions/delete-student.php') ?>";
        document.getElementById('delete-id').value = id;
        document.getElementById('delete-user-id').value = id;
        document.getElementById('delete-teacher-id').value = id;
        document.getElementById('delete-student-id').value = id;
        document.getElementById('delete-target').textContent = '"' + name + '"';
        document.getElementById('delete-modal').showModal();
    }
</script>
