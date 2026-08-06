<?php
declare(strict_types=1);

$_bc_items = $breadcrumbs ?? [];
if (empty($_bc_items)) return;
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="flex items-center gap-1.5 text-sm">
        <?php foreach ($_bc_items as $_i => $_bc): ?>
            <?php if ($_bc['url'] !== null && $_i < count($_bc_items) - 1): ?>
                <li class="flex items-center gap-1.5">
                    <a href="<?= esc($_bc['url']) ?>" class="text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition-colors"><?= esc($_bc['nombre']) ?></a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600"></i>
                </li>
            <?php else: ?>
                <li class="text-slate-500 dark:text-slate-400 font-medium" aria-current="page"><?= esc($_bc['nombre']) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>

<?php
$_bc_json_ld = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [],
];
$_pos = 1;
foreach ($_bc_items as $_bc):
    $_entry = ['@type' => 'ListItem', 'position' => $_pos, 'name' => $_bc['nombre']];
    if ($_bc['url'] !== null) $_entry['item'] = $_bc['url'];
    $_bc_json_ld['itemListElement'][] = $_entry;
    $_pos++;
endforeach;
?>
<?= json_ld($_bc_json_ld) ?>
