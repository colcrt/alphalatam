<?php
declare(strict_types=1);

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

<!-- Search Header -->
<section class="block-navy border-b border-brand-900 dark:border-brand-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-extrabold text-white mb-6">Buscar</h1>
        <form action="<?= esc(route('buscador.index')) ?>" method="GET" class="relative">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none"></i>
            <input type="search" name="q"
                   value="<?= esc($termino) ?>"
                   placeholder="Buscar artículos, noticias, opiniones..."
                   class="w-full pl-12 pr-24 py-4 text-base bg-white dark:bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-300 focus:border-brand-300 shadow-lg shadow-brand-950/20 transition-all"
                   autofocus>
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-5 py-2.5 bg-brand-900 hover:bg-brand-800 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-950/30 transition-all">
                Buscar
            </button>
        </form>

        <?php if ($termino !== ''): ?>
        <p class="text-sm text-brand-200 mt-3">
            Se encontraron <strong class="text-white"><?= $total ?></strong> resultado(s) para "<strong class="text-white"><?= esc($termino) ?></strong>"
        </p>
        <?php endif; ?>
    </div>
</section>

<!-- Results -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (!empty($termino) && !empty($resultados)): ?>
    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <i data-lucide="file-text" class="w-5 h-5 text-slate-400"></i>
        Artículos
        <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full"><?= count($resultados) ?></span>
    </h2>

    <div class="space-y-3">
        <?php foreach ($resultados as $item): ?>
        <?php
            $tipo = $item['tipo'] ?? '';
            $badge = $tipoBadgeMap[$tipo] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
            $label = $tipoLabelMap[$tipo] ?? ucfirst($tipo);
        ?>
        <a href="<?= esc($item['url']) ?>" class="group flex items-center gap-4 p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-300 dark:hover:border-brand-600 hover:shadow-card transition-all duration-200">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider <?= $badge ?>"><?= esc($label) ?></span>
                </div>
                <span class="block text-sm font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors truncate">
                    <?= esc($item['titulo']) ?>
                </span>
            </div>
            <i data-lucide="chevron-right" class="w-5 h-5 text-slate-300 dark:text-slate-600 shrink-0 group-hover:text-brand-500 transition-colors"></i>
        </a>
        <?php endforeach; ?>
    </div>

    <?php elseif (!empty($termino)): ?>
    <div class="text-center py-16">
        <i data-lucide="search" class="w-14 h-14 text-slate-300 dark:text-slate-600 mx-auto mb-4"></i>
        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">No se encontraron resultados</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Intenta con otros términos de búsqueda.</p>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();

\App\Core\View::renderLayout('public', $content, [
    'pageTitle' => $termino !== '' ? "Buscar: {$termino}" : 'Buscar',
    'metaDescription' => 'Buscador de artículos sobre corrupción.',
    'breadcrumbs' => [
        ['nombre' => 'Inicio', 'url' => url('/')],
        ['nombre' => 'Buscar', 'url' => null],
    ],
]);
