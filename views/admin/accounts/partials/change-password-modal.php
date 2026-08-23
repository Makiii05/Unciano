<dialog id="change-password-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <form method="POST" id="change-password-form">
            <?= csrf_field() ?>
            <input type="hidden" name="account_id" id="change-password-account-id">
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Change Password</h3>
                <button type="button" onclick="document.getElementById('change-password-modal').close()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                    <input type="password" name="new_password" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required minlength="8">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none" required minlength="8">
                </div>
            </div>

            <div class="flex justify-end gap-2 p-6 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('change-password-modal').close()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">Change Password</button>
            </div>
        </form>
    </div>
</dialog>

