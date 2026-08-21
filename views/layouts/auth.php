<?php
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> - UCA Nexus</title>
    <link rel="stylesheet" href="<?= url('public/css/app.css') ?>">
</head>
<body class="font-sans antialiased bg-linear-to-br from-primary-900 via-primary-800 to-sidebar min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <a href="<?= e($backUrl) ?>" class="inline-flex items-center text-primary-300/70 hover:text-primary-200 text-sm mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to portals
        </a>

        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/10 flex items-center justify-center">
                <svg class="w-8 h-8 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight"><?= e($title ?? 'Login') ?></h1>
            <p class="text-primary-200/60 mt-1 text-sm">Sign in with your credentials.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-slate-800 mb-1">Welcome back</h2>
            <p class="text-sm text-slate-500 mb-6">Enter your details to continue.</p>

            <?php if ($flash): ?>
                <div class="mb-4 p-3 rounded-lg text-sm <?= $flash['type'] === 'error' ? 'bg-red-50 border-red-200' : 'bg-green-50 border border-green-200' ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= e($formAction) ?>">
                <?= csrf_field() ?>

                <?= $content ?? '' ?>

                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-150 mt-2">
                    Sign In
                </button>
            </form>

            <p class="text-center text-slate-400 text-xs mt-6">
                <a href="<?= e($backUrl) ?>" class="hover:text-primary-600 transition-colors">Select a different portal</a>
            </p>
        </div>

        <p class="text-center text-primary-200/30 text-xs mt-6">
            &copy; <?= date('Y') ?> UCA Nexus. All rights reserved.
        </p>
    </div>
</body>
</html>
