<dialog id="delete-component-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full">
        <form method="POST" id="delete-component-form">
            <?= csrf_field() ?>
            <input type="hidden" name="component_id" id="delete-component-id">
            <div class="p-6 text-center">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 text-red-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2">Delete Component</h3>
                <p class="text-sm text-slate-500 mb-1">Are you sure you want to delete</p>
                <p class="text-sm font-medium text-slate-700" id="delete-component-target"></p>
                <p class="text-xs text-slate-400 mt-3">Cannot delete if still used by grading systems.</p>
            </div>
            <div class="flex justify-center gap-2 p-6 pt-2">
                <button type="button" onclick="document.getElementById('delete-component-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">Delete</button>
            </div>
        </form>
    </div>
</dialog>
