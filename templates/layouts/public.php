<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

use App\Core\Auth\Auth;

/**
 * Public layout template — Tailwind CSS + Lucide Icons + Dark Mode
 *
 * Expected variables:
 *  string $content        Page body HTML
 *  string $pageTitle      <title> text
 *  string $metaDescription (optional) meta description
 *  string $canonical      (optional) canonical URL
 *  string $ogImage        (optional) OG image
 *  array  $breadcrumbs    (optional) [{nombre, url}]
 *  array  $jsonLd         (optional) JSON-LD structured data
 */
$_searchPlaceholder = 'Buscar artículos...';
$_isLoggedIn = Auth::check();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="google-adsense-account" content="ca-pub-2634424902791842">
    
	<title><?= esc($pageTitle ?? 'AlphaLatam') ?></title>

    <!-- Tailwind CSS (pre-compiled) -->
    <link rel="stylesheet" href="<?= asset('assets/css/tailwind-public.css') ?>?v=20260726">

    <!-- Google Fonts (non-blocking) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet"></noscript>
	
	<!-- Custom CSS -->
    <link href="<?= asset('assets/css/public.css') ?>?v=20260727" rel="stylesheet">

    <!-- SEO Meta -->
    <?php require __DIR__ . '/../partials/seo-meta.php'; ?>
