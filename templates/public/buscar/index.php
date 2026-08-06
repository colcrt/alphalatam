<?php
declare(strict_types=1);

$tipoBadgeMap = [
    'noticia' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
    'opinion' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
    'investigacion' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
];

$tipoLabelMap = [
    'noticia' => 'Noticia',
    'opinion' => 'Opinión',
    'investigacion' => 'Investigación',
];

ob_start();
?>

<!-- Search Header -->
<section class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Buscar</h1>
        <form action="<?= esc(route('buscador.index')) ?>" method="GET" class="relative">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none"></i>
            <input type="search" name="q"
                   value="<?= esc($termino) ?>"
                   placeholder="Buscar artículos, noticias, opiniones..."
                   class="w-full pl-12 pr-24 py-4 text-base bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 shadow-sm transition-all"
                   autofocus>
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/25 transition-all">
                Buscar
            </button>
        </form>

        <?php if ($termino !== ''): ?>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-3">
            Se encontraron <strong class="text-slate-700 dark:text-slate-200"><?= $total ?></strong> resultado(s) para "<strong class="text-slate-700 dark:text-slate-200"><?= esc($termino) ?></strong>"
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
        <a href="<?= esc($item['url']) ?>" class="group flex items-center gap-4 p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-300 dark:hover:border-brand-600 hover:shadow-md transition-all duration-200">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?= $badge ?>"><?= esc($label) ?></span>
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
