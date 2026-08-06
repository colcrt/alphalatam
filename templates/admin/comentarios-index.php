<?php
$title = 'Comentarios';
$module = 'comentarios';
ob_start();
?>
<div class="flex min-h-screen">
    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 dark:bg-slate-950 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0">
        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-slate-800"><span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-600 text-white shrink-0"><i data-lucide="shield-alert" class="w-4 h-4"></i></span><span class="text-base font-bold text-white">Admin</span></div>
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <a href="<?= url('/admin/dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors"><i data-lucide="gauge" class="w-4 h-4 shrink-0"></i> Dashboard</a>
            <?php if ($user->puedePublicar()): ?>
            <a href="<?= url('/admin/blog') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors"><i data-lucide="file-pen-line" class="w-4 h-4 shrink-0"></i> Artículos</a>
            <?php endif; ?>
            <?php if ($user->esAdmin()): ?>
            <a href="<?= url('/admin/categorias') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors"><i data-lucide="folder" class="w-4 h-4 shrink-0"></i> Categorías</a>
            <?php endif; ?>
            <?php if ($user->esAdmin() || $user->role === 'revisor'): ?>
            <a href="<?= url('/admin/comentarios') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-slate-800 text-white"><i data-lucide="message-circle" class="w-4 h-4 shrink-0"></i> Comentarios</a>
            <?php endif; ?>
            <?php if ($user->esAdmin() || $user->role === 'revisor'): ?>
            <a href="<?= url('/admin/denuncias') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors"><i data-lucide="megaphone" class="w-4 h-4 shrink-0"></i> Denuncias</a>
            <?php endif; ?>
            <div class="pt-4 pb-2 px-3"><span class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600">Sistema</span></div>
            <a href="<?= url('/admin/two-factor') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors"><i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i> 2FA</a>
            <a href="<?= url('/admin/mi-perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors"><i data-lucide="user" class="w-4 h-4 shrink-0"></i> Mi Perfil</a>
            <form method="POST" action="<?= url('/logout') ?>" class="mt-1"><?= csrf_field() ?><button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors text-left"><i data-lucide="log-out" class="w-4 h-4 shrink-0"></i> Salir</button></form>
        </nav>
    </aside>
    <div id="admin-sidebar-overlay" onclick="toggleSidebar(false)" class="fixed inset-0 bg-black/50 z-30 lg:hidden hidden"></div>
    <div class="flex-1 min-w-0 lg:ml-64">
        <header class="sticky top-0 z-20 flex items-center gap-4 px-4 sm:px-6 py-3 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Menú"><i data-lucide="menu" class="w-5 h-5"></i></button>
            <span class="text-sm text-slate-600 dark:text-slate-300 ml-auto">Bienvenido, <strong><?= esc($user->name ?? 'Admin') ?></strong></span>
            <a href="<?= url('/') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-brand-300 dark:hover:border-brand-600 transition-colors">
                <i data-lucide="home" class="w-4 h-4"></i>
                Volver al sitio
            </a>
        </header>
        <?php if ($msg = flash('exito')): ?><div class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 p-4 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-xl" role="alert"><i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i><span class="text-sm font-medium flex-1"><?= esc($msg) ?></span><button onclick="this.parentElement.remove()" class="p-1 hover:bg-green-100 dark:hover:bg-green-900 rounded-lg transition-colors" aria-label="Cerrar"><i data-lucide="x" class="w-4 h-4"></i></button></div><?php endif; ?>
        <?php if ($msg = flash('error')): ?><div class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-xl" role="alert"><i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i><span class="text-sm font-medium flex-1"><?= esc($msg) ?></span><button onclick="this.parentElement.remove()" class="p-1 hover:bg-red-100 dark:hover:bg-red-900 rounded-lg transition-colors" aria-label="Cerrar"><i data-lucide="x" class="w-4 h-4"></i></button></div><?php endif; ?>

        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Comentarios</h1>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= url('/admin/comentarios') ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all <?= ($statusFiltro ?? '') === '' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-brand-300' ?>">Todos</a>
                    <a href="<?= url('/admin/comentarios?status=pendiente') ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all <?= ($statusFiltro ?? '') === 'pendiente' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-amber-300' ?>">Pendientes</a>
                    <a href="<?= url('/admin/comentarios?status=aprobado') ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all <?= ($statusFiltro ?? '') === 'aprobado' ? 'bg-green-500 text-white shadow-md shadow-green-500/25' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-green-300' ?>">Aprobados</a>
                    <a href="<?= url('/admin/comentarios?status=rechazado') ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all <?= ($statusFiltro ?? '') === 'rechazado' ? 'bg-red-500 text-white shadow-md shadow-red-500/25' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-red-300' ?>">Rechazados</a>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Autor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Contenido</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Post</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Acciones</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php if (empty($comentarios)): ?><tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No hay comentarios.</td></tr><?php endif; ?>
                            <?php foreach ($comentarios as $com):
                                $statusBadge = match($com['status'] ?? '') { 'aprobado' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300', 'rechazado' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300', default => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' };
                            ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-sm text-slate-900 dark:text-white"><?= esc($com['autor_nombre'] ?? '') ?></div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500"><?= esc($com['autor_email'] ?? '') ?></div>
                                </td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400 max-w-xs truncate text-xs"><?= esc(str_limit($com['contenido'] ?? '', 100)) ?></td>
                                <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300"><?= esc($com['post_titulo'] ?? 'Post #' . ($com['blog_post_id'] ?? '')) ?></td>
                                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusBadge ?>"><?= ucfirst($com['status'] ?? 'pendiente') ?></span></td>
                                <td class="px-4 py-3 text-xs text-slate-400 dark:text-slate-500"><?= date_format_es($com['created_at'] ?? '') ?></td>
                                <td class="px-4 py-3 text-right space-x-1">
                                    <?php if (($com['status'] ?? '') !== 'aprobado'): ?>
                                    <form method="POST" action="<?= url('/admin/comentarios/' . $com['id'] . '/aprobar') ?>" class="inline"><?= csrf_field() ?><button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-950 transition-colors" title="Aprobar"><i data-lucide="check" class="w-4 h-4"></i></button></form>
                                    <?php endif; ?>
                                    <?php if (($com['status'] ?? '') !== 'rechazado'): ?>
                                    <form method="POST" action="<?= url('/admin/comentarios/' . $com['id'] . '/rechazar') ?>" class="inline"><?= csrf_field() ?><button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950 transition-colors" title="Rechazar"><i data-lucide="x" class="w-4 h-4"></i></button></form>
                                    <?php endif; ?>
                                    <form method="POST" action="<?= url('/admin/comentarios/' . $com['id']) ?>" class="inline" onsubmit="return confirm('¿Eliminar este comentario?')"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 transition-colors" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('admin', $content, ['pageTitle' => $title, 'module' => $module]);
