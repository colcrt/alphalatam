<?php
$title = 'Iniciar Sesión';
ob_start();
?>
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl p-8">
            <!-- Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-600 text-white mb-4">
                    <i data-lucide="shield-alert" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Iniciar Sesión</h1>
            </div>

            <?php if ($msg = flash('error')): ?>
            <div class="flex items-center gap-3 p-3 mb-5 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl text-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <?= esc($msg) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('/login') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Correo electrónico</label>
                    <input type="email" id="email" name="email" required autofocus
                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Contraseña</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                </div>
                <?php if (!empty($_showRecaptcha)): ?>
                <div class="flex justify-center py-2">
                    <div class="g-recaptcha" data-sitekey="<?= esc($_ENV['RECAPTCHA_SITE_KEY'] ?? '') ?>" data-theme="light"></div>
                </div>
                <?php endif; ?>
                <button type="submit" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-lg shadow-brand-600/25 hover:shadow-brand-600/40 transition-all">Ingresar</button>
            </form>

            <div class="mt-6 text-center text-sm space-y-1">
                <a href="<?= url('/register') ?>" class="block text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium">Crear cuenta</a>
                <a href="<?= url('/forgot-password') ?>" class="block text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">¿Olvidó su contraseña?</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('public', $content, ['pageTitle' => $title]);

if (!empty($_showRecaptcha)):
?><script src="https://www.google.com/recaptcha/api.js" async defer></script>
<style>.g-recaptcha{transform:scale(1);transform-origin:0 0}</style>
<?php endif; ?>