</head>
<body class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased min-h-screen flex flex-col">
    <!-- NAVBAR -->
    <header class="sticky top-0 z-50 bg-white/90 dark:bg-slate-950/90 backdrop-blur-lg border-b border-slate-200 dark:border-slate-800">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" aria-label="Navegación principal">
            <div class="flex items-center justify-between h-16">
                <!-- Brand -->
                <a href="<?= url('/') ?>" class="flex items-center gap-2.5 shrink-0">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-600 text-white">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    </span>
                    <span class="text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">AlphaLatam</span>
                </a>

                <!-- Desktop nav -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="<?= url('/blog?tipo=noticia') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Noticias</a>
                    <a href="<?= url('/blog?tipo=opinion') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Opinión</a>
                    <a href="<?= url('/blog?tipo=investigacion') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Investigaciones</a>
                </div>

                <!-- Search + Actions -->
                <div class="flex items-center gap-3">
                    <!-- Search (desktop) -->
                    <form action="<?= esc(route('buscador.index')) ?>" method="GET" class="hidden sm:block relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                        <input type="search" name="q" placeholder="<?= $_searchPlaceholder ?>"
                               class="w-56 lg:w-72 pl-9 pr-3 py-2 text-sm bg-slate-100 dark:bg-slate-800 border-0 rounded-full text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:bg-white dark:focus:bg-slate-700 transition-all">
                    </form>

                    <!-- Dark mode toggle -->
                    <button id="dark-toggle" type="button"
                            class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            aria-label="Cambiar tema">
                        <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                        <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
                    </button>

                    <!-- Login / Dashboard (desktop) -->
                    <?php if ($_isLoggedIn): ?>
                    <a href="<?= url('/admin/dashboard') ?>" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-full shadow-md shadow-brand-600/25 hover:shadow-brand-600/40 transition-all">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        Dashboard
                    </a>
                    <?php else: ?>
                    <a href="<?= url('/login') ?>" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-800 rounded-full hover:bg-brand-50 dark:hover:bg-brand-950 transition-colors">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Iniciar sesión
                    </a>
                    <?php endif; ?>

                    <!-- Mobile menu toggle -->
                    <button id="mobile-menu-btn" type="button" class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Menú">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-slate-100 dark:border-slate-800 mt-2 pt-3">
                <div class="flex flex-col gap-1">
                    <a href="<?= url('/blog?tipo=noticia') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">Noticias</a>
                    <a href="<?= url('/blog?tipo=opinion') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">Opinión</a>
                    <a href="<?= url('/blog?tipo=investigacion') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">Investigaciones</a>
                    <?php if ($_isLoggedIn): ?>
                    <a href="<?= url('/admin/dashboard') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-950">Dashboard</a>
                    <?php else: ?>
                    <a href="<?= url('/login') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">Iniciar sesión</a>
                    <?php endif; ?>
                </div>
                <form action="<?= esc(route('buscador.index')) ?>" method="GET" class="mt-3 relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                    <input type="search" name="q" placeholder="<?= $_searchPlaceholder ?>"
                           class="w-full pl-9 pr-3 py-2 text-sm bg-slate-100 dark:bg-slate-800 border-0 rounded-full text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500">
                </form>
            </div>
        </nav>
    </header>

    <!-- BREADCRUMBS -->
    <?php if (!empty($breadcrumbs)): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>
    </div>
    <?php endif; ?>

    <!-- FLASH MESSAGES -->
    <?php if ($msg = flash('exito')): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-xl" role="alert">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span class="text-sm font-medium"><?= esc($msg) ?></span>
            <button onclick="this.parentElement.remove()" class="ml-auto p-1 hover:bg-green-100 dark:hover:bg-green-900 rounded-lg transition-colors" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-xl" role="alert">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <span class="text-sm font-medium"><?= esc($msg) ?></span>
            <button onclick="this.parentElement.remove()" class="ml-auto p-1 hover:bg-red-100 dark:hover:bg-red-900 rounded-lg transition-colors" aria-label="Cerrar">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1">
        <?= $content ?>
    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-600 text-white">
                            <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        </span>
                        <span class="text-lg font-extrabold text-white">AlphaLatam</span>
                    </div>
                    <p class="text-sm leading-relaxed max-w-xs">Información veraz, análisis independiente y seguimiento a casos de corrupción. Datos y hechos para la ciudadanía.</p>
                </div>

                <!-- Sections -->
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Secciones</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="<?= url('/blog?tipo=noticia') ?>" class="hover:text-white transition-colors">Noticias</a></li>
                        <li><a href="<?= url('/blog?tipo=opinion') ?>" class="hover:text-white transition-colors">Opinión</a></li>
                        <li><a href="<?= url('/blog?tipo=investigacion') ?>" class="hover:text-white transition-colors">Investigaciones</a></li>
                        <li><a href="<?= url('/denuncias') ?>" class="hover:text-white transition-colors">Denuncias</a></li>
                        <li><a href="<?= url('/blog') ?>" class="hover:text-white transition-colors">Blog</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Legal</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="<?= url('/legal/quienes-somos') ?>" class="hover:text-white transition-colors">Quiénes Somos</a></li>
                        <li><a href="<?= url('/legal/politicas-legales') ?>" class="hover:text-white transition-colors">Políticas Legales</a></li>
                        <li><a href="<?= url('/legal/politica-editorial') ?>" class="hover:text-white transition-colors">Política Editorial</a></li>
                        <li><a href="<?= url('/legal/transparencia') ?>" class="hover:text-white transition-colors">Transparencia</a></li>
                        <li><a href="<?= url('/legal/politica-privacidad') ?>" class="hover:text-white transition-colors">Política de Privacidad</a></li>
                    </ul>
                </div>

                <!-- Social -->
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Síguenos</h3>
                    <div class="flex gap-3">
                        <a href="#" class="flex items-center justify-center w-10 h-10 rounded-lg bg-slate-800 hover:bg-brand-600 text-slate-400 hover:text-white transition-all" aria-label="Twitter">
                            <i data-lucide="twitter" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="flex items-center justify-center w-10 h-10 rounded-lg bg-slate-800 hover:bg-brand-600 text-slate-400 hover:text-white transition-all" aria-label="Facebook">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="flex items-center justify-center w-10 h-10 rounded-lg bg-slate-800 hover:bg-brand-600 text-slate-400 hover:text-white transition-all" aria-label="YouTube">
                            <i data-lucide="youtube" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-800 mt-8 pt-8 text-xs text-center text-slate-500">
                &copy; <?= date('Y') ?> AlphaLatam. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- Dark mode + Mobile menu init -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dark mode
        const html = document.documentElement;
        const stored = localStorage.getItem('theme');
        if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        }
        document.getElementById('dark-toggle')?.addEventListener('click', function() {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });

        // Mobile menu
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });

        // Load embed scripts only when embeds are present on the page
        function loadEmbedScripts() {
            // Twitter/X widgets
            if (document.querySelector('.twitter-tweet, .embed-twitter, [data-tweet-id]')) {
                if (!document.getElementById('twitter-wjs')) {
                    var s = document.createElement('script');
                    s.id = 'twitter-wjs';
                    s.src = 'https://platform.twitter.com/widgets.js';
                    s.async = true;
                    document.body.appendChild(s);
                }
            }
            // TikTok embeds
            if (document.querySelector('.tiktok-embed, .embed-tiktok')) {
                if (!document.getElementById('tiktok-embed-js')) {
                    var s = document.createElement('script');
                    s.id = 'tiktok-embed-js';
                    s.src = 'https://www.tiktok.com/embed.js';
                    s.async = true;
                    document.body.appendChild(s);
                }
            }
            // Instagram embeds
            if (document.querySelector('.instagram-media, .embed-instagram')) {
                if (!window.instgrm) {
                    var s = document.createElement('script');
                    s.src = 'https://www.instagram.com/embed.js';
                    s.async = true;
                    document.body.appendChild(s);
                }
            }
            // Reddit embeds
            if (document.querySelector('.reddit-embed, .embed-reddit')) {
                if (!document.getElementById('reddit-embed-js')) {
                    var s = document.createElement('script');
                    s.id = 'reddit-embed-js';
                    s.src = 'https://www.redditstatic.com/comment-embed.js';
                    s.async = true;
                    document.body.appendChild(s);
                }
            }
        }
        loadEmbedScripts();
    });
    </script>
    <!-- Lucide Icons (deferred, non-blocking) -->
    <script defer src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.min.js" onload="if(typeof lucide!=='undefined')lucide.createIcons()"></script>

	<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2634424902791842"
     crossorigin="anonymous"></script>
	 
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-T98ZBVXF57"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'G-T98ZBVXF57');
	</script>
</body>
</html>
