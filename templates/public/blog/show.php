<?php
declare(strict_types=1);

$tipoLabelMap = [
    'noticia' => 'Noticia',
    'opinion' => 'Opinión',
    'investigacion' => 'Investigación',
];
$tipoBadgeMap = [
    'noticia' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
    'opinion' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
    'investigacion' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
];

ob_start();
?>

<!-- Article Header -->
<article itemscope itemtype="https://schema.org/NewsArticle">
    <header class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
            <!-- Type badge -->
            <?php if (!empty($post->tipo)): ?>
            <?php
                $tipo = $post->tipo;
                $badge = $tipoBadgeMap[$tipo] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                $label = $tipoLabelMap[$tipo] ?? ucfirst($tipo);
            ?>
            <div class="mb-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?= $badge ?>"><?= esc($label) ?></span>
            </div>
            <?php endif; ?>

            <!-- Title -->
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white leading-tight tracking-tight mb-4" itemprop="headline">
                <?= esc($post->titulo) ?>
            </h1>

            <!-- Meta -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                <?php if (!empty($post->autor_nombre)): ?>
                <span class="inline-flex items-center gap-1.5" itemprop="author" itemscope itemtype="https://schema.org/Person">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span itemprop="name"><?= esc($post->autor_nombre) ?></span>
                </span>
                <?php endif; ?>
                <?php if (!empty($post->published_at)): ?>
                <time class="inline-flex items-center gap-1.5" datetime="<?= esc($post->published_at) ?>" itemprop="datePublished">
                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    <?= esc(date_format_es($post->published_at)) ?>
                </time>
                <?php endif; ?>
                <?php if (!empty($post->categoria_nombre)): ?>
                <span class="inline-flex items-center gap-1.5">
                    <i data-lucide="folder" class="w-4 h-4"></i>
                    <?= esc($post->categoria_nombre) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Featured Image -->
    <?php if (!empty($post->imagen_destacada_path)): ?>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
        <figure>
            <img src="<?= esc(asset('uploads/' . $post->imagen_destacada_path)) ?>"
                 alt="<?= esc($post->titulo) ?>"
                 itemprop="image"
                 width="1200" height="675"
                 fetchpriority="high"
                 decoding="async"
                 class="w-full rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800"
                 loading="eager">
        </figure>
    </div>
    <?php endif; ?>

    <!-- Article Body -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
        <div class="post-content" itemprop="articleBody">
            <?= $post->contenido ?>
        </div>

        <!-- Share -->
        <div class="mt-10 pt-8 border-t border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Compartir artículo</span>
                <div class="flex gap-2">
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(url('/blog/post/' . $post->slug)) ?>&text=<?= urlencode($post->titulo) ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-sky-100 dark:hover:bg-sky-900 hover:text-sky-600 dark:hover:text-sky-400 transition-colors" aria-label="Compartir en Twitter">
                        <i data-lucide="twitter" class="w-4 h-4"></i> Twitter
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('/blog/post/' . $post->slug)) ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" aria-label="Compartir en Facebook">
                        <i data-lucide="facebook" class="w-4 h-4"></i> Facebook
                    </a>
                </div>
            </div>
        </div>
    </div>
</article>

<!-- Related Posts -->
<?php if (!empty($postsRelacionados)): ?>
<section class="bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Artículos Relacionados</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($postsRelacionados as $rel): ?>
            <?php
                $rTipo = $rel->tipo ?? '';
                $rBadge = $tipoBadgeMap[$rTipo] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                $rLabel = $tipoLabelMap[$rTipo] ?? ucfirst($rTipo);
            ?>
            <a href="<?= esc(route('blog.show', ['slug' => $rel->slug])) ?>" class="group block bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                <?php if (!empty($rel->imagen_destacada_path)): ?>
                <img src="<?= esc(asset('uploads/' . $rel->imagen_destacada_path)) ?>" alt="<?= esc($rel->titulo) ?>" width="400" height="160" loading="lazy" decoding="async" class="w-full h-40 object-cover">
                <?php else: ?>
                <div class="w-full h-40 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-8 h-8 text-slate-300 dark:text-slate-500"></i>
                </div>
                <?php endif; ?>
                <div class="p-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?= $rBadge ?> mb-2"><?= esc($rLabel) ?></span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                        <?= esc($rel->titulo) ?>
                    </h3>
                    <span class="text-xs text-slate-400 dark:text-slate-500 mt-2 inline-block"><?= esc(date_format_es($rel->published_at ?? '')) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$content = ob_get_clean();

\App\Core\View::renderLayout('public', $content, [
    'pageTitle' => ($post->meta_title ?: $post->titulo) . ' — AlphaLatam',
    'metaDescription' => $post->meta_description ?: $post->extracto,
    'canonical' => $post->canonical_url ?: url('/blog/post/' . $post->slug),
    'ogImage' => $post->imagen_destacada_path ? asset('uploads/' . $post->imagen_destacada_path) : '',
    'preloadImage' => $post->imagen_destacada_path ? asset('uploads/' . $post->imagen_destacada_path) : '',
    'breadcrumbs' => [
        ['nombre' => 'Inicio', 'url' => url('/')],
        ['nombre' => 'Blog', 'url' => url('/blog')],
        ['nombre' => $post->titulo, 'url' => null],
    ],
    'jsonLd' => [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $post->titulo,
        'description' => $post->extracto ?? '',
        'author' => ['@type' => 'Person', 'name' => $post->autor_nombre ?? 'Admin'],
        'datePublished' => $post->published_at ?? '',
        'dateModified' => $post->updated_at ?? '',
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'AlphaLatam',
        ],
        'mainEntityOfPage' => url('/blog/post/' . $post->slug),
        'image' => $post->imagen_destacada_path ? asset('uploads/' . $post->imagen_destacada_path) : '',
    ],
]);
