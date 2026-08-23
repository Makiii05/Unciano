<dialog id="edit-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full">
        <form method="POST" id="edit-user-form">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" id="edit-user-id">
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Edit Faculty Account</h3>
                <button type="button" onclick="document.getElementById('edit-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                    <input type="text" name="name" id="edit-user-name" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" id="edit-user-email" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select name="status" id="edit-user-status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between p-6 border-t border-slate-200">
                <button type="button" id="change-password-btn" class="px-3 py-1.5 text-xs font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">Change Password</button>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('edit-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">Update</button>
                </div>
            </div>
        </form>
    </div>
</dialog>

