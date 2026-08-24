<dialog id="assign-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-slate-800">Assign Subject Offering</h3>
            <button onclick="document.getElementById('assign-modal').close()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <div id="assign-error-alert" class="hidden text-sm rounded-xl px-4 py-3 bg-red-50 border border-red-200 text-red-600 mb-4"></div>
        <form id="assign-form" method="POST" action="<?= url('api/department/teacher-loadings/store.php') ?>">
            <?= csrf_field() ?>
            <div class="mb-4 relative">
                <label class="block text-sm font-medium text-slate-700 mb-1">Teacher</label>
                <input type="text" id="assign-teacher-search" placeholder="Search teacher by code or name..." autocomplete="off" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                <input type="hidden" name="teacher_id" id="assign-teacher_id" required>
                <div id="assign-teacher-results" class="hidden absolute z-10 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto mt-1"></div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Academic Term</label>
                <select name="academic_term_id" id="assign-academic_term_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-white" required>
                    <option value="">Select academic term</option>
                    <?php foreach ($terms as $t): ?>
                        <option value="<?= (int)$t['id'] ?>"><?= e($t['description']) ?> <?= !empty($t['sy_description']) ? '('.e($t['sy_description']).')' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject Offering</label>
                <select name="offering_id" id="assign-offering_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-white" disabled required>
                    <option value="">Select academic term first</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('assign-modal').close()" class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">Assign</button>
            </div>
        </form>
    </div>
</dialog>
