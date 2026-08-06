# AlphaLatam — Blog de Corrupción

Plataforma de periodismo y documentación ciudadana dedicada a investigar, registrar y
denunciar casos de corrupción en América Latina. Desarrollada por **colcrt** (2025–2026).

## ¿De qué trata el proyecto?

AlphaLatam es un medio digital independiente que combina un blog de investigación con
herramientas de participación ciudadana. Permite publicar artículos e investigaciones,
mantener un repositorio de fuentes documentales, buscar contenido histórico mediante
búsqueda de texto completo y recibir denuncias anónimas de la comunidad. El panel de
administración está pensado para que el equipo editorial redacte, revise y publique
contenido verificable y con trazabilidad de fuentes.

## Tecnologías

- **PHP 8.1+** — framework propio (núcleo en `src/Core/`), sin Laravel, Livewire ni Blade.
- **MySQL** a través de **PDO** con capa propia: `Connection`, `QueryBuilder` y un ORM
  ligero (`App\Database\Model`) con casts, soft deletes, relaciones y observadores.
- **Tailwind CSS 3.4** — compilado con dark mode (`class`), dos builds (público y admin).
- **Vue 3** (CDN UMD) + **Quill.js** — SPA de administración en `assets/js/admin/app.js`.
- **Lucide icons** y **Chart.js** para la interfaz y estadísticas.
- **Patrón Repository → Service** con DTOs (`final readonly class`) y Enums con etiquetas.
- **Seguridad**: bcrypt (cost 12), sesiones con CSRF, 2FA TOTP con códigos de respaldo,
  roles (admin, editor, revisor, auditor), sanitización de HTML y soft deletes.
- **SEO**: meta tags, JSON-LD, sitemap XML dinámico y URLs amigables (slugs).
- **Búsqueda** FULLTEXT de MySQL y resolución **oEmbed** (YouTube, X, TikTok, etc.).

## Estructura

    src/Core/          Router, Request/Response, View, Validator, Session, Cache, Auth
    src/Database/      Conexión PDO, QueryBuilder, Model (ORM propio)
    src/Domain/        DTOs y Enums por módulo (Caso, Contrato, Fuente, Noticia, Persona…)
    src/Repositories/  Contratos + implementaciones (patrón Repository)
    src/Services/      Lógica de negocio (Blog, Busqueda, SEO, 2FA, Noticias, Partidos…)
    src/Http/          Controllers públicos, admin y API JSON (usada por el SPA Vue)
    src/Observers/     Auditoría y limpieza de caché
    templates/         Vistas en PHP puro (layouts, público, admin, auth)
    routes/web.php     Todas las rutas de la aplicación
    migrations/        Esquema SQL de MySQL
    assets/            CSS compilado y JS (SPA Vue)
    scripts/           cron-backup.php (dump + limpieza)

## Módulos

- **Blog** (completo): artículos, categorías, comentarios, versionado y SEO.
- **Noticias, Partidos Políticos** con repositorio y servicio (regla: no publicar sin fuente).
- **Fuentes** — repositorio documental para respaldar las publicaciones.
- Persona, Caso, Empresa, Institución, Sentencia, Contrato (DTOs y administración).
- **Extras**: buscador global, denuncias ciudadanas, encuesta/reto de la comunidad,
  boletín/suscriptores y bloqueo 2FA.

## Despliegue

En producción se ejecuta sobre un hosting compartido con MySQL, accesible en
**https://alphalatam.click/**. No requiere Node en el servidor: el CSS ya está
compilado y el front se sirve por CDN.

## Comandos

    npm install            # Instala Tailwind (dev)
    npm run build          # Compila el CSS público y de admin
    php -f scripts/cron-backup.php   # Backup manual de la BD

## Autor y derechos

© **colcrt** — Todos los derechos reservados. Este proyecto es de autoría exclusiva de
**colcrt**. **Está prohibida la reproducción, distribución o uso total o parcial** de este
código, su diseño o su contenido sin autorización expresa y por escrito del autor..
