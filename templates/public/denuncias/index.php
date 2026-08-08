<?php
$title = 'Denuncia Ciudadana';
ob_start();
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">

    <!-- Encabezado -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-900 text-white mb-4 shadow-lg shadow-brand-950/30">
            <i data-lucide="shield-alert" class="w-7 h-7"></i>
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white leading-tight tracking-tight">
            Comparte información con nuestro equipo
        </h1>
        <p class="text-sm md:text-base text-slate-500 dark:text-slate-400 mt-3 max-w-2xl mx-auto leading-relaxed">
            Si conoces hechos que podrían ser de interés público, compártelos con nosotros. Nuestro equipo editorial analizará la información y, cuando existan elementos suficientes, podrá convertirla en una investigación periodística.
        </p>
    </div>

    <!-- Cómo funciona -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-card">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-900 text-white dark:bg-brand-800 text-sm font-bold">1</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">Comparte la información</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Cuéntanos lo ocurrido con el mayor detalle posible. Si tienes documentos, enlaces, fotografías o cualquier otro material que ayude a entender el caso, también puedes incluirlos..</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-card">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-900 text-white dark:bg-brand-800 text-sm font-bold">2</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">La revisamos cuidadosamente</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Nuestro equipo editorial analiza cada envío, verifica la información disponible y evalúa si existen elementos suficientes para iniciar una investigación periodística.</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-card">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-900 text-white dark:bg-brand-800 text-sm font-bold">3</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">Investigamos y publicamos</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Cuando la información está debidamente sustentada y supera nuestro proceso de verificación, elaboramos un artículo bajo nuestra responsabilidad editorial.</p>
        </div>
    </div>

    <!-- Nota de confidencialidad -->
    <div class="flex gap-3 p-4 mb-10 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-900 text-sky-900 dark:text-sky-200 rounded-xl">
        <i data-lucide="lock" class="w-5 h-5 shrink-0 mt-0.5"></i>
        <p class="text-sm leading-relaxed">
            <strong>Confidencialidad:</strong> Tu correo electrónico es opcional y solo se utilizará si necesitamos comunicarnos contigo para solicitar información adicional. Nunca se publica ni se comparte con terceros. Además, no almacenamos tu dirección IP. La decisión de investigar o publicar un caso corresponde exclusivamente a nuestro equipo editorial.
        </p>
    </div>

    <!-- Formulario -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-card p-6 md:p-8">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Formulario de denuncia</h2>

        <form action="<?= esc(url('/denuncias')) ?>" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div>
                <label for="titulo" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Título de la denuncia <span class="text-red-500">*</span></label>
                <input type="text" id="titulo" name="titulo" value="<?= esc(old('titulo')) ?>" maxlength="255" required
                       placeholder="Ej.: Irregularidades en la contratación de obras municipales"
                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
            </div>

            <div>
                <label for="hechos" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Descripción de los hechos <span class="text-red-500">*</span></label>
                <textarea id="hechos" name="hechos" rows="8" minlength="50" maxlength="10000" required
                          placeholder="Describe con detalle qué ocurrió, cuándo, quiénes participaron y por qué consideras que es un caso de corrupción. Incluye cifras, fechas y nombres si los conoces."
                          class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all min-h-[160px]"><?= esc(old('hechos')) ?></textarea>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Mínimo 50 caracteres.</p>
            </div>

            <div>
                <label for="evidencias" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Enlaces de evidencia <span class="text-xs font-normal text-slate-400 dark:text-slate-500">(opcional, una URL por línea)</span></label>
                <textarea id="evidencias" name="evidencias" rows="4" maxlength="4000"
                          placeholder="https://ejemplo.com/documento.pdf&#10;https://ejemplo.com/noticia"
                          class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all"><?= esc(old('evidencias')) ?></textarea>
            </div>

            <div>
                <label for="email_contacto" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Correo de contacto <span class="text-xs font-normal text-slate-400 dark:text-slate-500">(opcional, para que podamos pedirte más información)</span></label>
                <input type="email" id="email_contacto" name="email_contacto" value="<?= esc(old('email_contacto')) ?>" maxlength="255"
                       placeholder="tu@correo.com"
                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
            </div>

            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                <label for="acepta_terminos" class="flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" id="acepta_terminos" name="acepta_terminos" value="1" required
                           class="mt-0.5 w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-brand-600 focus:ring-2 focus:ring-brand-500">
                    <span class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Declaro que la información que proporciono es veraz según mi conocimiento, y acepto los
                        <a href="<?= url('/legal/politicas-legales') ?>" class="text-brand-600 dark:text-brand-400 hover:underline">términos legales</a>
                        y la
                        <a href="<?= url('/legal/politica-privacidad') ?>" class="text-brand-600 dark:text-brand-400 hover:underline">política de privacidad</a>.
                        Entiendo que la publicación de los hechos queda a criterio del equipo editorial.
                        <span class="text-red-500">*</span>
                    </span>
                </label>
            </div>

            <div class="g-recaptcha" data-sitekey="<?= esc($_ENV['RECAPTCHA_SITE_KEY'] ?? '') ?>"></div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/25 transition-all">
                <i data-lucide="send" class="w-4 h-4"></i>
                Enviar denuncia
            </button>
        </form>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php
$content = ob_get_clean();

\App\Core\View::renderLayout('public', $content, [
    'pageTitle' => $title . ' | AlphaLatam',
    'metaDescription' => 'Formulario de denuncia ciudadana. Envía hechos de presunta corrupción para que nuestro equipo editorial los revise e investigue.',
    'breadcrumbs' => [
        ['nombre' => 'Inicio', 'url' => url('/')],
        ['nombre' => 'Denuncias', 'url' => null],
    ],
]);
