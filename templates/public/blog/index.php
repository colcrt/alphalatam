<?php
declare(strict_types=1);

$tipos = [
    '' => 'Todos',
    'noticia' => 'Noticias',
    'opinion' => 'Opinión',
    'investigacion' => 'Investigaciones',
];

$tipoBadgeMap = [
    'noticia' => 'bg-brand-700 text-white dark:bg-brand-900 dark:text-brand-100',
    'opinion' => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
    'investigacion' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-200',
];

$tipoLabelMap = [
    'noticia' => 'Noticia',
    'opinion' => 'Opinión',
    'investigacion' => 'Investigación',
];

ob_start();
?>

<!-- Header -->
<section class="block-navy border-b border-brand-900 dark:border-brand-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-extrabold text-white mb-4">Blog</h1>

        <!-- Filter pills -->
        <div class="flex flex-wrap gap-2">
            <?php foreach ($tipos as $key => $label): ?>
            <?php
                $isActive = ($tipoActual ?? null) === $key || (!$tipoActual && $key === '');
                $activeClass = $isActive
                    ? 'bg-white text-brand-900 shadow-md shadow-brand-950/20'
                    : 'bg-white/10 text-brand-50 border border-white/20 hover:bg-white/20 hover:text-white';
            ?>
            <a href="<?= esc(url('/blog') . ($key ? '?tipo=' . $key : '')) ?>"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 <?= $activeClass ?>">
                <?= esc($label) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <p class="text-sm text-brand-200 mt-3"><?= $paginator['total'] ?> artículos</p>
    </div>
</section>

<!-- Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (empty($paginator['data'])): ?>
    <div class="text-center py-16">
        <i data-lucide="file-text" class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-4"></i>
        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-1">No hay artículos</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">No hay artículos publicados en esta categoría.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($paginator['data'] as $post): ?>
        <?php
            $tipo = $post->tipo ?? '';
            $badge = $tipoBadgeMap[$tipo] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
            $label = $tipoLabelMap[$tipo] ?? ucfirst($tipo);
        ?>
        <article class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-card hover:shadow-lift transition-all duration-300 hover:-translate-y-0.5">
            <a href="<?= esc(route('blog.show', ['slug' => $post->slug])) ?>" class="block">
                <?= render_card_media($post, '', 'w-full h-48 object-cover')
                    ?: '<div class="w-full h-48 bg-gradient-to-br from-brand-50 to-brand-100 dark:from-brand-950 dark:to-brand-900 flex items-center justify-center"><i data-lucide="file-text" class="w-10 h-10 text-brand-300 dark:text-brand-700"></i></div>' ?>

                <div class="p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider <?= $badge ?>"><?= esc($label) ?></span>
                        <?php if (!empty($post->categoria_nombre)): ?>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-200"><?= esc($post->categoria_nombre) ?></span>
                        <?php endif; ?>
                    </div>

                    <h2 class="text-base font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors leading-snug">
                        <?= esc($post->titulo) ?>
                    </h2>

                    <?php if (!empty($post->extracto)): ?>
                    <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mb-4"><?= esc(str_limit($post->extracto, 120)) ?></p>
                    <?php endif; ?>

                    <div class="flex items-center text-xs text-slate-500 dark:text-slate-400">
                        <?php if (!empty($post->autor_nombre)): ?>
                        <span class="inline-flex items-center gap-1"><i data-lucide="user" class="w-3.5 h-3.5"></i> <?= esc($post->autor_nombre) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($post->published_at)): ?>
                        <span class="inline-flex items-center gap-1 ml-auto"><i data-lucide="calendar-days" class="w-3.5 h-3.5"></i> <?= esc(date_format_es($post->published_at)) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($paginator['has_pages']): ?>
    <nav aria-label="Paginación" class="mt-10">
        <ul class="flex items-center justify-center gap-1.5">
            <li class="<?= $paginator['current_page'] <= 1 ? 'pointer-events-none opacity-40' : '' ?>">
                <a href="<?= esc(url('/blog') . ($tipoActual ? '?tipo=' . $tipoActual . '&' : '?') . 'page=' . ($paginator['current_page'] - 1)) ?>"
                   class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i> Anterior
                </a>
            </li>
            <?php for ($i = 1; $i <= $paginator['last_page']; $i++): ?>
            <li>
                <a href="<?= esc(url('/blog') . ($tipoActual ? '?tipo=' . $tipoActual . '&' : '?') . 'page=' . $i) ?>"
                   class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium rounded-lg transition-colors <?= $i === $paginator['current_page'] ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
            <li class="<?= !$paginator['has_more_pages'] ? 'pointer-events-none opacity-40' : '' ?>">
                <a href="<?= esc(url('/blog') . ($tipoActual ? '?tipo=' . $tipoActual . '&' : '?') . 'page=' . ($paginator['current_page'] + 1)) ?>"
                   class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Siguiente <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();

\App\Core\View::renderLayout('public', $content, [
    'pageTitle' => 'Blog',
    'metaDescription' => 'Noticias, artículos de opinión e investigaciones sobre corrupción.',
    'breadcrumbs' => [
        ['nombre' => 'Inicio', 'url' => url('/')],
        ['nombre' => 'Blog', 'url' => null],
    ],
]);
