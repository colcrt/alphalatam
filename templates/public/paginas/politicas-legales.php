<?php
$title = 'Políticas Legales';
ob_start();
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Políticas Legales</h1>

    <div class="prose prose-slate dark:prose-invert max-w-none">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Términos de Uso</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Al acceder y utilizar este sitio web, usted acepta los siguientes términos y condiciones. Si no está de acuerdo con alguno de estos términos, le recomendamos no utilizar el sitio.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Propiedad Intelectual</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Todo el contenido publicado en este sitio, incluyendo textos, imágenes, gráficos y logotipos, está protegido por las leyes de propiedad intelectual. Su uso está permitido únicamente con fines informativos y educativos, citando la fuente.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Responsabilidad</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            La información proporcionada en este sitio tiene carácter informativo. Nos esforzamos por mantener la precisión de los datos, pero no garantizamos la completitud o exactitud de la información publicada.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Enlaces Externos</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Este sitio puede contener enlaces a sitios web de terceros. No nos hacemos responsables del contenido o prácticas de privacidad de dichos sitios.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Contacto</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Para consultas legales, puede contactarnos a través de los medios disponibles en nuestro sitio.
        </p>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('public', $content, ['pageTitle' => $title]);
?>
