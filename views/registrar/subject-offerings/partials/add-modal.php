<dialog id="add-offering-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <form method="POST" id="add-offering-form" action="<?= url('views/registrar/subject-offerings/actions/store.php') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="academic_term_id" id="add-offering-academic_term_id">
            <input type="hidden" name="subject_id" id="add-offering-subject_id">
            <input type="hidden" name="prospectus_id" id="add-offering-prospectus_id">
            <input type="hidden" name="department_id" id="add-offering-department_id">
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Add Subject Offering</h3>
                    <p class="text-sm text-slate-500" id="add-context-display">—</p>
                </div>
                <button type="button" onclick="document.getElementById('add-offering-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div id="add-error-alert" class="hidden p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600"></div>

                <div id="add-program-field" class="hidden">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Program</label>
                    <select name="program_id" id="add-offering-program_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <option value="">Select program</option>
                    </select>
                </div>
                <div id="add-level-field" class="hidden">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Level</label>
                    <select name="level_id" id="add-offering-level_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" disabled>
                        <option value="">Select level</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Grading System</label>
                    <select name="grading_id" id="add-offering-grading_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="">Select grading system</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Class Size</label>
                    <input type="number" name="class_size" id="add-offering-class_size" value="40" min="1" max="500" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-6 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('add-offering-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">Add Offering</button>
            </div>
        </form>
    </div>
</dialog>
