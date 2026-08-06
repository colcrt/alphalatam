<?php
declare(strict_types=1);
$pageTitle = 'Página no encontrada';
ob_start();
?>
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="text-center">
        <div class="text-8xl font-extrabold text-slate-200 dark:text-slate-800 mb-4">404</div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Página no encontrada</h1>
        <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-md mx-auto">La página que buscas no existe o fue movida a otra ubicación.</p>
        <a href="<?= url('/') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-lg shadow-brand-600/25 hover:shadow-brand-600/40 transition-all">
            <i data-lucide="home" class="w-5 h-5"></i> Volver al inicio
        </a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/public.php';
