<?php
$title = 'Detalle de Denuncia';
$module = 'denuncia';
ob_start();
$estadoBadge = match($denuncia->status ?? '') { 'convertida' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300', 'rechazada' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300', 'en_revision' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300', default => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' };
$estadoLabel = match($denuncia->status ?? '') { 'convertida' => 'Convertida', 'rechazada' => 'Rechazada', 'en_revision' => 'En revisión', default => 'Pendiente' };
$evidencias = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $denuncia->evidencias)), fn ($l) => $l !== ''));
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
            <a href="<?= url('/admin/denuncias') ?>" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition-colors mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver a denuncias
            </a>

            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 mb-5">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $estadoBadge ?>"><?= $estadoLabel ?></span>
                        <span class="text-xs text-slate-400 dark:text-slate-500">Recibida: <?= date_format_es($denuncia->created_at ?? '') ?></span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white"><?= esc($denuncia->titulo ?? '') ?></h1>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    <?php if ($user->puedePublicar() && ($denuncia->status ?? '') !== 'convertida'): ?>
                    <form method="POST" action="<?= url('/admin/denuncias/' . $denuncia->id . '/convertir') ?>"><?= csrf_field() ?>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/25 transition-all"><i data-lucide="file-pen-line" class="w-4 h-4"></i> Convertir en artículo</button>
                    </form>
                    <?php endif; ?>
                    <?php if (($denuncia->status ?? '') !== 'en_revision'): ?>
                    <form method="POST" action="<?= url('/admin/denuncias/' . $denuncia->id . '/estado') ?>"><?= csrf_field() ?><input type="hidden" name="estado" value="en_revision">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950 transition-colors"><i data-lucide="clipboard-list" class="w-4 h-4"></i> En revisión</button>
                    </form>
                    <?php endif; ?>
                    <?php if (($denuncia->status ?? '') !== 'rechazada'): ?>
                    <form method="POST" action="<?= url('/admin/denuncias/' . $denuncia->id . '/estado') ?>"><?= csrf_field() ?><input type="hidden" name="estado" value="rechazada">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 transition-colors"><i data-lucide="x" class="w-4 h-4"></i> Rechazar</button>
                    </form>
                    <?php else: ?>
                    <form method="POST" action="<?= url('/admin/denuncias/' . $denuncia->id . '/estado') ?>"><?= csrf_field() ?><input type="hidden" name="estado" value="pendiente">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950 transition-colors"><i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reabrir</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($user->esAdmin()): ?>
                    <form method="POST" action="<?= url('/admin/denuncias/' . $denuncia->id) ?>" onsubmit="return confirm('¿Eliminar esta denuncia? Esta acción no se puede deshacer.')"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($post): ?>
            <div class="p-4 mb-5 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-xl">
                <div class="flex items-center gap-2 text-sm font-semibold mb-1"><i data-lucide="check-circle" class="w-5 h-5"></i> Convertida en artículo</div>
                <p class="text-sm mb-2">Borrador creado: <?= esc($post->titulo ?? '') ?> (<?= esc($post->status ?? '') ?>). Edítalo y publícalo desde el editor de artículos.</p>
                <a href="<?= url('/admin/blog/' . $post->id . '/editar') ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold hover:underline"><i data-lucide="external-link" class="w-4 h-4"></i> Abrir en el editor</a>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="lg:col-span-2 space-y-5">
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Descripción de los hechos</h2>
                        <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line"><?= esc($denuncia->hechos ?? '') ?></div>
                    </div>

                    <?php if (!empty($evidencias)): ?>
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Evidencias</h2>
                        <ul class="space-y-2">
                            <?php foreach ($evidencias as $url): ?>
                            <li class="flex items-start gap-2 text-sm">
                                <i data-lucide="link" class="w-4 h-4 shrink-0 mt-0.5 text-slate-400"></i>
                                <a href="<?= esc($url) ?>" target="_blank" rel="noopener nofollow" class="text-brand-600 dark:text-brand-400 hover:underline break-all"><?= esc($url) ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-5">
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Información</h2>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-xs text-slate-400 dark:text-slate-500">Correo de contacto</dt>
                                <dd class="text-slate-700 dark:text-slate-200"><?= esc($denuncia->email_contacto ?? 'No proporcionado') ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400 dark:text-slate-500">Fecha de envío</dt>
                                <dd class="text-slate-700 dark:text-slate-200"><?= date_format_es($denuncia->created_at ?? '') ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400 dark:text-slate-500">Revisada</dt>
                                <dd class="text-slate-700 dark:text-slate-200"><?= $denuncia->revisado_at ? date_format_es($denuncia->revisado_at) : 'Aún no' ?></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-400 dark:text-slate-500">Aceptó términos</dt>
                                <dd class="text-slate-700 dark:text-slate-200"><?= (int) ($denuncia->acepta_terminos ?? 0) === 1 ? 'Sí' : 'No' ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('admin', $content, ['pageTitle' => $title, 'user' => $user, 'module' => $module]);
