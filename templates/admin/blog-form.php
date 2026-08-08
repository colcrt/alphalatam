<?php
$isEdit = isset($postId) && $postId !== null;
$title = $isEdit ? 'Editar Entrada' : 'Nueva Entrada';
$module = 'blog';
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

        <?php if ($msg = flash('exito')): ?>
        <div class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 p-4 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-xl" role="alert">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i><span class="text-sm font-medium flex-1"><?= esc($msg) ?></span>
            <button onclick="this.parentElement.remove()" class="p-1 hover:bg-green-100 dark:hover:bg-green-900 rounded-lg transition-colors" aria-label="Cerrar"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
        <div class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-xl" role="alert">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i><span class="text-sm font-medium flex-1"><?= esc($msg) ?></span>
            <button onclick="this.parentElement.remove()" class="p-1 hover:bg-red-100 dark:hover:bg-red-900 rounded-lg transition-colors" aria-label="Cerrar"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <?php endif; ?>

        <div class="p-4 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white"><?= $isEdit ? 'Editar Entrada' : 'Nueva Entrada' ?></h1>
                <a href="<?= url('/admin/blog') ?>" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver
                </a>
            </div>

            <form method="POST" action="<?= $isEdit ? url('/admin/blog/' . $postId) : url('/admin/blog') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

                <!-- Tabs -->
                <div class="flex gap-0 border-b border-slate-200 dark:border-slate-800 mb-5">
                    <button type="button" onclick="showTab('general')" data-tab-btn="general"
                            class="px-5 py-3 text-sm font-medium border-b-2 transition-colors -mb-px border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400 font-semibold">General</button>
                    <button type="button" onclick="showTab('seo')" data-tab-btn="seo"
                            class="px-5 py-3 text-sm font-medium border-b-2 transition-colors -mb-px border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300">SEO</button>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                    <!-- General Tab -->
                    <div data-tab-content="general">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2 space-y-4">
                                <div>
                                    <label for="titulo" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Título <span class="text-red-500">*</span></label>
                                    <input type="text" id="titulo" name="titulo" value="<?= esc($post->titulo ?? old('titulo')) ?>" required
                                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                </div>
                                <div>
                                    <label for="slug" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Slug</label>
                                    <input type="text" id="slug" name="slug" value="<?= esc($post->slug ?? old('slug')) ?>" placeholder="Se genera automáticamente si se deja vacío"
                                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                </div>
                                <div>
                                    <label for="extracto" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Extracto</label>
                                    <textarea id="extracto" name="extracto" rows="3"
                                              class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all resize-y"><?= esc($post->extracto ?? old('extracto')) ?></textarea>
                                </div>
                                <div>
                                    <label for="contenido" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Contenido <span class="text-red-500">*</span></label>
                                    <textarea id="contenido" name="contenido" rows="12" required
                                              class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all resize-y min-h-[250px]"><?= esc($post->contenido ?? old('contenido')) ?></textarea>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label for="tipo" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tipo <span class="text-red-500">*</span></label>
                                    <select id="tipo" name="tipo" required class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                        <option value="">Seleccionar...</option>
                                        <?php $current = $post->tipo ?? old('tipo'); ?>
                                        <option value="noticia" <?= $current === 'noticia' ? 'selected' : '' ?>>Noticia</option>
                                        <option value="opinion" <?= $current === 'opinion' ? 'selected' : '' ?>>Artículo de Opinión</option>
                                        <option value="investigacion" <?= $current === 'investigacion' ? 'selected' : '' ?>>Investigación</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="categoria_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Categoría</label>
                                    <select id="categoria_id" name="categoria_id" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                        <option value="">Sin categoría...</option>
                                        <?php $currentCat = $post->categoria_id ?? old('categoria_id'); foreach ($categorias as $cat): ?>
                                        <option value="<?= (int) $cat['id'] ?>" <?= (int) $currentCat === (int) $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="imagen" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Imagen destacada</label>
                                    <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/gif,image/webp"
                                           class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 dark:file:bg-brand-950 dark:file:text-brand-300 hover:file:bg-brand-100">
                                    <?php if (!empty($post->imagen_destacada_path)): ?>
                                    <div class="mt-2"><img src="/uploads/<?= esc($post->imagen_destacada_path) ?>" alt="Imagen actual" class="w-full h-24 object-cover rounded-lg border border-slate-200 dark:border-slate-700"></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Estado</label>
                                    <select id="status" name="status" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                        <option value="borrador" <?= ($post->status ?? old('status', 'borrador')) === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                        <option value="publicado" <?= ($post->status ?? old('status')) === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                                    </select>
                                </div>
                                <?php if ($isEdit): ?>
                                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 space-y-1">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Creado: <?= date_format_es($post->created_at ?? '') ?></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Actualizado: <?= date_format_es($post->updated_at ?? '') ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Tab -->
                    <div data-tab-content="seo" style="display:none">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2 space-y-4">
                                <div>
                                    <label for="meta_title" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Meta título</label>
                                    <input type="text" id="meta_title" name="meta_title" value="<?= esc($post->meta_title ?? old('meta_title')) ?>" maxlength="60" placeholder="Máximo 60 caracteres"
                                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                    <p class="text-xs text-slate-400 mt-1">Se muestra en los resultados de búsqueda.</p>
                                </div>
                                <div>
                                    <label for="meta_description" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Meta descripción</label>
                                    <textarea id="meta_description" name="meta_description" rows="3" maxlength="160" placeholder="Máximo 160 caracteres"
                                              class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all resize-y"><?= esc($post->meta_description ?? old('meta_description')) ?></textarea>
                                </div>
                                <div>
                                    <label for="canonical" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">URL Canónica</label>
                                    <input type="url" id="canonical" name="canonical" value="<?= esc($post->canonical_url ?? old('canonical')) ?>" placeholder="https://ejemplo.com/ruta"
                                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                                </div>
                            </div>
                            <div>
                                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Vista previa en buscadores</h3>
                                    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 p-4">
                                        <div class="text-base font-semibold text-blue-700 dark:text-blue-400 truncate" id="preview-title"><?= esc($post->meta_title ?? $post->titulo ?? 'Título de la página') ?></div>
                                        <div class="text-xs text-green-700 dark:text-green-400 mt-1 truncate">https://ejemplo.com/blog/<?= esc($post->slug ?? 'slug') ?></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 truncate" id="preview-description"><?= esc($post->meta_description ?? $post->extracto ?? 'Descripción de la página') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 mt-5">
                    <a href="<?= url('/admin/blog') ?>" class="px-5 py-2.5 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancelar</a>
                    <?php if ($isEdit): ?>
                    <a href="<?= url('/blog/post/' . ($post->slug ?? '')) ?>" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <i data-lucide="eye" class="w-4 h-4"></i> Ver
                    </a>
                    <?php endif; ?>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/25 transition-all">
                        <i data-lucide="check" class="w-4 h-4"></i> <?= $isEdit ? 'Actualizar' : 'Crear' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tituloInput = document.getElementById('titulo');
    const slugInput = document.getElementById('slug');
    const previewTitle = document.getElementById('preview-title');
    const previewDesc = document.getElementById('preview-description');

    if (tituloInput && slugInput && !slugInput.value) {
        tituloInput.addEventListener('input', function() {
            const slug = this.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').trim();
            slugInput.value = slug;
            if (previewTitle) previewTitle.textContent = this.value || 'Título de la página';
        });
    }
    document.getElementById('meta_title')?.addEventListener('input', function() {
        if (previewTitle) previewTitle.textContent = this.value || tituloInput?.value || 'Título de la página';
    });
    document.getElementById('meta_description')?.addEventListener('input', function() {
        if (previewDesc) previewDesc.textContent = this.value || 'Descripción de la página';
    });
});
</script>
<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('admin', $content, ['pageTitle' => $title, 'user' => $user, 'module' => 'blog']);
