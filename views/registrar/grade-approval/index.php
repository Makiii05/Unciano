<?php
require_once __DIR__ . '/../../../bootstrap.php';

$pageTitle = 'Grade Approval';

ob_start();
?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-primary-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= e($pageTitle) ?></h3>
                <p class="text-slate-500 text-sm">This module is coming soon.</p>
            </div>
        </div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/portal.php';
