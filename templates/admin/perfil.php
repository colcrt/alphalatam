<?php
$title = 'Mi Perfil';
$module = 'perfil';
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

        <div class="p-4 sm:p-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Mi Perfil</h1>

            <!-- Tabs -->
            <div class="flex gap-0 border-b border-slate-200 dark:border-slate-800 mb-5">
                <button type="button" onclick="showTab('datos')" data-tab-btn="datos"
                        class="px-5 py-3 text-sm font-medium border-b-2 transition-colors -mb-px border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400 font-semibold">Datos Personales</button>
                <button type="button" onclick="showTab('password')" data-tab-btn="password"
                        class="px-5 py-3 text-sm font-medium border-b-2 transition-colors -mb-px border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300">Cambiar Contraseña</button>
            </div>

            <!-- Datos Personales Tab -->
            <div data-tab-content="datos">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Avatar -->
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Foto de Perfil</h2>
                        <div class="flex flex-col items-center">
                            <div class="w-24 h-24 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden mb-4">
                                <?php if (!empty($user->avatar_path)): ?>
                                <img src="<?= asset('uploads/' . $user->avatar_path) ?>" alt="Avatar" class="w-full h-full object-cover">
                                <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                    <i data-lucide="user" class="w-10 h-10"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <form method="POST" action="<?= url('/admin/mi-perfil/avatar') ?>" enctype="multipart/form-data" class="w-full">
                                <?= csrf_field() ?>
                                <input type="file" name="avatar" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950 dark:file:text-brand-400 mb-3">
                                <button type="submit" class="w-full px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg transition-colors">Actualizar Avatar</button>
                            </form>
                        </div>
                    </div>

                    <!-- Datos -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Información Personal</h2>
                        <form method="POST" action="<?= url('/admin/mi-perfil') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="POST">
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nombre</label>
                                    <input type="text" id="name" name="name" value="<?= esc($user->name) ?>" required
                                           class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Correo Electrónico</label>
                                    <input type="email" id="email" name="email" value="<?= esc($user->email) ?>" required
                                           class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Rol</label>
                                    <div class="px-3 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-500 dark:text-slate-400">
                                        <?= ucfirst($user->role) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-md shadow-brand-600/25 transition-all">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cambiar Contraseña Tab -->
            <div data-tab-content="password" style="display:none">
                <div class="max-w-lg bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Cambiar Contraseña</h2>
                    <form method="POST" action="<?= url('/admin/mi-perfil/password') ?>">
                        <?= csrf_field() ?>
                        <div class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Contraseña Actual</label>
                                <input type="password" id="current_password" name="current_password" required
                                       class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                            </div>
                            <div>
                                <label for="new_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nueva Contraseña</label>
                                <input type="password" id="new_password" name="new_password" required minlength="8"
                                       class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mínimo 8 caracteres</p>
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Confirmar Nueva Contraseña</label>
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                                       class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-md shadow-brand-600/25 transition-all">Actualizar Contraseña</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('admin', $content, ['pageTitle' => $title, 'user' => $user, 'module' => 'perfil']);
?>
