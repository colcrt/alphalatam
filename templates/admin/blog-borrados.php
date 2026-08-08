<?php
$title = 'Solicitudes de borrado';
$module = 'blog-borrados';
ob_start();
?>
<div class="flex min-h-screen">
    <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
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
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Solicitudes de borrado</h1>
                <a href="<?= url('/admin/blog') ?>" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"><i data-lucide="arrow-left" class="w-4 h-4"></i> Volver a Artículos</a>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Artículo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Autor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Solicitado por</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha solicitud</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Acciones</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php if (empty($solicitudes)): ?><tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No hay solicitudes de borrado pendientes.</td></tr><?php endif; ?>
                            <?php foreach ($solicitudes as $post): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3">
                                    <a href="<?= url('/admin/blog/' . $post->id . '/editar') ?>" class="font-semibold text-sm text-slate-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 max-w-xs truncate block"><?= esc($post->titulo ?? '') ?></a>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-wide"><?= esc($post->tipo ?? '') ?></span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400"><?= esc($post->autor->name ?? '—') ?></td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400"><?= esc($post->solicitante->name ?? '—') ?></td>
                                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300"><?= esc($post->status ?? '') ?></span></td>
                                <td class="px-4 py-3 text-xs text-slate-400 dark:text-slate-500"><?= date_format_es($post->delete_requested_at ?? '', 'd/m/Y H:i') ?></td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <a href="<?= url('/admin/blog/' . $post->id . '/editar') ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" title="Ver artículo"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                    <form method="POST" action="<?= url('/admin/blog/' . $post->id . '/aprobar-borrado') ?>" class="inline" onsubmit="return confirm('¿Aprobar el borrado de este artículo? Se eliminará de forma definitiva (recuperable por base de datos).')"><?= csrf_field() ?><button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-950 transition-colors" title="Aprobar borrado"><i data-lucide="check" class="w-4 h-4"></i></button></form>
                                    <form method="POST" action="<?= url('/admin/blog/' . $post->id . '/rechazar-borrado') ?>" class="inline" onsubmit="return confirm('¿Rechazar la solicitud? El artículo se mantiene publicado.')"><?= csrf_field() ?><button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 transition-colors" title="Rechazar solicitud"><i data-lucide="x" class="w-4 h-4"></i></button></form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($paginacion) && $paginacion['last_page'] > 1): ?>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4">
                <span class="text-sm text-slate-500 dark:text-slate-400">
                    Página <?= (int) $paginacion['current_page'] ?> de <?= (int) $paginacion['last_page'] ?> · <?= (int) $paginacion['total'] ?> solicitud<?= $paginacion['total'] === 1 ? '' : 'es' ?>
                </span>
                <div class="flex items-center gap-1">
                    <?php if ($paginacion['current_page'] > 1): ?>
                    <a href="<?= url('/admin/blog/borrados?page=' . ($paginacion['current_page'] - 1)) ?>" class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">&laquo;</a>
                    <?php endif; ?>
                    <?php if ($paginacion['current_page'] < $paginacion['last_page']): ?>
                    <a href="<?= url('/admin/blog/borrados?page=' . ($paginacion['current_page'] + 1)) ?>" class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">&raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('admin', $content, ['pageTitle' => $title, 'user' => $user, 'module' => $module]);
