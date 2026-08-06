<?php
$title = 'Quiénes Somos';
ob_start();
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Quiénes Somos</h1>

    <div class="prose prose-slate dark:prose-invert max-w-none">
        <p class="text-lg text-slate-600 dark:text-slate-300 mb-6">
            AlphaLatam es un medio de investigación periodística dedicado al seguimiento y análisis de casos de corrupción en América Latina.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Nuestra Misión</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Promover la transparencia y rendición de cuentas a través del periodismo de investigación, proporcionando información veraz y documentada sobre actos de corrupción que afectan a la sociedad.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Nuestra Visión</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Ser la plataforma de referencia en América Latina para el seguimiento de casos de corrupción, contribuyendo a una sociedad más justa y transparente.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Nuestros Valores</h2>
        <ul class="list-disc list-inside text-slate-600 dark:text-slate-300 space-y-2">
            <li><strong>Independencia:</strong> Nuestro trabajo editorial es independiente de cualquier grupo político o económico.</li>
            <li><strong>Veracidad:</strong> Cada dato publicado es verificado y documentado con fuentes confiables.</li>
            <li><strong>Transparencia:</strong> Operamos con total apertura sobre nuestros métodos y fuentes.</li>
            <li><strong>Compromiso social:</strong> Nuestro trabajo está al servicio de la ciudadanía.</li>
        </ul>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('public', $content, ['pageTitle' => $title]);
?>
