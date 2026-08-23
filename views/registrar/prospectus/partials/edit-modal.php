<dialog id="edit-prospectus-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full">
        <form method="POST" id="edit-prospectus-form">
            <?= csrf_field() ?>
            <input type="hidden" name="prospectus_id" id="edit-prospectus-id">
            <input type="hidden" name="curriculum_id" id="edit-prospectus-curriculum_id">
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Edit Prospectus Entry</h3>
                    <p class="text-sm text-slate-500">Curriculum: <span id="edit-prospectus-curriculum-display" class="font-medium text-slate-700">—</span></p>
                </div>
                <button type="button" onclick="document.getElementById('edit-prospectus-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Level</label>
                    <select name="level_id" id="edit-level_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="">Select level</option>
                        <?php foreach ($allLevels as $lvl): ?>
                            <option value="<?= e($lvl['id']) ?>"><?= e($lvl['code'] ?? '') ?> - <?= e($lvl['description'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Academic Term</label>
                    <select name="term_id" id="edit-term_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="">Select term</option>
                        <?php foreach ($termsFull as $term): ?>
                            <option value="<?= e($term['id']) ?>"><?= e($term['description'] ?? $term['code']) ?> <?= isset($term['school_year_code']) ? '(' . e($term['school_year_code']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                    <select name="subject_id" id="edit-subject_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="">Select subject</option>
                        <?php foreach ($subjects as $subj): ?>
                            <option value="<?= e($subj['id']) ?>"><?= e($subj['code']) ?> - <?= e($subj['description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select name="status" id="edit-prospectus-status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-6 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('edit-prospectus-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">Update</button>
            </div>
        </form>
    </div>
</dialog>
