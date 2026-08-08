<?php
$title = 'Autenticación de Dos Factores';
$module = 'two-factor';
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
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Autenticación de Dos Factores (2FA)</h1>

            <?php if (!empty($user->two_factor_secret)): ?>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 mb-6">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-green-50 dark:bg-green-950 text-green-600 dark:text-green-400 shrink-0">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">2FA Activado</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">La autenticación de dos factores está habilitada en su cuenta.</p>
                    </div>
                    <form method="POST" action="<?= url('/admin/two-factor/disable') ?>" onsubmit="return confirm('¿Está seguro de desactivar 2FA?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="px-4 py-2 text-sm font-medium rounded-xl border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 transition-colors">Desactivar 2FA</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($qrCodeUrl)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800"><h3 class="text-base font-bold text-slate-900 dark:text-white">1. Escanee el código QR</h3></div>
                    <div class="p-6 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Use Google Authenticator, Authy u otra aplicación de autenticación.</p>
                        <img src="<?= esc($qrCodeUrl) ?>" alt="QR Code 2FA" class="mx-auto mb-4 rounded-xl" style="max-width: 200px;">
                        <p class="text-xs text-slate-400 dark:text-slate-500 mb-2">Si no puede escanear, ingrese manualmente:</p>
                        <code class="inline-block px-4 py-2 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono"><?= esc($secret) ?></code>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800"><h3 class="text-base font-bold text-slate-900 dark:text-white">2. Verifique el código</h3></div>
                    <div class="p-6">
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Ingrese el código de 6 dígitos que muestra su aplicación.</p>
                        <form method="POST" action="<?= url('/admin/two-factor/enable') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-4">
                                <label for="code" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Código de verificación</label>
                                <input type="text" id="code" name="code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required autofocus inputmode="numeric"
                                       class="w-full px-4 py-3 text-center text-lg tracking-[0.5em] font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                            </div>
                            <button type="submit" class="w-full px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/25 transition-all">Activar 2FA</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($recoveryCodes)): ?>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-amber-200 dark:border-amber-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-amber-50 dark:bg-amber-950 border-b border-amber-200 dark:border-amber-800">
                    <h3 class="text-base font-bold text-amber-800 dark:text-amber-200 flex items-center gap-2"><i data-lucide="triangle-alert" class="w-5 h-5"></i> Códigos de recuperación</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Guarde estos códigos en un lugar seguro. Cada código solo se puede usar una vez.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 mb-4">
                        <?php foreach ($recoveryCodes as $code): ?>
                        <code class="block px-3 py-2 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-center text-sm font-mono"><?= esc($code) ?></code>
                        <?php endforeach; ?>
                    </div>
                    <form method="POST" action="<?= url('/admin/two-factor/recovery-codes') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="px-4 py-2 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Regenerar códigos</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('admin', $content, ['pageTitle' => $title, 'user' => $user, 'module' => 'two-factor']);
