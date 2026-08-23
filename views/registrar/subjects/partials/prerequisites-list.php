<?php if (empty($prerequisites)): ?>
    <p class="text-center text-slate-400 py-6">No prerequisites assigned.</p>
<?php else: ?>
    <div class="border border-slate-200 rounded-lg divide-y divide-slate-100">
        <?php foreach ($prerequisites as $prereq): ?>
            <div class="flex items-center justify-between px-4 py-3 hover:bg-slate-50">
                <div>
                    <p class="text-sm font-medium text-slate-800"><?= e($prereq['prereq_code'] ?? $prereq['code'] ?? 'N/A') ?></p>
                    <p class="text-xs text-slate-500"><?= e($prereq['prereq_description'] ?? $prereq['description'] ?? '') ?></p>
                </div>
                <button onclick="removePrerequisite(this)" data-prereq-id="<?= e($prereq['id']) ?>" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded transition-colors">Remove</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
