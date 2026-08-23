<dialog id="create-user-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <form method="POST" action="<?= url('views/admin/accounts/actions/store-user.php') ?>">
            <?= csrf_field() ?>
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">New Faculty Account</h3>
                <button type="button" onclick="document.getElementById('create-user-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                    <input type="text" name="name" value="<?= e(old('name')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?= e(old('email')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Office</label>
                    <select name="type" id="office-type" onchange="toggleDepartmentField()" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="">Select office</option>
                        <option value="admin" <?= old('type') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="registrar" <?= old('type') === 'registrar' ? 'selected' : '' ?>>Registrar</option>
                        <option value="accounting" <?= old('type') === 'accounting' ? 'selected' : '' ?>>Accounting</option>
                        <option value="admission" <?= old('type') === 'admission' ? 'selected' : '' ?>>Admission</option>
                        <option value="department" <?= old('type') === 'department' ? 'selected' : '' ?>>Department</option>
                    </select>
                </div>
                <div id="department-field" class="hidden">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <option value="">Select department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= e($dept['id']) ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                <?= e($dept['code']) ?> - <?= e($dept['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <option value="">Select role (optional)</option>
                        <option value="head" <?= old('role') === 'head' ? 'selected' : '' ?>>Head</option>
                        <option value="proctor" <?= old('role') === 'proctor' ? 'selected' : '' ?>>Proctor</option>
                        <option value="interviewer" <?= old('role') === 'interviewer' ? 'selected' : '' ?>>Interviewer</option>
                        <option value="guidance" <?= old('role') === 'guidance' ? 'selected' : '' ?>>Guidance</option>
                        <option value="principal" <?= old('role') === 'principal' ? 'selected' : '' ?>>Principal</option>
                        <option value="secretary" <?= old('role') === 'secretary' ? 'selected' : '' ?>>Secretary</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                </div>
            </div>

            <div class="flex justify-end gap-2 p-6 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('create-user-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">Create</button>
            </div>
        </form>
    </div>
</dialog>

<script>
    function toggleDepartmentField() {
        const typeEl = document.getElementById('office-type');
        const field = document.getElementById('department-field');
        if (!typeEl || !field) return;
        field.classList.toggle('hidden', typeEl.value !== 'department');
    }
    // Defer until DOM is ready for the portal layout case where this partial loads inside tab
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', toggleDepartmentField);
    } else {
        toggleDepartmentField();
    }
</script>

