<dialog id="prerequisites-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <div>
                <h3 class="text-lg font-semibold text-slate-800">Prerequisites</h3>
                <p class="text-sm text-slate-500">Subject: <span id="prerequisites-subject-label" class="font-medium text-slate-700">—</span></p>
            </div>
            <button type="button" onclick="document.getElementById('prerequisites-modal').close()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">
            <div id="prerequisites-message" class="hidden"></div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Search subject to add as prerequisite</label>
                <input type="text" id="prerequisite-search" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" placeholder="Type code or description…">
                <div id="prerequisite-results" class="hidden mt-2"></div>
            </div>
            <div id="prerequisites-list">
                <p class="text-center text-slate-400 py-6">Loading...</p>
            </div>
        </div>
        <div class="p-6 border-t border-slate-200 flex justify-end">
            <button type="button" onclick="document.getElementById('prerequisites-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Close</button>
        </div>
    </div>
</dialog>
