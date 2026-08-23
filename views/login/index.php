<?php
require_once __DIR__ . '/../../bootstrap.php';

$title = 'UCA Nexus - Enrollment Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe',
                            300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6',
                            600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a'
                        },
                        sidebar: { DEFAULT: '#0f172a', hover: '#1e293b', active: '#1e40af' }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-primary-900 via-primary-800 to-sidebar min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center p-6">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-white tracking-tight">
                <span class="text-primary-300">UCA</span>
                <span class="text-white/90">Nexus</span>
            </h1>
            <p class="text-primary-200/60 mt-3 text-lg">Enrollment Management System</p>
            <p class="text-primary-200/40 mt-1 text-sm">Select your portal to continue</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 w-full max-w-5xl">
            <!-- Admin -->
            <a href="<?= url('views/login/staff.php?type=admin') ?>" class="group block bg-white/95 backdrop-blur rounded-2xl shadow-xl p-8 text-center hover:bg-white hover:shadow-2xl hover:-translate-y-1 transition-all duration-200">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-800 mb-1">Admin</h2>
                <p class="text-sm text-slate-500">System management and configuration</p>
                <span class="inline-block mt-4 text-sm font-medium text-primary-600 group-hover:text-primary-700">Enter Portal &rarr;</span>
            </a>

            <!-- Registrar -->
            <a href="<?= url('views/login/staff.php?type=registrar') ?>" class="group block bg-white/95 backdrop-blur rounded-2xl shadow-xl p-8 text-center hover:bg-white hover:shadow-2xl hover:-translate-y-1 transition-all duration-200">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-800 mb-1">Registrar</h2>
                <p class="text-sm text-slate-500">Student records and enrollment management</p>
                <span class="inline-block mt-4 text-sm font-medium text-primary-600 group-hover:text-primary-700">Enter Portal &rarr;</span>
            </a>

            <!-- Accounting -->
            <a href="<?= url('views/login/staff.php?type=accounting') ?>" class="group block bg-white/95 backdrop-blur rounded-2xl shadow-xl p-8 text-center hover:bg-white hover:shadow-2xl hover:-translate-y-1 transition-all duration-200">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-800 mb-1">Accounting</h2>
                <p class="text-sm text-slate-500">Fees, payments, and financial transactions</p>
                <span class="inline-block mt-4 text-sm font-medium text-primary-600 group-hover:text-primary-700">Enter Portal &rarr;</span>
            </a>

            <!-- Admission -->
            <a href="<?= url('views/login/staff.php?type=admission') ?>" class="group block bg-white/95 backdrop-blur rounded-2xl shadow-xl p-8 text-center hover:bg-white hover:shadow-2xl hover:-translate-y-1 transition-all duration-200">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-800 mb-1">Admission</h2>
                <p class="text-sm text-slate-500">Applicant processing and evaluation</p>
                <span class="inline-block mt-4 text-sm font-medium text-primary-600 group-hover:text-primary-700">Enter Portal &rarr;</span>
            </a>

            <!-- Department -->
            <a href="<?= url('views/login/staff.php?type=department') ?>" class="group block bg-white/95 backdrop-blur rounded-2xl shadow-xl p-8 text-center hover:bg-white hover:shadow-2xl hover:-translate-y-1 transition-all duration-200">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-800 mb-1">Department</h2>
                <p class="text-sm text-slate-500">Programs, curricula, and academic terms</p>
                <span class="inline-block mt-4 text-sm font-medium text-primary-600 group-hover:text-primary-700">Enter Portal &rarr;</span>
            </a>

            <!-- Teacher -->
            <a href="<?= url('views/login/teacher.php') ?>" class="group block bg-white/95 backdrop-blur rounded-2xl shadow-xl p-8 text-center hover:bg-white hover:shadow-2xl hover:-translate-y-1 transition-all duration-200">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-800 mb-1">Teacher</h2>
                <p class="text-sm text-slate-500">Subject loadings, class lists, and grading</p>
                <span class="inline-block mt-4 text-sm font-medium text-primary-600 group-hover:text-primary-700">Enter Portal &rarr;</span>
            </a>

            <!-- Student -->
            <a href="<?= url('views/login/student.php') ?>" class="group block bg-white/95 backdrop-blur rounded-2xl shadow-xl p-8 text-center hover:bg-white hover:shadow-2xl hover:-translate-y-1 transition-all duration-200">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-800 mb-1">Student</h2>
                <p class="text-sm text-slate-500">Subjects, grades, ledger, and examination permit</p>
                <span class="inline-block mt-4 text-sm font-medium text-primary-600 group-hover:text-primary-700">Enter Portal &rarr;</span>
            </a>

            <!-- Application -->
            <a href="<?= url('views/application/index.php') ?>" class="group block bg-white/95 backdrop-blur rounded-2xl shadow-xl p-8 text-center hover:bg-white hover:shadow-2xl hover:-translate-y-1 transition-all duration-200">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white transition-colors duration-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-slate-800 mb-1">Application</h2>
                <p class="text-sm text-slate-500">Submit and manage applications</p>
                <span class="inline-block mt-4 text-sm font-medium text-primary-600 group-hover:text-primary-700">Apply Now &rarr;</span>
            </a>
        </div>

        <p class="text-center text-primary-200/40 text-xs mt-12">
            &copy; <?= date('Y') ?> UCA Nexus. All rights reserved.
        </p>
    </div>
</body>
</html>
