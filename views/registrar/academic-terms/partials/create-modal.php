<dialog id="create-academic-term-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <form method="POST" action="<?= url('views/registrar/academic-terms/actions/store.php') ?>">
            <?= csrf_field() ?>
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">New Academic Term</h3>
                <button type="button" onclick="document.getElementById('create-academic-term-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Code</label>
                        <input type="text" name="code" value="<?= e(old('code')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required maxlength="255" placeholder="e.g. 2024-1st">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                            <option value="semester" <?= old('type','semester')==='semester'?'selected':'' ?>>Semester</option>
                            <option value="trimestral" <?= old('type')==='trimestral'?'selected':'' ?>>Trimestral</option>
                            <option value="summer" <?= old('type')==='summer'?'selected':'' ?>>Summer</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <input type="text" name="description" value="<?= e(old('description')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required maxlength="255" placeholder="e.g. First Semester 2024">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">School Year</label>
                    <select name="school_year_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="">Select school year</option>
                        <?php foreach ($schoolYears as $sy): ?>
                            <option value="<?= e($sy['id']) ?>" <?= old('school_year_id') == $sy['id'] ? 'selected' : '' ?>><?= e($sy['code']) ?> - <?= e($sy['description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Department (optional, leave empty for All)</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= e($dept['id']) ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>><?= e($dept['code']) ?> - <?= e($dept['description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="<?= e(old('start_date')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
                        <input type="date" name="end_date" value="<?= e(old('end_date')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="active" <?= old('status','active')==='active'?'selected':'' ?>>Active</option>
                        <option value="inactive" <?= old('status')==='inactive'?'selected':'' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-6 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('create-academic-term-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">Create</button>
            </div>
        </form>
    </div>
</dialog>
