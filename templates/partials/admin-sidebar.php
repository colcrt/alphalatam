<?php
declare(strict_types=1);

/**
 * Sidebar compartido del panel de administración.
 *
 * Variables esperadas:
 *  object $user    Usuario autenticado
 *  string $module  Módulo activo (dashboard, blog, blog-borrados, categoria,
 *                  comentario, denuncia, perfil, two-factor)
 *  array  $stats   Estadísticas del dashboard (opcional, solo para el badge)
 */
?>
<aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 dark:bg-slate-950 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0">
    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-slate-800">
        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-600 text-white shrink-0">
            <i data-lucide="shield-alert" class="w-4 h-4"></i>
        </span>
        <span class="text-base font-bold text-white">Admin</span>
    </div>
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <a href="<?= url('/admin/dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors <?= ($module ?? '') === 'dashboard' ? 'bg-slate-800 text-white' : '' ?>">
            <i data-lucide="gauge" class="w-4 h-4 shrink-0"></i> Dashboard
        </a>
        <?php if ($user->puedePublicar() || $user->role === 'revisor'): ?>
        <a href="<?= url('/admin/blog') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors <?= ($module ?? '') === 'blog' ? 'bg-slate-800 text-white' : '' ?>">
            <i data-lucide="file-pen-line" class="w-4 h-4 shrink-0"></i> Artículos
        </a>
        <?php endif; ?>
        <?php if ($user->esAdmin()): ?>
        <a href="<?= url('/admin/blog/borrados') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors <?= ($module ?? '') === 'blog-borrados' ? 'bg-slate-800 text-white' : '' ?>">
            <i data-lucide="trash-2" class="w-4 h-4 shrink-0"></i> Borrados
            <?php if (($stats['solicitudes_borrado'] ?? 0) > 0): ?>
            <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white"><?= (int) $stats['solicitudes_borrado'] ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>
        <?php if ($user->esAdmin()): ?>
        <a href="<?= url('/admin/categorias') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors <?= ($module ?? '') === 'categoria' ? 'bg-slate-800 text-white' : '' ?>">
            <i data-lucide="folder" class="w-4 h-4 shrink-0"></i> Categorías
        </a>
        <?php endif; ?>
        <?php if ($user->esAdmin() || $user->role === 'revisor'): ?>
        <a href="<?= url('/admin/comentarios') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors <?= ($module ?? '') === 'comentario' ? 'bg-slate-800 text-white' : '' ?>">
            <i data-lucide="message-circle" class="w-4 h-4 shrink-0"></i> Comentarios
        </a>
        <?php endif; ?>
        <?php if ($user->esAdmin() || $user->role === 'revisor'): ?>
        <a href="<?= url('/admin/denuncias') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors <?= ($module ?? '') === 'denuncia' ? 'bg-slate-800 text-white' : '' ?>">
            <i data-lucide="megaphone" class="w-4 h-4 shrink-0"></i> Denuncias
        </a>
        <?php endif; ?>
        <div class="pt-4 pb-2 px-3"><span class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600">Sistema</span></div>
        <a href="<?= url('/admin/two-factor') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors <?= ($module ?? '') === 'two-factor' ? 'bg-slate-800 text-white' : '' ?>">
            <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i> 2FA
        </a>
        <a href="<?= url('/admin/mi-perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors <?= ($module ?? '') === 'perfil' ? 'bg-slate-800 text-white' : '' ?>">
            <i data-lucide="user" class="w-4 h-4 shrink-0"></i> Mi Perfil
        </a>
        <form method="POST" action="<?= url('/logout') ?>" class="mt-1">
            <?= csrf_field() ?>
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors text-left">
                <i data-lucide="log-out" class="w-4 h-4 shrink-0"></i> Salir
            </button>
        </form>
    </nav>
</aside>

<div id="admin-sidebar-overlay" onclick="toggleSidebar(false)" class="fixed inset-0 bg-black/50 z-30 lg:hidden hidden"></div>
