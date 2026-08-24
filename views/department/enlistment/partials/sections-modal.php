<dialog id="sections-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-slate-800">Add by Section</h3>
            <button onclick="document.getElementById('sections-modal').close()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <div id="sections-modal-message" class="hidden mb-4"></div>
        <div id="sections-list" class="space-y-2">
            <p class="text-sm text-slate-400 py-4 text-center">Loading sections...</p>
        </div>
        <div class="flex justify-end mt-4">
            <button onclick="document.getElementById('sections-modal').close()" class="px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50">Close</button>
        </div>
    </div>
</dialog>
