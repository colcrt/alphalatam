<?php declare(strict_types=1); ?>
<?php
ob_start();

$noticias = $noticiasRecientes ?? [];
$opinion = $opinionRecientes ?? [];
$investigaciones = $investigacionesRecientes ?? [];
$estelar = $estelar ?? null;

function render_post_card(array $post, string $badgeClass, string $tipoLabel, string $size = 'normal'): string
{
    $slug = esc($post['slug'] ?? '');
    $titulo = esc($post['titulo'] ?? '');
    $extracto = esc(str_limit($post['extracto'] ?? '', 140));
    $fecha = esc(date_format_es($post['published_at'] ?? ''));
    $imagen = $post['imagen_destacada_path'] ?? '';
    $href = url('/blog/post/' . $slug);

    $imgHtml = $imagen
        ? '<img src="' . esc(asset('uploads/' . $imagen)) . '" alt="' . $titulo . '" width="400" height="192" loading="lazy" decoding="async" class="w-full h-48 object-cover rounded-t-xl">'
        : '<div class="w-full h-48 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 rounded-t-xl flex items-center justify-center"><i data-lucide="file-text" class="w-10 h-10 text-slate-300 dark:text-slate-600"></i></div>';

    $imgThumb = $imagen
        ? '<img src="' . esc(asset('uploads/' . $imagen)) . '" alt="' . $titulo . '" width="160" height="112" loading="lazy" decoding="async" class="w-24 h-20 sm:w-32 sm:h-24 object-cover rounded-lg shrink-0">'
        : '<div class="w-24 h-20 sm:w-32 sm:h-24 shrink-0 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center"><i data-lucide="file-text" class="w-6 h-6 text-slate-300 dark:text-slate-600"></i></div>';

    $imgSplit = $imagen
        ? '<img src="' . esc(asset('uploads/' . $imagen)) . '" alt="' . $titulo . '" width="640" height="360" loading="lazy" decoding="async" class="w-full h-48 lg:h-full object-cover">'
        : '<div class="w-full h-48 lg:h-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center"><i data-lucide="file-text" class="w-10 h-10 text-slate-300 dark:text-slate-600"></i></div>';

    if ($size === 'featured') {
        return <<<HTML
    <article class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <a href="{$href}" class="block">
            {$imgHtml}
            <div class="p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {$badgeClass}">{$tipoLabel}</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500"><i data-lucide="calendar-days" class="w-3.5 h-3.5 inline -mt-0.5"></i> {$fecha}</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{$titulo}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-3">{$extracto}</p>
                <div class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 dark:text-brand-400 group-hover:gap-2.5 transition-all">
                    Leer más <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </div>
            </div>
        </a>
    </article>
HTML;
    }

    if ($size === 'split') {
        return <<<HTML
    <article class="group h-full bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <a href="{$href}" class="block h-full lg:grid lg:grid-cols-2">
            {$imgSplit}
            <div class="p-5 flex flex-col justify-center">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {$badgeClass}">{$tipoLabel}</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{$fecha}</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1.5 line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors leading-snug">{$titulo}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-3">{$extracto}</p>
                <div class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 dark:text-brand-400 group-hover:gap-2.5 transition-all">
                    Leer más <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </div>
            </div>
        </a>
    </article>
HTML;
    }

    if ($size === 'compact') {
        return <<<HTML
    <article class="group h-full bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <a href="{$href}" class="flex gap-4 p-3 h-full">
            {$imgThumb}
            <div class="min-w-0 flex flex-col justify-center">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {$badgeClass}">{$tipoLabel}</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{$fecha}</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors leading-snug">{$titulo}</h3>
            </div>
        </a>
    </article>
HTML;
    }

    $imgSmall = $imagen
        ? '<img src="' . esc(asset('uploads/' . $imagen)) . '" alt="' . $titulo . '" width="400" height="160" loading="lazy" decoding="async" class="w-full h-40 object-cover rounded-t-xl">'
        : '<div class="w-full h-40 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 rounded-t-xl flex items-center justify-center"><i data-lucide="file-text" class="w-10 h-10 text-slate-300 dark:text-slate-600"></i></div>';

    if ($size === 'grid4') {
        return <<<HTML
    <article class="group h-full bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <a href="{$href}" class="block h-full">
            {$imgSmall}
            <div class="p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {$badgeClass}">{$tipoLabel}</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{$fecha}</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5 line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors leading-snug">{$titulo}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">{$extracto}</p>
            </div>
        </a>
    </article>
HTML;
    }

    $imgGrid3 = $imagen
        ? '<img src="' . esc(asset('uploads/' . $imagen)) . '" alt="' . $titulo . '" width="400" height="176" loading="lazy" decoding="async" class="w-full h-44 object-cover rounded-t-xl">'
        : '<div class="w-full h-44 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 rounded-t-xl flex items-center justify-center"><i data-lucide="file-text" class="w-10 h-10 text-slate-300 dark:text-slate-600"></i></div>';

    if ($size === 'grid3') {
        return <<<HTML
    <article class="group h-full bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <a href="{$href}" class="block h-full">
            {$imgGrid3}
            <div class="p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {$badgeClass}">{$tipoLabel}</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{$fecha}</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5 line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors leading-snug">{$titulo}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">{$extracto}</p>
            </div>
        </a>
    </article>
HTML;
    }

    return <<<HTML
    <article class="group bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <a href="{$href}" class="block">
            {$imgHtml}
            <div class="p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {$badgeClass}">{$tipoLabel}</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{$fecha}</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5 line-clamp-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors leading-snug">{$titulo}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">{$extracto}</p>
            </div>
        </a>
    </article>
HTML;
}
?>

