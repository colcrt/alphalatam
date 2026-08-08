<?php
$title = $editMode ? 'Editar Categoría' : 'Nueva Categoría';
$module = 'categoria';
ob_start();
?>
<div class="flex min-h-screen">
    <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>
    <div class="flex-1 min-w-0 lg:ml-64">
        <header class="sticky top-0 z-20 flex items-center gap-4 px-4 sm:px-6 py-3 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Menú"><i data-lucide="menu" class="w-5 h-5"></i></button>
            <span class="text-sm text-slate-600 dark:text-slate-300 ml-auto">Bienvenido, <strong><?= esc($user->name ?? 'Admin') ?></strong></span>
            <a href="<?= url('/') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-brand-300 dark:hover:border-brand-600 transition-colors">
                <i data-lucide="home" class="w-4 h-4"></i>
                Volver al sitio
            </a>
        </header>
        <?php if ($msg = flash('exito')): ?><div class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 p-4 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-xl" role="alert"><i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i><span class="text-sm font-medium flex-1"><?= esc($msg) ?></span><button onclick="this.parentElement.remove()" class="p-1 hover:bg-green-100 dark:hover:bg-green-900 rounded-lg transition-colors" aria-label="Cerrar"><i data-lucide="x" class="w-4 h-4"></i></button></div><?php endif; ?>
        <?php if ($msg = flash('error')): ?><div class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-xl" role="alert"><i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i><span class="text-sm font-medium flex-1"><?= esc($msg) ?></span><button onclick="this.parentElement.remove()" class="p-1 hover:bg-red-100 dark:hover:bg-red-900 rounded-lg transition-colors" aria-label="Cerrar"><i data-lucide="x" class="w-4 h-4"></i></button></div><?php endif; ?>

        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white"><?= $editMode ? 'Editar Categoría' : 'Nueva Categoría' ?></h1>
                <a href="<?= url('/admin/categorias') ?>" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"><i data-lucide="arrow-left" class="w-4 h-4"></i> Volver</a>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <form method="POST" action="<?= $editMode ? url('/admin/categorias/' . $categoria['id']) : url('/admin/categorias') ?>">
                    <?= csrf_field() ?>
                    <?php if ($editMode): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" id="nombre" name="nombre" value="<?= esc($categoria['nombre'] ?? old('nombre')) ?>" required class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                        </div>
                        <div>
                            <label for="slug" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Slug</label>
                            <input type="text" id="slug" name="slug" value="<?= esc($categoria['slug'] ?? old('slug')) ?>" placeholder="Se genera automáticamente si se deja vacío" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label for="descripcion" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="4" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all resize-y"><?= esc($categoria['descripcion'] ?? old('descripcion')) ?></textarea>
                        </div>
                    </div>
                    <hr class="my-6 border-slate-200 dark:border-slate-700">
                    <div class="flex justify-end gap-3">
                        <a href="<?= url('/admin/categorias') ?>" class="px-5 py-2.5 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancelar</a>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/25 transition-all"><i data-lucide="check" class="w-4 h-4"></i> <?= $editMode ? 'Actualizar' : 'Crear' ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const n = document.getElementById('nombre'), s = document.getElementById('slug');
    if (n && s && !s.value) n.addEventListener('input', function() { s.value = this.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').trim(); });
});
</script>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('admin', $content, ['pageTitle' => $title, 'module' => $module]);
