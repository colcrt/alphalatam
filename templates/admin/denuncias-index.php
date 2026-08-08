<?php
$title = 'Denuncias';
$module = 'denuncia';
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
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Denuncias ciudadanas</h1>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= url('/admin/denuncias') ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all <?= ($statusFiltro ?? '') === '' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-brand-300' ?>">Todas</a>
                    <a href="<?= url('/admin/denuncias?status=pendiente') ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all <?= ($statusFiltro ?? '') === 'pendiente' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-amber-300' ?>">Pendientes</a>
                    <a href="<?= url('/admin/denuncias?status=en_revision') ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all <?= ($statusFiltro ?? '') === 'en_revision' ? 'bg-blue-500 text-white shadow-md shadow-blue-500/25' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-blue-300' ?>">En revisión</a>
                    <a href="<?= url('/admin/denuncias?status=convertida') ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all <?= ($statusFiltro ?? '') === 'convertida' ? 'bg-green-500 text-white shadow-md shadow-green-500/25' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-green-300' ?>">Convertidas</a>
                    <a href="<?= url('/admin/denuncias?status=rechazada') ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all <?= ($statusFiltro ?? '') === 'rechazada' ? 'bg-red-500 text-white shadow-md shadow-red-500/25' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-red-300' ?>">Rechazadas</a>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Denuncia</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Contacto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Acciones</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php if (empty($denuncias)): ?><tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No hay denuncias.</td></tr><?php endif; ?>
                            <?php foreach ($denuncias as $den):
                                $estadoBadge = match($den->status ?? '') { 'convertida' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300', 'rechazada' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300', 'en_revision' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300', default => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' };
                                $estadoLabel = match($den->status ?? '') { 'convertida' => 'Convertida', 'rechazada' => 'Rechazada', 'en_revision' => 'En revisión', default => 'Pendiente' };
                            ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-sm text-slate-900 dark:text-white max-w-xs truncate"><?= esc($den->titulo ?? '') ?></div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500 max-w-xs truncate mt-0.5"><?= esc(str_limit($den->hechos ?? '', 90)) ?></div>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400"><?= esc($den->email_contacto ?? '—') ?></td>
                                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $estadoBadge ?>"><?= $estadoLabel ?></span></td>
                                <td class="px-4 py-3 text-xs text-slate-400 dark:text-slate-500"><?= date_format_es($den->created_at ?? '') ?></td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <a href="<?= url('/admin/denuncias/' . $den->id) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" title="Ver detalle"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                    <?php if ($user->puedePublicar() && ($den->status ?? '') !== 'convertida'): ?>
                                    <form method="POST" action="<?= url('/admin/denuncias/' . $den->id . '/convertir') ?>" class="inline"><?= csrf_field() ?><button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-950 transition-colors" title="Convertir en artículo"><i data-lucide="file-pen-line" class="w-4 h-4"></i></button></form>
                                    <?php endif; ?>
                                    <?php if (($den->status ?? '') !== 'rechazada'): ?>
                                    <form method="POST" action="<?= url('/admin/denuncias/' . $den->id . '/estado') ?>" class="inline"><?= csrf_field() ?><input type="hidden" name="estado" value="rechazada"><button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 transition-colors" title="Rechazar"><i data-lucide="x" class="w-4 h-4"></i></button></form>
                                    <?php else: ?>
                                    <form method="POST" action="<?= url('/admin/denuncias/' . $den->id . '/estado') ?>" class="inline"><?= csrf_field() ?><input type="hidden" name="estado" value="pendiente"><button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950 transition-colors" title="Reabrir"><i data-lucide="rotate-ccw" class="w-4 h-4"></i></button></form>
                                    <?php endif; ?>
                                    <?php if ($user->esAdmin()): ?>
                                    <form method="POST" action="<?= url('/admin/denuncias/' . $den->id) ?>" class="inline" onsubmit="return confirm('¿Eliminar esta denuncia? Esta acción no se puede deshacer.')"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 transition-colors" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
                                    <?php endif; ?>
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
                    Página <?= (int) $paginacion['current_page'] ?> de <?= (int) $paginacion['last_page'] ?> · <?= (int) $paginacion['total'] ?> denuncia<?= $paginacion['total'] === 1 ? '' : 's' ?>
                </span>
                <div class="flex items-center gap-1">
                    <?php $filtroQ = $statusFiltro !== '' ? '&status=' . $statusFiltro : ''; ?>
                    <?php if ($paginacion['current_page'] > 1): ?>
                    <a href="<?= url('/admin/denuncias?page=' . ($paginacion['current_page'] - 1) . $filtroQ) ?>" class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">&laquo;</a>
                    <?php endif; ?>
                    <?php if ($paginacion['current_page'] < $paginacion['last_page']): ?>
                    <a href="<?= url('/admin/denuncias?page=' . ($paginacion['current_page'] + 1) . $filtroQ) ?>" class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">&raquo;</a>
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