<!-- CONTENIDO -->
<div id="contenido" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- SECCIÓN ESTELAR -->
        <?php if ($estelar): ?>
        <?php
            $eTipo = $estelar->tipo ?? 'noticia';
            $eBadgeMap = [
                'noticia' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                'opinion' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                'investigacion' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
            ];
            $eLabelMap = [
                'noticia' => 'Noticia',
                'opinion' => 'Opinión',
                'investigacion' => 'Investigación',
            ];
            $eBadge = $eBadgeMap[$eTipo] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
            $eLabel = $eLabelMap[$eTipo] ?? ucfirst($eTipo);
            $eHref = url('/blog/post/' . esc($estelar->slug));
            $eImg = $estelar->imagen_destacada_path ?? '';
        ?>
        <section class="mb-12">
            <div class="flex items-center gap-2 mb-5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-brand-600 text-white shadow-md shadow-brand-600/25">
                    <i data-lucide="star" class="w-3.5 h-3.5"></i> Nota principal
                </span>
            </div>
            <article data-no-reveal class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <a href="<?= $eHref ?>" class="block lg:grid lg:grid-cols-2">
                    <?php if ($eImg): ?>
                    <div class="relative h-56 lg:h-full overflow-hidden">
                        <img src="<?= esc(asset('uploads/' . $eImg)) ?>" alt="<?= esc($estelar->titulo) ?>"
                             width="1200" height="675" decoding="async"
                             class="parallax w-full h-full object-cover" data-parallax>
                    </div>
                    <?php else: ?>
                    <div class="w-full h-56 lg:h-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-12 h-12 text-slate-300 dark:text-slate-600"></i>
                    </div>
                    <?php endif; ?>
                    <div class="p-6 md:p-8 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $eBadge ?>"><?= esc($eLabel) ?></span>
                            <span class="text-xs text-slate-400 dark:text-slate-500"><?= esc(date_format_es($estelar->published_at ?? '')) ?></span>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white mb-3 leading-tight group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                            <?= esc($estelar->titulo) ?>
                        </h2>
                        <p class="text-sm md:text-base text-slate-500 dark:text-slate-400 line-clamp-3 leading-relaxed"><?= esc($estelar->extracto ?? '') ?></p>
                        <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                            <?php if (!empty($estelar->autor_nombre)): ?>
                            <span class="inline-flex items-center gap-1.5"><i data-lucide="user" class="w-4 h-4"></i> <?= esc($estelar->autor_nombre) ?></span>
                            <?php endif; ?>
                            <span class="ml-auto inline-flex items-center gap-1.5 font-semibold text-brand-600 dark:text-brand-400 group-hover:gap-2.5 transition-all">
                                Leer más <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </span>
                        </div>
                    </div>
                </a>
            </article>
        </section>
        <?php endif; ?>

        <!-- NOTICIAS -->
        <?php if (!empty($noticias)): ?>
        <?php
            $noticiasItems = array_values($noticias);
            $noticiasSplit = $noticiasItems[0] ?? null;
            $noticiasStack = array_slice($noticiasItems, 1, 2);
            $noticiasWide = array_slice($noticiasItems, 3, 7);
            $badgeNoticia = 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300';
        ?>
        <section>
            <div class="seccion-empuje flex items-end justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Últimas Noticias</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Cobertura reciente sobre hechos noticiosos</p>
                </div>
                <a href="<?= url('/blog?tipo=noticia') ?>" class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition-colors">
                    Ver todas <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <?php if ($noticiasSplit): ?>
                <div class="h-full">
                    <?= render_post_card((array) $noticiasSplit, $badgeNoticia, 'Noticia', 'split') ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($noticiasStack)): ?>
                <div class="flex flex-col gap-5 h-full">
                    <?php foreach ($noticiasStack as $post): ?>
                        <?= render_post_card((array) $post, $badgeNoticia, 'Noticia', 'compact') ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($noticiasWide)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-5">
                <?php foreach ($noticiasWide as $post): ?>
                    <?= render_post_card((array) $post, $badgeNoticia, 'Noticia', 'grid4') ?>
                <?php endforeach; ?>
                <?php if (!empty($encuesta)): ?>
                <div data-reveal class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden h-full">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                        <p class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Trivia</p>
                    </div>
                    <div class="p-5" id="encuesta-widget" data-token="<?= esc(csrf_token()) ?>">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white mb-4 leading-snug"><?= esc($encuesta['pregunta']) ?></p>
                        <div id="encuesta-opciones">
                            <?php foreach ($encuesta['opciones'] as $i => $opcion): ?>
                            <button type="button" data-opcion="<?= (int) $i ?>" data-texto="<?= esc($opcion['texto']) ?>"
                                    class="encuesta-btn w-full text-left px-4 py-2.5 mb-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:border-brand-400 hover:bg-brand-50 dark:hover:bg-brand-950 transition-all">
                                <?= esc($opcion['texto']) ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <p id="encuesta-meta" class="text-xs text-slate-400 dark:text-slate-500 mt-3">Voto único por navegador.</p>
                    </div>
                </div>
                <script type="application/json" id="encuesta-data"><?= json_encode($encuesta ?? null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>
        <hr class="max-w-7xl mx-auto my-12 border-slate-200 dark:border-slate-800">
        <?php endif; ?>

        <!-- OPINIÓN -->
        <?php if (!empty($opinion)): ?>
        <?php
            $opinionItems = array_values($opinion);
            $opinionSplit = $opinionItems[0] ?? null;
            $opinionStacked = array_slice($opinionItems, 1, 3);
            $opinionWide = array_slice($opinionItems, 4, 7);
            $badgeOpinion = 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300';
        ?>
        <section>
            <div class="seccion-empuje flex items-end justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Artículos de Opinión</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Análisis y reflexiones de expertos</p>
                </div>
                <a href="<?= url('/blog?tipo=opinion') ?>" class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition-colors">
                    Ver todos <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <?php if ($opinionSplit): ?>
                <div class="h-full">
                    <?= render_post_card((array) $opinionSplit, $badgeOpinion, 'Opinión', 'split') ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($opinionStacked)): ?>
                <div class="flex flex-col gap-5 h-full">
                    <?php foreach ($opinionStacked as $post): ?>
                        <?= render_post_card((array) $post, $badgeOpinion, 'Opinión', 'compact') ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($opinionWide)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-5">
                <?php foreach ($opinionWide as $post): ?>
                    <?= render_post_card((array) $post, $badgeOpinion, 'Opinión', 'grid4') ?>
                <?php endforeach; ?>
                <?php if (!empty($reto)): ?>
                <div data-reveal class="bg-white dark:bg-slate-900 rounded-2xl border border-amber-300 dark:border-amber-900 shadow-sm overflow-hidden h-full">
                    <div class="px-5 py-4 border-b border-amber-100 dark:border-amber-900 bg-amber-50 dark:bg-amber-950/40">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Reto de la comunidad</p>
                    </div>
                    <div class="p-5" id="reto-widget" data-token="<?= esc(csrf_token()) ?>">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white mb-4 leading-snug"><?= esc($reto['pregunta']) ?></p>
                        <div id="reto-opciones">
                            <?php foreach ($reto['opciones'] as $i => $opcion): ?>
                            <button type="button" data-opcion="<?= (int) $i ?>" data-texto="<?= esc($opcion['texto']) ?>"
                                    class="reto-btn w-full text-left px-4 py-2.5 mb-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950 transition-all">
                                <?= esc($opcion['texto']) ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <p id="reto-meta" class="text-xs text-slate-400 dark:text-slate-500 mt-3">Voto único por navegador. Al votar se revela la respuesta correcta.</p>
                    </div>
                </div>
                <script type="application/json" id="reto-data"><?= json_encode($reto ?? null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>
        <hr class="max-w-7xl mx-auto my-12 border-slate-200 dark:border-slate-800">
        <?php endif; ?>

        <!-- INVESTIGACIONES -->
        <?php if (!empty($investigaciones)): ?>
        <section>
            <div class="seccion-empuje flex items-end justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Investigaciones</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Periodismo de investigación</p>
                </div>
                <a href="<?= url('/blog?tipo=investigacion') ?>" class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition-colors">
                    Ver todas <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($investigaciones as $post): ?>
                    <?= render_post_card((array) $post, 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300', 'Investigación', 'grid3') ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
</div>

<!-- HERO COMPACTO -->
<section class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 border-b border-slate-200 dark:border-slate-800">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-7">
        <div class="lg:max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-brand-50 dark:bg-brand-950 border border-brand-200 dark:border-brand-800 rounded-full text-brand-700 dark:text-brand-300 text-xs font-semibold mb-3">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                Plataforma de periodismo investigativo
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white leading-tight tracking-tight">
                Periodismo de investigación, noticias y análisis sobre Colombia y América Latina
            </h1>
            <p class="text-sm md:text-base text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                Investigaciones respaldadas por documentos, datos y contexto para entender el poder más allá de los titulares.
            </p>
        </div>
        <div class="mt-6 lg:mt-8 max-w-xl">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                Boletín diario
            </p>
            <form action="<?= esc(url('/boletin/suscribir')) ?>" method="POST" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="email" name="email" required placeholder="Tu correo electrónico"
                       class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                <button type="submit" class="shrink-0 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200 text-sm font-semibold rounded-xl shadow-md shadow-slate-900/25 hover:shadow-slate-900/40 transition-all">
                    Suscribirme
                </button>
            </form>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Resumen diario de investigaciones en tu correo.</p>
        </div>
    </div>
</section>

<div id="dossier-progress" aria-hidden="true">
    <div class="dossier-fill"></div>
    <span class="dossier-label">Expediente</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var contenido = document.getElementById('contenido');
    if (!contenido) return;

    /* --- Reveal escalonado de tarjetas --- */
    if (!reduced && 'IntersectionObserver' in window) {
        var targets = contenido.querySelectorAll('article.group:not([data-no-reveal]), [data-reveal]');
        var buckets = new Map();
        targets.forEach(function (el) {
            var parent = el.parentNode;
            if (!buckets.has(parent)) buckets.set(parent, []);
            buckets.get(parent).push(el);
        });

        var cola = [];
        buckets.forEach(function (arr) {
            arr.forEach(function (el, i) {
                cola.push({ el: el, delay: Math.min(i * 70, 350) });
            });
        });

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var item = cola.find(function (c) { return c.el === entry.target; });
                if (item) {
                    entry.target.style.transitionDelay = item.delay + 'ms';
                    entry.target.classList.add('reveal-activo');
                }
                observer.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

        cola.forEach(function (item) {
            item.el.classList.add('reveal');
            observer.observe(item.el);
        });
    }

    /* --- Parallax estelar + raíl de progreso --- */
    var parallaxEls = contenido.querySelectorAll('[data-parallax]');
    var rail = document.getElementById('dossier-progress');
    var railFill = rail ? rail.querySelector('.dossier-fill') : null;
    var ticking = false;

    function onScroll() {
        var max = document.documentElement.scrollHeight - window.innerHeight;
        var p = max > 0 ? (window.scrollY / max) : 0;

        if (railFill) railFill.style.transform = 'scaleY(' + p + ')';
        if (rail) rail.classList.toggle('visible', p > 0.03);

        if (!reduced) {
            var vh = window.innerHeight;
            parallaxEls.forEach(function (el) {
                var r = el.getBoundingClientRect();
                var rel = r.top + r.height / 2 - vh / 2;
                var py = Math.max(-12, Math.min(12, -rel * 0.03));
                el.style.setProperty('--py', py.toFixed(2) + 'px');
            });
        }
        ticking = false;
    }

    if (railFill || parallaxEls.length) {
        window.addEventListener('scroll', function () {
            if (!ticking) {
                ticking = true;
                window.requestAnimationFrame(onScroll);
            }
        }, { passive: true });
        onScroll();
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function escHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function totalVotos(opts) {
        return opts.reduce(function (sum, o) { return sum + (o.votos || 0); }, 0);
    }

    function renderOpcionesReveladas(opts, correcta, eleccion) {
        var total = totalVotos(opts) || 1;
        var html = '';
        opts.forEach(function (o, i) {
            var pct = Math.round(((o.votos || 0) / total) * 100);
            var esCorrecta = (i === correcta);
            var esElegida = (i === eleccion);
            var claseFondo = 'bg-slate-100 dark:bg-slate-800';
            var claseBarra = 'bg-brand-500';
            var marca = '';
            if (esCorrecta) {
                claseFondo = 'bg-green-50 dark:bg-green-950/50 border-green-300 dark:border-green-800';
                claseBarra = 'bg-green-500';
                marca = '<span class="text-xs font-bold text-green-600 dark:text-green-400 ml-2 shrink-0">Correcta ✓</span>';
            } else if (esElegida) {
                claseFondo = 'bg-red-50 dark:bg-red-950/50 border-red-300 dark:border-red-800';
                claseBarra = 'bg-red-500';
                marca = '<span class="text-xs font-bold text-red-600 dark:text-red-400 ml-2 shrink-0">Tu elección</span>';
            }
            html +=
                '<div class="mb-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 ' + claseFondo + '">' +
                    '<div class="flex items-center justify-between text-sm mb-1.5">' +
                        '<span class="font-medium text-slate-700 dark:text-slate-200 leading-snug">' + escHtml(o.texto) + '</span>' +
                        marca +
                        '<span class="text-xs font-bold text-slate-600 dark:text-slate-400 ml-2 shrink-0">' + pct + '%</span>' +
                    '</div>' +
                    '<div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">' +
                        '<div class="h-full ' + claseBarra + ' rounded-full transition-all duration-700" style="width:' + pct + '%"></div>' +
                    '</div>' +
                '</div>';
        });
        return html;
    }

    function renderResultados(opts) {
        var total = totalVotos(opts) || 1;
        var html = '';
        opts.forEach(function (o) {
            var pct = Math.round(((o.votos || 0) / total) * 100);
            html +=
                '<div class="mb-3">' +
                    '<div class="flex items-center justify-between text-sm mb-1.5">' +
                        '<span class="font-medium text-slate-700 dark:text-slate-200 leading-snug">' + escHtml(o.texto) + '</span>' +
                        '<span class="text-xs font-bold text-brand-600 dark:text-brand-400 ml-2 shrink-0">' + pct + '%</span>' +
                    '</div>' +
                    '<div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">' +
                        '<div class="h-full bg-brand-500 rounded-full transition-all duration-700" style="width:' + pct + '%"></div>' +
                    '</div>' +
                '</div>';
        });
        return html;
    }

    function initJuego(cfg) {
        var widget = document.getElementById(cfg.widgetId);
        if (!widget) return;

        var token = widget.dataset.token || '';
        var dataEl = document.getElementById(cfg.dataId);
        var data = dataEl ? (JSON.parse(dataEl.textContent || 'null') || {}) : {};
        var opciones = data.opciones || [];
        var KEY = cfg.key;
        var cont = document.getElementById(cfg.opcionesId);
        var meta = document.getElementById(cfg.metaId);

        if (!cont || !opciones.length) return;

        function actualizarMeta(total) {
            if (meta) meta.textContent = total + ' voto' + (total === 1 ? '' : 's');
        }

        cont.addEventListener('click', function (e) {
            var btn = e.target.closest('.' + cfg.btnClass);
            if (!btn || localStorage.getItem(KEY)) return;

            var indice = parseInt(btn.dataset.opcion, 10);
            if (isNaN(indice)) return;

            cont.querySelectorAll('.' + cfg.btnClass).forEach(function (b) { b.disabled = true; });

            var fd = new FormData();
            fd.append('_token', token);
            fd.append('opcion', indice);

            fetch(cfg.url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: fd
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data.ok) {
                    localStorage.setItem(KEY, '1');
                    if (cfg.reveal) {
                        cont.innerHTML = renderOpcionesReveladas(data.opciones, data.respuesta_correcta, indice);
                        if (meta) meta.textContent = (data.total_votos + ' voto' + (data.total_votos === 1 ? '' : 's')) +
                            ' · Respuesta correcta: ' + (data.opciones[data.respuesta_correcta] ? data.opciones[data.respuesta_correcta].texto : '');
                    } else {
                        cont.innerHTML = renderResultados(data.opciones);
                        actualizarMeta(data.total_votos);
                    }
                } else {
                    cont.querySelectorAll('.' + cfg.btnClass).forEach(function (b) { b.disabled = false; });
                }
            }).catch(function () {
                cont.querySelectorAll('.' + cfg.btnClass).forEach(function (b) { b.disabled = false; });
            });
        });

        if (localStorage.getItem(KEY)) {
            if (cfg.reveal) {
                cont.innerHTML = renderOpcionesReveladas(opciones, data.respuesta_correcta, -1);
                if (meta) meta.textContent = (data.total_votos + ' voto' + (data.total_votos === 1 ? '' : 's')) +
                    ' · Respuesta correcta: ' + (opciones[data.respuesta_correcta] ? opciones[data.respuesta_correcta].texto : '');
            } else {
                cont.innerHTML = renderResultados(opciones);
                actualizarMeta(data.total_votos);
            }
        }
    }

    initJuego({
        widgetId: 'encuesta-widget',
        opcionesId: 'encuesta-opciones',
        metaId: 'encuesta-meta',
        dataId: 'encuesta-data',
        btnClass: 'encuesta-btn',
        key: 'alphalatam_encuesta_votada',
        url: '<?= esc(url('/encuesta/votar')) ?>',
        reveal: false
    });

    initJuego({
        widgetId: 'reto-widget',
        opcionesId: 'reto-opciones',
        metaId: 'reto-meta',
        dataId: 'reto-data',
        btnClass: 'reto-btn',
        key: 'alphalatam_reto_votada',
        url: '<?= esc(url('/reto/votar')) ?>',
        reveal: true
    });
});
</script>

<?php
$content = ob_get_clean();

\App\Core\View::renderLayout('public', $content, [
    'pageTitle' => 'AlphaLatam — Noticias, Opinión e Investigaciones',
    'metaDescription' => 'Noticias, artículos de opinión e investigaciones sobre corrupción. Información veraz y datos abiertos.',
]);
