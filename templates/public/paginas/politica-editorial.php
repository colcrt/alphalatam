<?php
$title = 'Política Editorial';
ob_start();
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Política Editorial</h1>

    <div class="prose prose-slate dark:prose-invert max-w-none">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Principios Editoriales</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Nuestro trabajo se rige por los más altos estándares del periodismo de investigación. Cada publicación pasa por un riguroso proceso de verificación y edición.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Proceso de Verificación</h2>
        <ul class="list-disc list-inside text-slate-600 dark:text-slate-300 space-y-2">
            <li>Verificación de fuentes documentales</li>
            <li>Cruce de información con múltiples fuentes</li>
            <li>Revisión legal antes de la publicación</li>
            <li>Edición por al menos dos profesionales</li>
        </ul>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Fuentes</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Utilizamos fuentes públicas, documentos oficiales, bases de datos gubernamentales y testimonios verificados. Todas nuestras fuentes son documentadas y archivadas.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Correcciones</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Si detecta un error en nuestra información, por favor contáctenos. Publicaremos correcciones de manera transparente y oportuna.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Ética Periodística</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Respetamos los principios de la Declaración de Principios de la Sociedad Interamericana de Prensa. Nuestro trabajo es independiente, imparcial y al servicio de la verdad.
        </p>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('public', $content, ['pageTitle' => $title]);
?>
