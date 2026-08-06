<?php
$title = 'Dashboard';
ob_start();
?>
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 dark:bg-slate-950 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0">
        <!-- Brand -->
        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-slate-800">
            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-600 text-white shrink-0">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
            </span>
            <span class="text-base font-bold text-white">Admin</span>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <a href="<?= url('/admin/dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-slate-800 text-white">
                <i data-lucide="gauge" class="w-4 h-4 shrink-0"></i> Dashboard
            </a>
            <?php if ($user->puedePublicar() || $user->role === 'revisor'): ?>
            <a href="<?= url('/admin/blog') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors">
                <i data-lucide="file-pen-line" class="w-4 h-4 shrink-0"></i> Artículos
            </a>
            <?php endif; ?>
            <?php if ($user->esAdmin()): ?>
            <a href="<?= url('/admin/categorias') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors">
                <i data-lucide="folder" class="w-4 h-4 shrink-0"></i> Categorías
            </a>
            <?php endif; ?>
            <?php if ($user->esAdmin() || $user->role === 'revisor'): ?>
            <a href="<?= url('/admin/comentarios') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors">
                <i data-lucide="message-circle" class="w-4 h-4 shrink-0"></i> Comentarios
            </a>
            <?php endif; ?>
            <?php if ($user->esAdmin() || $user->role === 'revisor'): ?>
            <a href="<?= url('/admin/denuncias') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors">
                <i data-lucide="megaphone" class="w-4 h-4 shrink-0"></i> Denuncias
            </a>
            <?php endif; ?>

            <div class="pt-4 pb-2 px-3">
                <span class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600">Sistema</span>
            </div>

            <a href="<?= url('/admin/two-factor') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors">
                <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i> 2FA
            </a>
            <a href="<?= url('/admin/mi-perfil') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors">
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

    <!-- Mobile overlay -->
    <div id="admin-sidebar-overlay" onclick="toggleSidebar(false)" class="fixed inset-0 bg-black/50 z-30 lg:hidden hidden"></div>

    <!-- Main content -->
    <div class="flex-1 min-w-0 lg:ml-64">
        <!-- Top bar -->
        <header class="sticky top-0 z-20 flex items-center gap-4 px-4 sm:px-6 py-3 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Menú">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <span class="text-sm text-slate-600 dark:text-slate-300 ml-auto">Bienvenido, <strong><?= esc($user->name ?? 'Admin') ?></strong></span>
            <a href="<?= url('/') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-brand-300 dark:hover:border-brand-600 transition-colors">
                <i data-lucide="home" class="w-4 h-4"></i>
                Volver al sitio
            </a>
        </header>

        <!-- Flash messages -->
        <?php if ($msg = flash('exito')): ?>
        <div class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 p-4 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-xl" role="alert">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span class="text-sm font-medium flex-1"><?= esc($msg) ?></span>
            <button onclick="this.parentElement.remove()" class="p-1 hover:bg-green-100 dark:hover:bg-green-900 rounded-lg transition-colors" aria-label="Cerrar"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
        <div class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-xl" role="alert">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <span class="text-sm font-medium flex-1"><?= esc($msg) ?></span>
            <button onclick="this.parentElement.remove()" class="p-1 hover:bg-red-100 dark:hover:bg-red-900 rounded-lg transition-colors" aria-label="Cerrar"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <?php endif; ?>

        <!-- Page content -->
        <div class="p-4 sm:p-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">
                <?php if ($role === 'admin'): ?>
                    Resumen del Blog
                <?php elseif ($role === 'editor'): ?>
                    Mis Artículos
                <?php elseif ($role === 'revisor'): ?>
                    Panel de Revisión
                <?php else: ?>
                    Resumen General
                <?php endif; ?>
            </h1>

            <?php if ($role === 'admin'): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Total Posts</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_posts'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400"><i data-lucide="file-text" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Noticias</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_noticias'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-red-50 dark:bg-red-950 text-red-600 dark:text-red-400"><i data-lucide="newspaper" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Opinión</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_opinion'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-cyan-50 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400"><i data-lucide="message-square" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Investigaciones</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_investigaciones'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400"><i data-lucide="search" class="w-5 h-5"></i></div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Borradores</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_borradores'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400"><i data-lucide="pencil" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Publicados</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_publicados'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-green-50 dark:bg-green-950 text-green-600 dark:text-green-400"><i data-lucide="check-circle" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <a href="<?= url('/admin/comentarios') ?>" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:border-brand-300 dark:hover:border-brand-600 transition-colors block">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Comentarios Pendientes</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_comentarios_pendientes'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-400"><i data-lucide="message-circle" class="w-5 h-5"></i></div>
                    </div>
                </a>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Usuarios</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_usuarios'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400"><i data-lucide="users" class="w-5 h-5"></i></div>
                    </div>
                </div>
            </div>

            <?php elseif ($role === 'editor'): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Mis Artículos</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['mis_posts'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400"><i data-lucide="file-text" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Borradores</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['mis_borradores'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400"><i data-lucide="pencil" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Publicados</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['mis_publicados'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-green-50 dark:bg-green-950 text-green-600 dark:text-green-400"><i data-lucide="check-circle" class="w-5 h-5"></i></div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Noticias</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['mis_noticias'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-red-50 dark:bg-red-950 text-red-600 dark:text-red-400"><i data-lucide="newspaper" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Opinión</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['mis_opinion'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-cyan-50 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400"><i data-lucide="message-square" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Investigaciones</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['mis_investigaciones'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400"><i data-lucide="search" class="w-5 h-5"></i></div>
                    </div>
                </div>
            </div>

            <?php elseif ($role === 'revisor'): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Publicados</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_publicados'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-green-50 dark:bg-green-950 text-green-600 dark:text-green-400"><i data-lucide="check-circle" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Borradores</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_borradores'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400"><i data-lucide="pencil" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <a href="<?= url('/admin/comentarios') ?>" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:border-brand-300 dark:hover:border-brand-600 transition-colors block">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Comentarios Pendientes</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['comentarios_pendientes'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-400"><i data-lucide="message-circle" class="w-5 h-5"></i></div>
                    </div>
                </a>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Categorías</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_categorias'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400"><i data-lucide="folder" class="w-5 h-5"></i></div>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Publicados</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_publicados'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-green-50 dark:bg-green-950 text-green-600 dark:text-green-400"><i data-lucide="check-circle" class="w-5 h-5"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Usuarios</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= number_format($stats['total_usuarios'] ?? 0) ?></p>
                        </div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400"><i data-lucide="users" class="w-5 h-5"></i></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
            <!-- Configuración del Sitio -->
            <div class="mt-8">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Configuración del Sitio</h2>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                    <form method="POST" action="<?= url('/admin/configuracion') ?>">
                        <?= csrf_field() ?>
                        <?php
                        $encuestaConfig = $config['encuesta'] ?? null;
                        $encuestaPregunta = is_array($encuestaConfig) ? (string) ($encuestaConfig['pregunta'] ?? '') : '';
                        $encuestaOpciones = '';
                        if (is_array($encuestaConfig) && !empty($encuestaConfig['opciones'])) {
                            $encuestaOpciones = implode("\n", array_map(fn ($o) => (string) ($o['texto'] ?? ''), $encuestaConfig['opciones']));
                        }
                        $encuestaTotalVotos = is_array($encuestaConfig) ? array_sum(array_column($encuestaConfig['opciones'] ?? [], 'votos')) : 0;

                        $retoConfig = $config['reto'] ?? null;
                        $retoPregunta = is_array($retoConfig) ? (string) ($retoConfig['pregunta'] ?? '') : '';
                        $retoOpciones = '';
                        if (is_array($retoConfig) && !empty($retoConfig['opciones'])) {
                            $retoOpciones = implode("\n", array_map(fn ($o) => (string) ($o['texto'] ?? ''), $retoConfig['opciones']));
                        }
                        $retoTotalVotos = is_array($retoConfig) ? array_sum(array_column($retoConfig['opciones'] ?? [], 'votos')) : 0;
                        $retoCorrecta = is_array($retoConfig) ? (int) ($retoConfig['respuesta_correcta'] ?? 0) + 1 : 1;
                        ?>
                        <div class="space-y-4">
                            <!-- Registro de usuarios -->
                            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Registro de nuevos usuarios</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Permitir que nuevos usuarios se registren en la plataforma</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="registro_habilitado" value="1" <?= ($config['registro_habilitado'] ?? true) ? 'checked' : '' ?> class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-slate-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600"></div>
                                    <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                        <?= ($config['registro_habilitado'] ?? true) ? 'Habilitado' : 'Deshabilitado' ?>
                                    </span>
                                        </label>
                            </div>

                            <!-- Encuesta del home -->
                            <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Encuesta del home</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Pregunta con opciones para el aside de la portada. Si dejas la pregunta vacía, la encuesta se oculta. Los votos se conservan al editar las opciones.</p>
                                <div class="space-y-4">
                                    <div>
                                        <label for="encuesta_pregunta" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Pregunta</label>
                                        <input type="text" id="encuesta_pregunta" name="encuesta_pregunta" value="<?= esc($encuestaPregunta) ?>" maxlength="255"
                                               placeholder="¿Qué tema deberíamos investigar a fondo?"
                                               class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                    </div>
                                    <div>
                                        <label for="encuesta_opciones" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Opciones (una por línea)</label>
                                        <textarea id="encuesta_opciones" name="encuesta_opciones" rows="4"
                                                  class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all resize-y"
                                                  placeholder="Narcotráfico y política&#10;Lavado de dinero&#10;Contratos públicos"><?= esc($encuestaOpciones) ?></textarea>
                                    </div>
                                    <?php if ($encuestaTotalVotos > 0): ?>
                                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= (int) $encuestaTotalVotos ?> voto<?= $encuestaTotalVotos === 1 ? '' : 's' ?> recibido<?= $encuestaTotalVotos === 1 ? '' : 's' ?> hasta ahora.</p>
                                    <?php endif; ?>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="encuesta_reiniciar" value="1" class="rounded border-slate-300 dark:border-slate-600 text-brand-600 focus:ring-brand-500">
                                        <span class="text-xs text-slate-600 dark:text-slate-300">Reiniciar votos de esta encuesta</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Reto de la comunidad -->
                            <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Reto de la comunidad</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Pregunta para la sección Juegos del home. Al votar se revela la opción correcta. Si dejas la pregunta vacía, el reto se oculta. Los votos se conservan al editar las opciones.</p>
                                <div class="space-y-4">
                                    <div>
                                        <label for="reto_pregunta" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Pregunta</label>
                                        <input type="text" id="reto_pregunta" name="reto_pregunta" value="<?= esc($retoPregunta) ?>" maxlength="255"
                                               placeholder="¿Cuál de estas afirmaciones es falsa?"
                                               class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                                    </div>
                                    <div>
                                        <label for="reto_opciones" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Opciones (una por línea)</label>
                                        <textarea id="reto_opciones" name="reto_opciones" rows="4"
                                                  class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all resize-y"
                                                  placeholder="La Opción A es cierta&#10;La Opción B es cierta&#10;La Opción C es falsa"><?= esc($retoOpciones) ?></textarea>
                                    </div>
                                    <div>
                                        <label for="reto_correcta" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Opción correcta (nº de línea)</label>
                                        <input type="number" id="reto_correcta" name="reto_correcta" min="1" value="<?= (int) $retoCorrecta ?>"
                                               class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                                    </div>
                                    <?php if ($retoTotalVotos > 0): ?>
                                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= (int) $retoTotalVotos ?> voto<?= $retoTotalVotos === 1 ? '' : 's' ?> recibido<?= $retoTotalVotos === 1 ? '' : 's' ?> hasta ahora.</p>
                                    <?php endif; ?>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="reto_reiniciar" value="1" class="rounded border-slate-300 dark:border-slate-600 text-amber-600 focus:ring-amber-500">
                                        <span class="text-xs text-slate-600 dark:text-slate-300">Reiniciar votos de este reto</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-md shadow-brand-600/25 transition-all">
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('admin', $content, ['pageTitle' => $title, 'user' => $user, 'module' => 'dashboard']);
?>
