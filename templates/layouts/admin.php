<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

/**
 * Admin layout template — Tailwind CSS + Lucide Icons + Dark Mode
 *
 * Expected variables:
 *  string $content        Page body HTML
 *  string $pageTitle      <title> text
 *  object $user           Authenticated user
 *  string $module         Current admin module
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($pageTitle ?? 'Admin') ?></title>
    <meta name="csrf-token" content="<?= csrf_token() ?>">

    <!-- Tailwind CSS (pre-compiled) -->
    <link rel="stylesheet" href="<?= asset('assets/css/tailwind-admin.css') ?>?v=20260724">

    <!-- Google Fonts (non-blocking) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"></noscript>

    <!-- Quill.js (local, non-blocking CSS) -->
    <link rel="preload" href="<?= asset('assets/vendor/css/quill.snow.min.css') ?>?v=20260807" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?= asset('assets/vendor/css/quill.snow.min.css') ?>?v=20260807" rel="stylesheet"></noscript>
    <script defer src="<?= asset('assets/vendor/js/quill.min.js') ?>?v=20260807"></script>

    <!-- Custom CSS -->
    <link href="<?= asset('assets/css/admin.css') ?>?v=20260807" rel="stylesheet">

    <!-- Dark mode init (prevent flash) -->
    <script>
    (function() {
        var t = localStorage.getItem('theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
    </script>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased">

<!-- Dark mode toggle (fixed bottom-right) -->
<button id="admin-dark-toggle" type="button"
        class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-11 h-11 rounded-full bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 shadow-lg hover:scale-110 transition-all"
        aria-label="Cambiar tema">
    <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
    <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
</button>

<?= $content ?>

<!-- Vue 3 (local, con fallback a CDN) -->
<script defer src="<?= asset('assets/vendor/js/vue.global.prod.js') ?>?v=20260807" onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js';"></script>

<!-- App JS -->
<script defer src="<?= asset('assets/js/admin/app.js') ?>?v=20260807"></script>

<!-- Lucide re-init + Sidebar toggle + Dark mode toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();

    document.getElementById('admin-dark-toggle')?.addEventListener('click', function() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    });
});

function toggleSidebar(open) {
    var sidebar = document.getElementById('admin-sidebar');
    var overlay = document.getElementById('admin-sidebar-overlay');
    if (!sidebar) return;
    if (open === undefined) open = sidebar.classList.contains('-translate-x-full');
    if (open) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        if (overlay) overlay.classList.remove('hidden');
    } else {
        sidebar.classList.remove('translate-x-0');
        sidebar.classList.add('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
    }
}

function showTab(tab) {
    document.querySelectorAll('[data-tab-content]').forEach(function(el) {
        el.style.display = el.dataset.tabContent === tab ? '' : 'none';
    });
    document.querySelectorAll('[data-tab-btn]').forEach(function(btn) {
        if (btn.dataset.tabBtn === tab) {
            btn.className = btn.className.replace(/border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300/g, 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400 font-semibold');
        } else {
            btn.className = btn.className.replace(/border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400 font-semibold/g, 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300');
        }
    });
}
</script>
<!-- Lucide Icons (local, deferred) -->
<script defer src="<?= asset('assets/vendor/js/lucide.min.js') ?>?v=20260807" onload="if(typeof lucide!=='undefined')lucide.createIcons()"></script>
</body>
</html>
