<?php
$title = 'Transparencia';
ob_start();
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Transparencia</h1>

    <div class="prose prose-slate dark:prose-invert max-w-none">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Nuestro Compromiso</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Creemos que la transparencia es fundamental para la credibilidad periodística. Por ello, hacemos público cómo operamos, quiénes somos y cómo financiamos nuestro trabajo.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Financiamiento</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Nuestro financiamiento proviene de:
        </p>
        <ul class="list-disc list-inside text-slate-600 dark:text-slate-300 space-y-2">
            <li>Cuotas de suscripción de lectores</li>
            <li>Donaciones de fundaciones sin fines de lucro</li>
            <li>Ventas de sindicación de contenido</li>
        </ul>
        <p class="text-slate-600 dark:text-slate-300 mb-4 mt-4">
            No aceptamos financiamiento de gobiernos, partidos políticos ni empresas que puedan comprometer nuestra independencia editorial.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Equipo</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Nuestro equipo está compuesto por periodistas profesionales con experiencia en investigación. Cada miembro declara posibles conflictos de interés.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Métodos</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Utilizamos bases de datos públicas, solicitudes de acceso a la información, entrevistas en profundidad y análisis documental. Nuestros métodos están disponibles para consulta.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Contacto</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Para consultas sobre nuestra operación y transparencia, puede contactarnos a través de los medios disponibles en nuestro sitio.
        </p>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('public', $content, ['pageTitle' => $title]);
?>
