<?php
$title = 'Admin - ' . ucfirst($module ?? '');
ob_start();
?>
<div class="flex min-h-screen">
    <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

    <!-- Main content -->
    <div class="flex-1 min-w-0 lg:ml-64">
        <header class="sticky top-0 z-20 flex items-center gap-4 px-4 sm:px-6 py-3 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Menú">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <span class="text-sm text-slate-600 dark:text-slate-300 ml-auto">Bienvenido, <strong><?= esc($user->name ?? 'Admin') ?></strong></span>
            <a href="<?= url('/') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-brand-300 dark:hover:border-brand-600 transition-colors">
                <i data-lucide="home" class="w-4 h-4"></i>
                Volver al sitio
            </a>
        </header>

        <!-- Vue app root -->
        <div class="p-4 sm:p-6" id="app" data-module="<?= esc($module ?? '') ?>" data-role="<?= esc($user->role ?? '') ?>">

            <!-- Flash message -->
            <div v-show="message.text"
                 :class="{
                    'bg-green-50 dark:bg-green-950 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200': message.type === 'success',
                    'bg-red-50 dark:bg-red-950 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200': message.type === 'danger',
                    'bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200': message.type === 'warning',
                    'bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200': message.type === 'info',
                 }"
                 class="flex items-center gap-3 p-4 mb-4 border rounded-xl transition-all" role="alert">
                <span class="text-sm font-medium flex-1">{{ message.text }}</span>
                <button @click="message.text = ''" class="p-1 rounded-lg hover:bg-white/50 dark:hover:bg-slate-800/50 transition-colors" aria-label="Cerrar"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <!-- Loading -->
            <div v-cloak v-show="loading" class="flex items-center justify-center py-20">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-10 h-10 border-4 border-brand-200 dark:border-brand-800 border-t-brand-600 rounded-full animate-spin"></div>
                    <span class="text-sm text-slate-500 dark:text-slate-400">Cargando...</span>
                </div>
            </div>
            <noscript>
                <div class="flex items-center justify-center py-20">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Este panel necesita JavaScript. Actívalo para gestionar el contenido.</p>
                </div>
            </noscript>

            <!-- ===== LIST VIEW ===== -->
            <div v-show="!loading && view === 'list'" v-cloak>
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ moduleName }}</h1>
                    <button v-if="!esSoloLectura" @click="createItem" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/25 transition-all">
                        <i data-lucide="plus" class="w-4 h-4"></i> Nuevo {{ singularName }}
                    </button>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 mb-4 shadow-sm">
                    <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                        <div class="flex-1 min-w-0">
                            <input type="text" placeholder="Buscar..." v-model="search"
                                   class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                        </div>
                        <div v-if="config && config.filters.includes('status')" class="sm:w-48">
                            <select v-model="statusFilter" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                            </select>
                        </div>
                        <div v-if="extraFilterLabel" class="sm:w-48">
                            <select v-model="extraFilter" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                <option value="">{{ extraFilterLabel }}: Todos</option>
                                <option v-for="o in extraFilterOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
                            </select>
                        </div>
                        <div class="text-sm text-slate-500 dark:text-slate-400 self-center">{{ pagination.total }} registro(s)</div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                                    <th v-for="col in displayColumns" :key="col.key" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ col.label }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-40">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-if="items.length === 0">
                                    <td :colspan="displayColumns.length + 1" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No hay registros.</td>
                                </tr>
                                <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td v-for="col in displayColumns" :key="col.key" class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                        <template v-if="col.key === 'status'">
                                            <span :class="statusBadgeClass(item[col.key])" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold">{{ statusLabel(item[col.key]) }}</span>
                                            <span v-if="item.delete_requested_at" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300 ml-1" title="Solicitud de borrado pendiente de moderación">
                                                <i data-lucide="hourglass" class="w-3 h-3"></i> Borrado solicitado
                                            </span>
                                        </template>
                                        <template v-else-if="col.key === 'tipo'">
                                            {{ tipoLabel(item[col.key]) }}
                                        </template>
                                        <template v-else-if="col.key === 'destacado'">
                                            <span v-if="item[col.key]" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300">
                                                <i data-lucide="star" class="w-3 h-3"></i> Principal
                                            </span>
                                            <span v-else class="text-slate-400 dark:text-slate-600">—</span>
                                        </template>
                                        <template v-else>
                                            {{ truncate(getCellValue(item, col.key), 60) }}
                                        </template>
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-1">
                                        <button v-if="!esSoloLectura && config && config.hasPublish && item.status !== 'publicado'" @click="publishItem(item)" title="Publicar"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-950 transition-colors">
                                            <i data-lucide="globe" class="w-4 h-4"></i>
                                        </button>
                                        <button v-if="!esSoloLectura" @click="editItem(item)" title="Editar"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950 transition-colors">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <button v-if="!esSoloLectura" @click="confirmDelete(item)" title="Eliminar"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4">
                    <span class="text-sm text-slate-500 dark:text-slate-400">
                        Mostrando {{ pagination.total > 0 ? ((pagination.current_page - 1) * pagination.per_page + 1) : 0 }}
                        - {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
                        de {{ pagination.total }}
                    </span>
                    <div class="flex items-center gap-1">
                        <button @click="fetchItems(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                                class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:pointer-events-none transition-colors">&laquo;</button>
                        <button v-for="p in pageNumbers" :key="p" @click="fetchItems(p)"
                                :class="p === pagination.current_page ? 'bg-brand-600 text-white border-brand-600 shadow-md shadow-brand-600/25' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'"
                                class="inline-flex items-center justify-center w-9 h-9 text-sm rounded-lg border font-medium transition-colors">{{ p }}</button>
                        <button @click="fetchItems(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page"
                                class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:pointer-events-none transition-colors">&raquo;</button>
                    </div>
                </div>
            </div>

            <!-- ===== FORM VIEW ===== -->
            <div v-show="!loading && view === 'form'" v-cloak>
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ editingId ? 'Editar' : 'Crear' }} {{ singularName }}</h1>
                    <button @click="cancelForm" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver
                    </button>
                </div>

                <!-- Tabs -->
                <div>
                    <div v-show="availableTabs.length > 1" class="flex gap-0 border-b border-slate-200 dark:border-slate-800 mb-5">
                        <template v-for="t in availableTabs" :key="t">
                            <button @click="tab = t"
                                    :class="tab === t ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400 font-semibold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300'"
                                    class="px-5 py-3 text-sm font-medium border-b-2 transition-colors -mb-px">{{ tabLabel(t) }}</button>
                        </template>
                    </div>

                    <!-- Form card -->
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                        <!-- General fields -->
                        <div v-show="tab === 'general'">
                            <div class="flex items-center gap-2 mb-3">
                                <div id="quill-editor-outer" class="flex-1">
                                    <div id="quill-editor-contenido" class="mb-4"></div>
                                </div>
                            </div>
                            <button @click="openEmbedModal" type="button"
                                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-brand-50 dark:hover:bg-brand-950 hover:border-brand-300 dark:hover:border-brand-700 hover:text-brand-600 dark:hover:text-brand-400 transition-colors mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                Insertar contenido embebido
                            </button>
                            <template v-for="f in generalFields" :key="f.key">
                                <div class="mb-4" v-show="!f.wysiwyg">
                                    <label v-if="f.type !== 'checkbox'" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                        {{ f.label }} <span v-show="f.required" class="text-red-500">*</span>
                                    </label>
                                    <input v-if="f.type === 'text' || f.type === 'number'" :type="f.type"
                                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all"
                                           :class="errors[f.key] ? 'border-red-500' : 'border-slate-200 dark:border-slate-700'"
                                           v-model="formData[f.key]" :required="f.required">
                                    <input v-else-if="f.type === 'file'" :id="'file-input-' + f.key" type="file"
                                           :accept="f.accept || 'image/*'"
                                           @change="handleFileSelect($event, f.key)"
                                           class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 dark:file:bg-brand-950 dark:file:text-brand-300 hover:file:bg-brand-100">
                                    <div v-if="f.type === 'file' && imagenPreview && f.key === 'imagen_destacada'" class="mt-2">
                                        <img :src="imagenPreview" alt="Preview" class="w-full h-32 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
                                    </div>
                                    <div v-else-if="f.type === 'file' && mediaPreview && f.key === 'media_destacada'" class="mt-2">
                                        <video v-if="mediaPreviewTipo === 'video'" :src="mediaPreview" controls muted loop playsinline class="w-full h-32 object-cover rounded-lg border border-slate-200 dark:border-slate-700"></video>
                                        <img v-else :src="mediaPreview" alt="Preview" class="w-full h-32 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
                                    </div>
                                    <textarea v-else-if="f.type === 'textarea' && !f.wysiwyg"
                                              class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all min-h-[100px]"
                                              :class="errors[f.key] ? 'border-red-500' : 'border-slate-200 dark:border-slate-700'"
                                              v-model="formData[f.key]" rows="4" :required="f.required"></textarea>
                                    <select v-else-if="f.type === 'select'"
                                            class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all"
                                            :class="errors[f.key] ? 'border-red-500' : 'border-slate-200 dark:border-slate-700'"
                                            v-model="formData[f.key]">
                                        <option value="">Seleccionar...</option>
                                        <option v-for="opt in getFieldOptions(f)" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <label v-else-if="f.type === 'checkbox'" class="flex items-center gap-3 pt-1 cursor-pointer select-none">
                                        <input type="checkbox" :checked="!!formData[f.key]"
                                               @change="formData[f.key] = $event.target.checked"
                                               class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-brand-600 focus:ring-2 focus:ring-brand-500">
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                            {{ f.label }}
                                            <span v-if="f.hint" class="block text-xs text-slate-400 dark:text-slate-500 font-normal">{{ f.hint }}</span>
                                        </span>
                                    </label>
                                    <p v-if="f.hint && f.type !== 'checkbox'" class="text-xs text-slate-400 dark:text-slate-500 mt-1.5">{{ f.hint }}</p>
                                    <p v-show="errors[f.key]" class="text-sm text-red-500 mt-1">{{ errors[f.key] }}</p>
                                </div>
                            </template>
                        </div>

                        <!-- SEO fields -->
                        <div v-show="tab === 'seo'">
                            <template v-for="f in seoFields" :key="f.key">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">{{ f.label }}</label>
                                    <input v-if="f.type === 'text'" type="text"
                                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all"
                                           :class="errors[f.key] ? 'border-red-500' : 'border-slate-200 dark:border-slate-700'"
                                           v-model="formData[f.key]">
                                    <textarea v-else-if="f.type === 'textarea'"
                                              class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all min-h-[80px]"
                                              :class="errors[f.key] ? 'border-red-500' : 'border-slate-200 dark:border-slate-700'"
                                              v-model="formData[f.key]" rows="3"></textarea>
                                    <p v-show="errors[f.key]" class="text-sm text-red-500 mt-1">{{ errors[f.key] }}</p>
                                </div>
                            </template>
                            <p v-show="seoFields.length === 0" class="text-sm text-slate-500 dark:text-slate-400">No hay campos SEO para este módulo.</p>
                        </div>

                        <!-- Fuentes fields -->
                        <div v-show="tab === 'fuentes'">
                            <template v-for="f in fuentesFields" :key="f.key">
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                        {{ f.label }} <span v-show="f.required" class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all min-h-[180px]"
                                        :class="errors[f.key] ? 'border-red-500' : 'border-slate-200 dark:border-slate-700'"
                                        v-model="formData[f.key]" rows="8"></textarea>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Pega aquí las fuentes, enlaces o referencias del artículo para consultarlas después.</p>
                                    <p v-show="errors[f.key]" class="text-sm text-red-500 mt-1">{{ errors[f.key] }}</p>
                                </div>
                            </template>
                            <p v-show="fuentesFields.length === 0" class="text-sm text-slate-500 dark:text-slate-400">No hay campos de fuentes para este módulo.</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 mt-5">
                        <button @click="saveItem" :disabled="saving"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-brand-600/25 transition-all disabled:opacity-50 disabled:pointer-events-none">
                            <div v-show="saving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            {{ saving ? 'Guardando...' : (editingId ? 'Actualizar' : 'Crear') }}
                        </button>
                        <button @click="cancelForm" class="px-5 py-2.5 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancelar</button>
                    </div>
                </div>
            </div>

            <!-- ===== DELETE MODAL ===== -->
            <div v-show="deleteTarget"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity">
                <div class="absolute inset-0 bg-black/50" @click="cancelDelete"></div>
                <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-700 transition-transform">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ deleteModalTitle }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">{{ deleteModalBody }}</p>
                    <div class="flex justify-end gap-3">
                        <button @click="cancelDelete" class="px-4 py-2 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancelar</button>
                        <button @click="executeDelete" :disabled="deleteSaving"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl shadow-md transition-all disabled:opacity-50">
                            <div v-show="deleteSaving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            {{ deleteSaving ? 'Procesando...' : deleteConfirmLabel }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===== EMBED MODAL ===== -->
            <div v-show="embedModalOpen"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity">
                <div class="absolute inset-0 bg-black/50" @click="closeEmbedModal"></div>
                <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-slate-200 dark:border-slate-700 transition-transform">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="film" class="w-5 h-5 text-brand-600"></i>
                            Insertar contenido embebido
                        </h3>
                        <button @click="closeEmbedModal" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Cerrar">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Pega la URL del contenido que deseas embeber. Soporta YouTube, Vimeo, Twitter/X, TikTok, Instagram, Facebook, Spotify, SoundCloud y más.</p>

                    <div class="flex gap-2 mb-4">
                        <input type="url" v-model="embedUrl" placeholder="https://www.youtube.com/watch?v=..."
                               @keydown="$event.key === 'Enter' && resolveEmbed()"
                               class="flex-1 px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                        <button @click="resolveEmbed" :disabled="embedLoading || !(embedUrl || '').trim()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shadow-md transition-all disabled:opacity-50 disabled:pointer-events-none shrink-0">
                            <div v-show="embedLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            {{ embedLoading ? 'Cargando...' : 'Vista previa' }}
                        </button>
                    </div>

                    <div v-show="embedError" class="p-3 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-lg mb-4">
                        <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            {{ embedError }}
                        </p>
                    </div>

                    <div v-show="embedPreview && !embedLoading" class="mb-4">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Vista previa</label>
                        <div class="embed-preview-container rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-4 overflow-hidden" v-html="embedPreview"></div>
                        <p v-if="embedProvider" class="text-xs text-slate-400 dark:text-slate-500 mt-2">Fuente: {{ embedProvider }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button @click="closeEmbedModal" class="px-4 py-2 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancelar</button>
                        <button @click="insertEmbed" :disabled="!embedPreview"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-md transition-all disabled:opacity-50 disabled:pointer-events-none">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Insertar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
\App\Core\View::renderLayout('admin', $content, ['pageTitle' => $title, 'user' => $user, 'module' => $module]);
?>
