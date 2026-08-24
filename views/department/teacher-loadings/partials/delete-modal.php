<dialog id="delete-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-base font-semibold text-slate-800 mb-2">Delete Loading</h3>
        <p class="text-sm text-slate-600 mb-4">Are you sure you want to delete loading <span id="delete-target" class="font-mono font-medium"></span>? This action cannot be undone.</p>
        <form id="delete-form" method="POST" action="<?= url('views/department/teacher-loadings/actions/delete.php') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="delete-id" value="">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('delete-modal').close()" class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
            </div>
        </form>
    </div>
</dialog>
