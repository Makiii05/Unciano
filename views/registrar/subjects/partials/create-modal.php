<dialog id="create-subject-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <form method="POST" action="<?= url('views/registrar/subjects/actions/store.php') ?>">
            <?= csrf_field() ?>
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">New Subject</h3>
                <button type="button" onclick="document.getElementById('create-subject-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Code</label>
                        <input type="text" name="code" value="<?= e(old('code')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required maxlength="255" placeholder="e.g. CS 101">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                        <input type="number" name="unit" value="<?= e(old('unit', '3')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <input type="text" name="description" value="<?= e(old('description')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required maxlength="255" placeholder="e.g. Introduction to Computing">
                </div>
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lech</label>
                        <input type="number" name="lech" value="<?= e(old('lech', '0')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lecu</label>
                        <input type="number" name="lecu" value="<?= e(old('lecu', '0')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Labh</label>
                        <input type="number" name="labh" value="<?= e(old('labh', '0')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Labu</label>
                        <input type="number" name="labu" value="<?= e(old('labu', '0')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                            <option value="lecture" <?= old('type','lecture')==='lecture'?'selected':'' ?>>Lecture</option>
                            <option value="laboratory" <?= old('type')==='laboratory'?'selected':'' ?>>Laboratory</option>
                            <option value="lecture_lab" <?= old('type')==='lecture_lab'?'selected':'' ?>>Lecture Lab</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Education Level</label>
                        <select name="education_level" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            <option value="college" <?= old('education_level','college')==='college'?'selected':'' ?>>College</option>
                            <option value="K12" <?= old('education_level')==='K12'?'selected':'' ?>>K12</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                            <option value="active" <?= old('status','active')==='active'?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= old('status')==='inactive'?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-6 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('create-subject-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">Create</button>
            </div>
        </form>
    </div>
</dialog>
