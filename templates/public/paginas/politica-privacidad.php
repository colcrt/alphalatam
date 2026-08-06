<?php
$title = 'Política de Privacidad';
ob_start();
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Política de Privacidad</h1>

    <div class="prose prose-slate dark:prose-invert max-w-none">
        <p class="text-slate-600 dark:text-slate-300 mb-6">
            <strong>Última actualización:</strong> <?php echo date('d/m/Y'); ?>
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Información que Recopilamos</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Recopilamos información que usted nos proporciona directamente, como:
        </p>
        <ul class="list-disc list-inside text-slate-600 dark:text-slate-300 space-y-2">
            <li>Nombre y correo electrónico al registrarse</li>
            <li>Comentarios en publicaciones</li>
            <li>Información de contacto al enviarnos mensajes</li>
        </ul>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Uso de la Información</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Utilizamos su información para:
        </p>
        <ul class="list-disc list-inside text-slate-600 dark:text-slate-300 space-y-2">
            <li>Proporcionar y mejorar nuestros servicios</li>
            <li>Enviar notificaciones sobre nuevas publicaciones</li>
            <li>Responder a sus consultas y comentarios</li>
            <li>Garantizar la seguridad de la plataforma</li>
        </ul>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Cookies</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Utilizamos cookies para mejorar su experiencia de navegación. Puede configurar su navegador para rechazar cookies, aunque esto podría afectar la funcionalidad del sitio.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Sus Derechos</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Usted tiene derecho a:
        </p>
        <ul class="list-disc list-inside text-slate-600 dark:text-slate-300 space-y-2">
            <li>Acceder a su información personal</li>
            <li>Solicitar la corrección de datos inexactos</li>
            <li>Solicitar la eliminación de su información</li>
            <li>Oponerse al procesamiento de sus datos</li>
        </ul>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Seguridad</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Implementamos medidas de seguridad técnicas y organizativas para proteger su información contra acceso no autorizado, alteración, divulgación o destrucción.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Cambios en esta Política</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Nos reservamos el derecho de actualizar esta política de privacidad. Los cambios serán publicados en esta página con la fecha de última actualización.
        </p>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-8 mb-4">Contacto</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">
            Para consultas sobre esta política de privacidad o sobre el tratamiento de sus datos personales, puede contactarnos a través de los medios disponibles en nuestro sitio.
        </p>
    </div>
</div>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('public', $content, ['pageTitle' => $title]);
?>
