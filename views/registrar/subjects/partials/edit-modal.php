<dialog id="edit-subject-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full">
        <form method="POST" id="edit-subject-form">
            <?= csrf_field() ?>
            <input type="hidden" name="subject_id" id="edit-subject-id">
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Edit Subject</h3>
                <button type="button" onclick="document.getElementById('edit-subject-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Code</label>
                        <input type="text" name="code" id="edit-subject-code" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required maxlength="255">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                        <input type="number" name="unit" id="edit-subject-unit" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <input type="text" name="description" id="edit-subject-description" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required maxlength="255">
                </div>
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lech</label>
                        <input type="number" name="lech" id="edit-subject-lech" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lecu</label>
                        <input type="number" name="lecu" id="edit-subject-lecu" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Labh</label>
                        <input type="number" name="labh" id="edit-subject-labh" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Labu</label>
                        <input type="number" name="labu" id="edit-subject-labu" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required min="0">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select name="type" id="edit-subject-type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                            <option value="lecture">Lecture</option>
                            <option value="laboratory">Laboratory</option>
                            <option value="lecture_lab">Lecture Lab</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Education Level</label>
                        <select name="education_level" id="edit-subject-education_level" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            <option value="college">College</option>
                            <option value="K12">K12</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" id="edit-subject-status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-6 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('edit-subject-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">Update</button>
            </div>
        </form>
    </div>
</dialog>
