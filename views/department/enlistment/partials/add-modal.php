<dialog id="add-subject-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-slate-800">Add Subject</h3>
            <button onclick="document.getElementById('add-subject-modal').close()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <div id="add-error-alert" class="hidden text-sm rounded-xl px-4 py-3 bg-red-50 border border-red-200 text-red-600 mb-4"></div>
        <form id="add-subject-form" method="POST" action="<?= url('api/department/enlistment/store.php') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">
            <input type="hidden" name="academic_term_id" value="<?= (int) $termId ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject Offering</label>
                <select name="subject_offering_id" id="add-subject_offering_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-white" disabled>
                    <option value="">Loading offerings...</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('add-subject-modal').close()" class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">Enlist Subject</button>
            </div>
        </form>
    </div>
</dialog>
