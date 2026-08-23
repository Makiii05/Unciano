<dialog id="edit-grading-system-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full">
        <form method="POST" id="edit-grading-system-form">
            <?= csrf_field() ?>
            <input type="hidden" name="grading_system_id" id="edit-grading-system-id">
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Edit Grading System</h3>
                <button type="button" onclick="document.getElementById('edit-grading-system-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <input type="text" name="description" id="edit-grading-system-description" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required maxlength="255">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                    <select name="department_id" id="edit-grading-system-department_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="">Select department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= e($dept['id']) ?>"><?= e($dept['code']) ?> - <?= e($dept['description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Components</label>
                    <div id="edit-components-container" class="border border-slate-200 rounded-lg p-3 space-y-2 max-h-40 overflow-y-auto">
                        <p class="text-sm text-slate-400">Loading...</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Total Percentage</label>
                        <input type="text" id="edit-total-percentage" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 outline-none" readonly value="0.00">
                    </div>
                    <div id="edit-warning" class="hidden">
                        <p class="text-sm text-red-600 mt-6">Total exceeds 100%!</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-6 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('edit-grading-system-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">Update</button>
            </div>
        </form>
    </div>
</dialog>
