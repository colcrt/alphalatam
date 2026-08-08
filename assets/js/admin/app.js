const { createApp, ref, reactive, computed, onMounted, watch, nextTick } = Vue;

// --- Custom Quill Embed Blot for oEmbed content ---
(function registerEmbedBlot() {
    if (typeof Quill === 'undefined') return;
    const Embed = Quill.import('blots/embed');
    class EmbedContentBlot extends Embed {
        static create(value) {
            const node = super.create();
            node.setAttribute('data-embed', 'true');
            node.setAttribute('data-embed-content', value || '');
            node.setAttribute('contenteditable', 'false');
            node.innerHTML = value || '';
            return node;
        }
        static formats(node) {
            return node.getAttribute('data-embed-content');
        }
        html() {
            return this.domNode.getAttribute('data-embed-content') || this.domNode.innerHTML;
        }
    }
    EmbedContentBlot.blotName = 'embed-content';
    EmbedContentBlot.tagName = 'DIV';
    EmbedContentBlot.className = 'embed-wrapper';
    Quill.register(EmbedContentBlot);
})();

const app = createApp({
    setup() {
        const module = document.getElementById('app')?.dataset.module || '';
        const userRole = document.getElementById('app')?.dataset.role || '';
        const loading = ref(true);
        const view = ref('list');
        const items = ref([]);
        const pagination = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 20 });
        const search = ref('');
        const statusFilter = ref('');
        const editingId = ref(null);
        const formData = reactive({});
        const errors = reactive({});
        const tab = ref('general');
        const saving = ref(false);
        const message = reactive({ text: '', type: '' });
        const deleteTarget = ref(null);
        const deleteSaving = ref(false);
        const categoriaOptions = ref([]);
        const imagenPreview = ref('');
        const mediaPreview = ref('');
        const mediaPreviewTipo = ref('');
        let quillEditor = null;

        // Embed modal state
        const embedModalOpen = ref(false);
        const embedUrl = ref('');
        const embedLoading = ref(false);
        const embedPreview = ref('');
        const embedError = ref('');
        const embedProvider = ref('');
        const hasWysiwyg = computed(() => fields.value.some(f => f.wysiwyg && f.tab === (tab.value || 'general')));
        const hasFileFields = computed(() => fields.value.some(f => f.type === 'file'));

        const moduleConfig = {
            blog: {
                name: 'Artículos', endpoint: '/admin/api/blog', singular: 'Artículo',
                fields: [
                    { key: 'titulo', label: 'Título', type: 'text', required: true, tab: 'general' },
                    { key: 'slug', label: 'Slug', type: 'text', tab: 'general' },
                    { key: 'extracto', label: 'Extracto', type: 'textarea', tab: 'general' },
                    { key: 'contenido', label: 'Contenido', type: 'textarea', tab: 'general', wysiwyg: true },
                    { key: 'imagen_destacada', label: 'Imagen destacada (card)', type: 'file', tab: 'general', accept: 'image/jpeg,image/png,image/gif,image/webp' },
                    { key: 'media_destacada', label: 'Animación / video en tarjeta (opcional)', type: 'file', tab: 'general', accept: 'image/gif,video/mp4,video/webm', hint: 'GIF animado, MP4 (H.264) o WebM que se reproduce en la tarjeta del artículo. Casos excepcionales. Máximo 8MB.' },
                    { key: 'card_embed_url', label: 'Embed en tarjeta (URL Instagram/Twitter...)', type: 'text', tab: 'general', hint: 'Si se define, la tarjeta reproduce el embed (Instagram, Twitter, YouTube, etc.) en lugar de la imagen/media. Se resuelve al guardar.' },
                    { key: 'tipo', label: 'Tipo', type: 'select', options: [
                        { value: 'noticia', label: 'Noticia' },
                        { value: 'opinion', label: 'Artículo de Opinión' },
                        { value: 'investigacion', label: 'Investigación' }
                    ], required: true, tab: 'general' },
                    { key: 'categoria_id', label: 'Categoría', type: 'select', options: [], tab: 'general' },
                    { key: 'status', label: 'Estado', type: 'select', options: [
                        { value: 'borrador', label: 'Borrador' },
                        { value: 'publicado', label: 'Publicado' }
                    ], tab: 'general' },
                    { key: 'destacado', label: 'Nota principal', type: 'checkbox', tab: 'general', adminOnly: true, hint: 'Se muestra como nota principal en la portada. Si marcas varias, se usa la más reciente publicada.' },
                    { key: 'interes', label: 'Artículo de interés', type: 'checkbox', tab: 'general', adminOnly: true, hint: 'Se muestra en la sección "Artículos de interés" de la portada. Máximo 3: se muestran los más recientes marcados.' },
                    { key: 'meta_title', label: 'Meta Title', type: 'text', tab: 'seo' },
                    { key: 'meta_description', label: 'Meta Description', type: 'textarea', tab: 'seo' },
                    { key: 'fuentes', label: 'Fuentes', type: 'textarea', tab: 'fuentes' },
                ],
                filters: ['status', 'tipo'],
                hasPublish: true
            },
            categoria: {
                name: 'Categorías', endpoint: '/admin/api/categorias', singular: 'Categoría',
                fields: [
                    { key: 'nombre', label: 'Nombre', type: 'text', required: true, tab: 'general' },
                    { key: 'slug', label: 'Slug', type: 'text', tab: 'general' },
                    { key: 'descripcion', label: 'Descripción', type: 'textarea', tab: 'general' },
                ],
                filters: [],
                hasPublish: false
            },
            comentario: {
                name: 'Comentarios', endpoint: '/admin/api/comentarios', singular: 'Comentario',
                fields: [
                    { key: 'autor_nombre', label: 'Autor', type: 'text', tab: 'general' },
                    { key: 'autor_email', label: 'Email', type: 'text', tab: 'general' },
                    { key: 'contenido', label: 'Contenido', type: 'textarea', tab: 'general' },
                    { key: 'status', label: 'Estado', type: 'select', options: [
                        { value: 'pendiente', label: 'Pendiente' },
                        { value: 'aprobado', label: 'Aprobado' },
                        { value: 'rechazado', label: 'Rechazado' }
                    ], tab: 'general' },
                ],
                filters: ['status'],
                hasPublish: false
            },
        };

        const config = computed(() => moduleConfig[module] || null);
        const moduleName = computed(() => config.value?.name || module);
        const singularName = computed(() => config.value?.singular || 'Registro');
        const esAdmin = computed(() => userRole === 'admin');
        const puedePublicar = computed(() => ['admin', 'editor'].includes(userRole));
        const esSoloLectura = computed(() => ['revisor', 'auditor'].includes(userRole));
        const deleteModalTitle = computed(() => esAdmin.value ? 'Confirmar eliminación' : 'Solicitar borrado');
        const deleteModalBody = computed(() => esAdmin.value
            ? `¿Está seguro de eliminar este ${singularName.value}? Esta acción no se puede deshacer.`
            : `Este ${singularName.value} se enviará a moderación. Un administrador aprobará o rechazará el borrado.`);
        const deleteConfirmLabel = computed(() => esAdmin.value ? 'Eliminar' : 'Solicitar borrado');
        const fields = computed(() => (config.value?.fields || []).filter(f => !(f.adminOnly && !esAdmin.value)));
        const generalFields = computed(() => fields.value.filter(f => f.tab === 'general'));
        const seoFields = computed(() => fields.value.filter(f => f.tab === 'seo'));
        const fuentesFields = computed(() => fields.value.filter(f => f.tab === 'fuentes'));

        const availableTabs = computed(() => {
            const t = new Set(fields.value.map(f => f.tab));
            return [...t];
        });

        const statusOptions = [
            { value: '', label: 'Todos' },
            { value: 'borrador', label: 'Borrador' },
            { value: 'publicado', label: 'Publicado' },
            { value: 'pendiente', label: 'Pendiente' },
            { value: 'aprobado', label: 'Aprobado' },
            { value: 'rechazado', label: 'Rechazado' }
        ];

        const blogTipoOptions = [
            { value: '', label: 'Todos' },
            { value: 'noticia', label: 'Noticia' },
            { value: 'opinion', label: 'Opinión' },
            { value: 'investigacion', label: 'Investigación' }
        ];

        const extraFilter = ref('');
        const extraFilterLabel = computed(() => {
            if (!config.value) return '';
            const map = { tipo: 'Tipo' };
            const filters = config.value.filters || [];
            for (const f of filters) {
                if (f !== 'status' && map[f]) return map[f];
            }
            return '';
        });

        const extraFilterKey = computed(() => {
            if (!config.value) return '';
            const filters = config.value.filters || [];
            for (const f of filters) {
                if (f !== 'status') return f;
            }
            return '';
        });

        const extraFilterOptions = computed(() => {
            const key = extraFilterKey.value;
            if (key === 'tipo') return blogTipoOptions;
            return [];
        });

        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function getJsonHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            };
        }

        function buildSearchParams() {
            const params = new URLSearchParams();
            params.set('page', pagination.current_page);
            params.set('per_page', pagination.per_page);
            if (search.value) params.set('busqueda', search.value);
            if (statusFilter.value) params.set('status', statusFilter.value);
            const ek = extraFilterKey.value;
            if (ek && extraFilter.value) params.set(ek, extraFilter.value);
            return params;
        }

        function refreshLucideIcons() {
            nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        async function fetchItems(page = 1) {
            if (!config.value) {
                loading.value = false;
                return;
            }
            loading.value = true;
            pagination.current_page = page;
            const params = buildSearchParams();

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 12000);
            try {
                const resp = await fetch(`${config.value.endpoint}?${params.toString()}`, {
                    headers: getJsonHeaders(),
                    signal: controller.signal
                });
                if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                const data = await resp.json();
                items.value = data.data || data;
                if (data.meta) {
                    pagination.current_page = data.meta.current_page || 1;
                    pagination.last_page = data.meta.last_page || 1;
                    pagination.total = data.meta.total || 0;
                    pagination.per_page = data.meta.per_page || 20;
                }
            } catch (e) {
                console.error('Fetch error:', e);
                items.value = [];
                showMessage(e.name === 'AbortError' ? 'Tiempo de espera agotado al cargar los datos. Recarga la página.' : 'Error al cargar datos', 'danger');
            }
            clearTimeout(timeoutId);
            loading.value = false;
            refreshLucideIcons();
        }

        async function saveItem() {
            if (!config.value) return;
            saving.value = true;
            Object.keys(errors).forEach(k => delete errors[k]);
            syncQuillContent();

            const url = editingId.value
                ? `${config.value.endpoint}/${editingId.value}`
                : config.value.endpoint;
            const method = editingId.value ? 'PUT' : 'POST';

            let headers = getJsonHeaders();
            let body;

            if (hasFileFields.value) {
                const fd = new FormData();
                fd.append('_method', method === 'PUT' ? 'PUT' : 'POST');
                fields.value.forEach(f => {
                    if (f.type === 'file') {
                        const fileInput = document.getElementById('file-input-' + f.key);
                        if (fileInput && fileInput.files.length > 0) {
                            fd.append(f.key, fileInput.files[0]);
                        }
                    } else if (f.type === 'checkbox') {
                        fd.append(f.key, formData[f.key] ? '1' : '0');
                    } else {
                        fd.append(f.key, formData[f.key] !== undefined ? formData[f.key] : '');
                    }
                });
                headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() };
                body = fd;
            } else {
                const jsonBody = {};
                fields.value.forEach(f => {
                    if (f.type === 'file') return;
                    jsonBody[f.key] = f.type === 'checkbox'
                        ? (formData[f.key] ? 1 : 0)
                        : (formData[f.key] !== undefined ? formData[f.key] : '');
                });
                body = JSON.stringify(jsonBody);
            }

            try {
                const resp = await fetch(url, {
                    method: hasFileFields.value ? 'POST' : method,
                    headers,
                    body
                });
                const data = await resp.json();
                if (resp.ok) {
                    showMessage(editingId.value ? 'Actualizado correctamente' : 'Creado correctamente', 'success');
                    editingId.value = null;
                    resetForm();
                    view.value = 'list';
                    fetchItems(pagination.current_page);
                } else if (resp.status === 422 && data.errors) {
                    Object.keys(data.errors).forEach(k => {
                        errors[k] = Array.isArray(data.errors[k]) ? data.errors[k][0] : data.errors[k];
                    });
                    showMessage('Por favor corrija los errores del formulario', 'danger');
                } else {
                    const errDetail = data.mensaje ? `${data.error}: ${data.mensaje}` : (data.error || 'Error al guardar');
                    showMessage(errDetail, 'danger');
                }
            } catch (e) {
                console.error('Save error:', e);
                showMessage('Error de conexión al guardar', 'danger');
            }
            saving.value = false;
        }

        function confirmDelete(item) {
            deleteTarget.value = item;
        }

        function cancelDelete() {
            deleteTarget.value = null;
        }

        async function executeDelete() {
            if (!deleteTarget.value || !config.value) return;
            deleteSaving.value = true;
            try {
                const resp = await fetch(`${config.value.endpoint}/${deleteTarget.value.id}`, {
                    method: 'DELETE',
                    headers: getJsonHeaders()
                });
                const data = resp.ok ? await resp.json() : null;
                if (resp.ok) {
                    showMessage(data && data.moderacion ? 'Solicitud de borrado enviada. Un administrador la revisará.' : 'Eliminado correctamente', 'success');
                    deleteTarget.value = null;
                    fetchItems(pagination.current_page);
                } else {
                    const errDetail = data && (data.mensaje ? `${data.error}: ${data.mensaje}` : (data.error || 'Error al eliminar'));
                    showMessage(errDetail, 'danger');
                }
            } catch (e) {
                showMessage('Error al eliminar', 'danger');
            }
            deleteSaving.value = false;
        }

        async function publishItem(item) {
            if (!config.value) return;
            try {
                const resp = await fetch(`${config.value.endpoint}/${item.id}/publicar`, {
                    method: 'POST',
                    headers: getJsonHeaders()
                });
                const data = await resp.json();
                if (resp.ok) {
                    showMessage(data.message || 'Publicado correctamente', 'success');
                    fetchItems(pagination.current_page);
                } else {
                    showMessage(data.mensaje || data.error || 'Error al publicar', 'danger');
                }
            } catch (e) {
                showMessage('Error al publicar', 'danger');
            }
        }

        function editItem(item) {
            editingId.value = item.id;
            fields.value.forEach(f => {
                formData[f.key] = item[f.key] !== null && item[f.key] !== undefined ? item[f.key] : '';
            });
            imagenPreview.value = item.imagen_destacada_path ? '/uploads/' + item.imagen_destacada_path : '';
            mediaPreview.value = item.media_destacada_path ? '/uploads/' + item.media_destacada_path : '';
            mediaPreviewTipo.value = item.media_destacada_tipo === 'video' ? 'video' : (item.media_destacada_path ? 'gif' : '');
            tab.value = 'general';
            view.value = 'form';
            refreshLucideIcons();
        }

        function createItem() {
            editingId.value = null;
            resetForm();
            imagenPreview.value = '';
            tab.value = 'general';
            view.value = 'form';
            refreshLucideIcons();
        }

        function resetForm() {
            editingId.value = null;
            fields.value.forEach(f => {
                formData[f.key] = '';
            });
            Object.keys(errors).forEach(k => delete errors[k]);
            imagenPreview.value = '';
            mediaPreview.value = '';
            mediaPreviewTipo.value = '';
            tab.value = 'general';
        }

        function cancelForm() {
            destroyQuill();
            resetForm();
            view.value = 'list';
            refreshLucideIcons();
        }

        function showMessage(text, type = 'success') {
            message.text = text;
            message.type = type;
            setTimeout(() => {
                message.text = '';
                message.type = '';
            }, 4000);
        }

        function statusBadgeClass(status) {
            const map = {
                borrador: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                publicado: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                pendiente: 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
                aprobado: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                rechazado: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
            };
            return map[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        }

        function statusLabel(status) {
            const map = {
                borrador: 'Borrador',
                publicado: 'Publicado',
                pendiente: 'Pendiente',
                aprobado: 'Aprobado',
                rechazado: 'Rechazado'
            };
            return map[status] || status;
        }

        function tipoLabel(tipo) {
            const map = {
                noticia: 'Noticia',
                opinion: 'Opinión',
                investigacion: 'Investigación'
            };
            return map[tipo] || tipo;
        }

        function truncate(text, length = 80) {
            if (!text) return '';
            return text.length > length ? text.substring(0, length) + '...' : text;
        }

        function getCellValue(item, key) {
            const val = item[key];
            if (val === null || val === undefined) return '-';
            return val;
        }

        function getFieldOptions(f) {
            if (f.key === 'categoria_id') return categoriaOptions.value;
            return f.options || [];
        }

        function handleFileSelect(event, key) {
            const file = event.target.files[0];
            if (file) {
                if (key === 'imagen_destacada') {
                    imagenPreview.value = URL.createObjectURL(file);
                } else if (key === 'media_destacada') {
                    mediaPreview.value = URL.createObjectURL(file);
                    mediaPreviewTipo.value = file.type.startsWith('video/') ? 'video' : 'gif';
                }
            }
        }

        const displayColumns = computed(() => {
            if (!config.value) return [];
            const cols = [];
            const skip = new Set(['meta_title', 'meta_description', 'slug', 'contenido', 'extracto', 'descripcion', 'fuentes', 'interes', 'media_destacada', 'card_embed_url']);
            fields.value.forEach(f => {
                if (skip.has(f.key)) return;
                cols.push(f);
            });
            return cols.slice(0, 6);
        });

        const pageNumbers = computed(() => {
            const pages = [];
            const current = pagination.current_page;
            const last = pagination.last_page;
            let start = Math.max(1, current - 2);
            let end = Math.min(last, current + 2);
            if (end - start < 4) {
                if (start === 1) end = Math.min(last, start + 4);
                else start = Math.max(1, end - 4);
            }
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        });

        const fieldLabel = (key) => {
            const f = fields.value.find(f => f.key === key);
            return f ? f.label : key;
        };

        const tabLabel = (t) => {
            const labels = { general: 'General', seo: 'SEO' };
            return labels[t] || t.charAt(0).toUpperCase() + t.slice(1);
        };

        function initQuill() {
            if (quillEditor) return;
            const container = document.getElementById('quill-editor-contenido');
            if (!container) {
                console.error('Quill: contenedor #quill-editor-contenido no encontrado en el DOM');
                return;
            }
            if (typeof Quill === 'undefined') {
                console.error('Quill: la librería no se cargó desde el CDN');
                showMessage('Error: el editor enriquecido no se pudo cargar. Verifique su conexión a internet.', 'danger');
                return;
            }
            while (container.previousElementSibling) {
                container.previousElementSibling.remove();
            }
            // Clipboard matcher: preserve embed-content divs during paste/load
            const ClipboardModule = Quill.import('quill/core/clipboard');
            if (ClipboardModule && ClipboardModule.DEFAULTS && ClipboardModule.DEFAULTS.matchers) {
                ClipboardModule.DEFAULTS.matchers.unshift([
                    Node.ELEMENT_NODE,
                    (node, delta) => {
                        if (node.getAttribute && node.getAttribute('data-embed')) {
                            const html = node.getAttribute('data-embed-content') || node.innerHTML;
                            return delta.insert({ 'embed-content': html });
                        }
                        return delta;
                    }
                ]);
            }
            quillEditor = new Quill(container, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link', 'image'],
                        ['clean']
                    ]
                },
                placeholder: 'Escribe el contenido del artículo...'
            });

            if (formData.contenido) {
                quillEditor.root.innerHTML = formData.contenido;
            }
            let syncTimeout;
            quillEditor.on('text-change', () => {
                clearTimeout(syncTimeout);
                syncTimeout = setTimeout(() => {
                    formData.contenido = quillEditor.root.innerHTML;
                }, 500);
            });
        }

        function destroyQuill() {
            if (!quillEditor) return;
            quillEditor = null;
            const outer = document.getElementById('quill-editor-outer');
            if (outer) {
                outer.innerHTML = '<div id="quill-editor-contenido" class="mb-4"></div>';
            }
        }

        function syncQuillContent() {
            if (quillEditor) {
                formData.contenido = quillEditor.root.innerHTML;
            }
        }

        // --- Embed methods ---
        function openEmbedModal() {
            embedUrl.value = '';
            embedPreview.value = '';
            embedError.value = '';
            embedProvider.value = '';
            embedLoading.value = false;
            embedModalOpen.value = true;
        }

        function closeEmbedModal() {
            embedModalOpen.value = false;
            embedUrl.value = '';
            embedPreview.value = '';
            embedError.value = '';
            embedProvider.value = '';
            embedLoading.value = false;
        }

        async function resolveEmbed() {
            const url = embedUrl.value.trim();
            if (!url) {
                embedError.value = 'Ingresa una URL válida.';
                return;
            }

            embedLoading.value = true;
            embedError.value = '';
            embedPreview.value = '';
            embedProvider.value = '';

            try {
                const formDataObj = new FormData();
                formDataObj.append('url', url);

                const resp = await fetch('/admin/api/oembed/resolve', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: formDataObj
                });

                const data = await resp.json();

                if (resp.ok && data.success) {
                    embedPreview.value = data.html;
                    embedProvider.value = data.provider_name || data.provider;
                } else {
                    embedError.value = data.error || 'No se pudo resolver la URL.';
                }
            } catch (e) {
                embedError.value = 'Error de conexión. Verifica tu red.';
            }

            embedLoading.value = false;
        }

        function insertEmbed() {
            if (!embedPreview.value || !quillEditor) return;

            const range = quillEditor.getSelection(true);
            quillEditor.insertEmbed(range.index, 'embed-content', embedPreview.value);
            syncQuillContent();
            closeEmbedModal();
        }

        let searchTimeout;
        watch(search, () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => fetchItems(1), 300);
        });
        watch(statusFilter, () => fetchItems(1));
        watch(extraFilter, () => fetchItems(1));

        watch(view, (newView) => {
            if (newView === 'form' && hasWysiwyg.value) {
                if (!quillEditor) {
                    nextTick(() => initQuill());
                }
            } else if (newView === 'list') {
                syncQuillContent();
                destroyQuill();
            }
        });

        watch(tab, () => {
            syncQuillContent();
        });

        onMounted(async () => {
            if (config.value) {
                fetchItems();
            } else {
                loading.value = false;
            }
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 12000);
            try {
                const resp = await fetch('/admin/api/categorias?per_page=100', { headers: getJsonHeaders(), signal: controller.signal });
                if (resp.ok) {
                    const data = await resp.json();
                    categoriaOptions.value = (data.data || []).map(c => ({ value: c.id, label: c.nombre }));
                }
            } catch (e) {
                console.error('Error cargando categorías:', e);
            }
            clearTimeout(timeoutId);
        });

        return {
            module, loading, view, items, pagination, search, statusFilter,
            editingId, formData, errors, tab, saving, message,
            deleteTarget, deleteSaving, imagenPreview, mediaPreview, mediaPreviewTipo, hasFileFields,
            embedModalOpen, embedUrl, embedLoading, embedPreview, embedError, embedProvider,
            config, moduleName, singularName, generalFields, seoFields, fuentesFields, fields,
            availableTabs, statusOptions, displayColumns, pageNumbers,
            esAdmin, puedePublicar, esSoloLectura, deleteModalTitle, deleteModalBody, deleteConfirmLabel,
            extraFilter, extraFilterLabel, extraFilterOptions,
            fetchItems, saveItem, confirmDelete, cancelDelete, executeDelete,
            publishItem, editItem, createItem, cancelForm, resetForm,
            statusBadgeClass, statusLabel, tipoLabel, truncate,
            getCellValue, fieldLabel, tabLabel, getFieldOptions, handleFileSelect,
            openEmbedModal, closeEmbedModal, resolveEmbed, insertEmbed
        };
    }
});

const appEl = document.getElementById('app');
if (appEl) {
    app.mount(appEl);
}
