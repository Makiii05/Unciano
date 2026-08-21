<?php
$flash = get_flash();
if ($flash): ?>
    <div class="mb-4 p-3 rounded-lg text-sm <?= $flash['type'] === 'error' ? 'bg-red-50 border-red-200' : 'bg-green-50 border border-green-200' ?>">
        <?= e($flash['message']) ?>
    </div>
<?php endif; ?>
