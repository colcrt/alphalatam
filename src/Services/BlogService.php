<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Core\Cache;
use App\Database\Connection;
use App\Models\BlogPost;
use App\Models\BlogCategoria;
use App\Models\BlogComentario;
use App\Models\User;

class BlogService
{
    private $repositorio;
    private SeoService $seoService;
    private HtmlSanitizer $htmlSanitizer;
    private OEmbedService $oembedService;

    public function __construct($repositorio = null, ?SeoService $seoService = null, ?HtmlSanitizer $htmlSanitizer = null, ?OEmbedService $oembedService = null)
    {
        $this->repositorio = $repositorio ?? App::make('App\\Repositories\\Contracts\\BlogRepositoryInterface');
        $this->seoService = $seoService ?? App::make(SeoService::class);
        $this->htmlSanitizer = $htmlSanitizer ?? App::make(HtmlSanitizer::class);
        $this->oembedService = $oembedService ?? App::make(OEmbedService::class);
    }

    public function paraAdmin(array $filtros = [])
    {
        return $this->repositorio->paginarParaAdmin($filtros);
    }

    // --- Categorías ---

    public function obtenerCategorias(): array
    {
        return array_map(fn($row) => (array) $row, BlogCategoria::query()->orderBy('nombre')->get());
    }

    public function obtenerCategoriaPorId(int $id): ?BlogCategoria
    {
        return BlogCategoria::find($id);
    }

    public function crearCategoria(array $datos): BlogCategoria
    {
        return BlogCategoria::create($datos);
    }

    public function actualizarCategoria(int $id, array $datos): BlogCategoria
    {
        $categoria = BlogCategoria::findOrFail($id);
        $categoria->update($datos);
        return $categoria;
    }

    public function eliminarCategoria(int $id): bool
    {
        $categoria = BlogCategoria::findOrFail($id);
        return $categoria->delete();
    }

    // --- Comentarios ---

    public function obtenerComentariosFiltrados(string $status = ''): array
    {
        $query = BlogComentario::query()->orderByDesc('created_at');
        if ($status !== '') {
            $query = $query->where('status', $status);
        }
        return array_map(fn($row) => (array) $row, $query->limit(100)->get());
    }

    public function obtenerComentariosPaginados(string $status = '', int $page = 1, int $perPage = 20): array
    {
        $query = BlogComentario::query()->orderByDesc('created_at');
        if ($status !== '') {
            $query = $query->where('status', $status);
        }
        $total = $query->count();
        $data = array_map(fn($row) => (array) $row, $query->offset(($page - 1) * $perPage)->limit($perPage)->get());
        $lastPage = (int) ceil($total / $perPage);

        return [
            'data' => $data,
            'current_page' => $page,
            'last_page' => $lastPage,
            'total' => $total,
        ];
    }

    public function cambiarEstadoComentario(int $id, string $status): void
    {
        $comentario = BlogComentario::findOrFail($id);
        $comentario->update(['status' => $status]);
    }

    public function eliminarComentario(int $id): bool
    {
        $comentario = BlogComentario::findOrFail($id);
        return $comentario->delete();
    }

    public function obtenerFichaPublica(string $slug): ?BlogPost
    {
        return Cache::remember(
            "blog:ficha:{$slug}",
            21600,
            function () use ($slug) {
                $post = $this->repositorio->buscarPorSlugPublicado($slug);
                if ($post) {
                    $post->contenido = $this->htmlSanitizer->sanitizar((string) ($post->contenido ?? ''));
                }
                return $post;
            }
        );
    }

    public function crear(array $datos): BlogPost
    {
        $datos['contenido'] = $this->htmlSanitizer->sanitizar((string) ($datos['contenido'] ?? ''));
        $datos['slug'] = $this->generarSlugUnico($datos['titulo']);
        $datos['autor_id'] = Auth::id();

        $this->seoService->autocompletar($datos, $datos['titulo'], $datos['extracto'] ?? null);

        $this->procesarArchivosAlGuardar(null, $datos);

        $post = $this->repositorio->crear($datos);

        if (!empty($datos['etiquetas'])) {
            $this->sincronizarEtiquetas($post, $datos['etiquetas']);
        }

        return $post;
    }

    public function actualizar(BlogPost $post, array $datos): BlogPost
    {
        if (array_key_exists('contenido', $datos)) {
            $datos['contenido'] = $this->htmlSanitizer->sanitizar((string) ($datos['contenido'] ?? ''));
        }

        $this->procesarArchivosAlGuardar($post, $datos);

        $post = $this->repositorio->actualizar($post, $datos);

        if (array_key_exists('etiquetas', $datos)) {
            $this->sincronizarEtiquetas($post, $datos['etiquetas'] ?? []);
        }

        Cache::forget("blog:ficha:{$post->slug}");

        return $post;
    }

    public function publicar(BlogPost $post): BlogPost
    {
        $post->update([
            'status' => 'publicado',
            'published_at' => $post->published_at ?? date('Y-m-d H:i:s'),
        ]);

        Cache::forget("blog:ficha:{$post->slug}");

        return $post;
    }

    public function eliminar(BlogPost $post): bool
    {
        Cache::forget("blog:ficha:{$post->slug}");

        return $this->repositorio->eliminar($post);
    }

    // --- Solicitudes de borrado (moderación) ---

    public function solicitarBorrado(BlogPost $post, int $usuarioId): BlogPost
    {
        $post->update([
            'delete_requested_at' => date('Y-m-d H:i:s'),
            'delete_requested_by' => $usuarioId,
        ]);

        Cache::forget("blog:ficha:{$post->slug}");

        return $post;
    }

    public function aprobarBorrado(BlogPost $post): bool
    {
        Cache::forget("blog:ficha:{$post->slug}");

        Connection::table('blog_posts')
            ->where('id', $post->id)
            ->update([
                'delete_requested_at' => null,
                'delete_requested_by' => null,
            ]);

        return $this->repositorio->eliminar($post);
    }

    public function rechazarBorrado(BlogPost $post): BlogPost
    {
        Connection::table('blog_posts')
            ->where('id', $post->id)
            ->update([
                'delete_requested_at' => null,
                'delete_requested_by' => null,
            ]);

        return $post;
    }

    public function obtenerSolicitudesBorrado(int $page = 1, int $perPage = 20): array
    {
        $pagina = BlogPost::query()
            ->whereNull('deleted_at')
            ->whereNotNull('delete_requested_at')
            ->orderByDesc('delete_requested_at')
            ->paginate($perPage, $page);

        $data = array_map(function ($row) {
            $post = BlogPost::fromRow($row);
            $post->autor = $post->autor_id ? User::find((int) $post->autor_id) : null;
            $post->solicitante = $post->delete_requested_by ? User::find((int) $post->delete_requested_by) : null;
            return $post;
        }, $pagina['data']);

        $pagina['data'] = $data;

        return $pagina;
    }

    public function contarSolicitudesBorrado(): int
    {
        return (int) BlogPost::query()
            ->whereNull('deleted_at')
            ->whereNotNull('delete_requested_at')
            ->count();
    }

    private function sincronizarEtiquetas(BlogPost $post, array $etiquetaIds): void
    {
        Connection::table('etiquetables')
            ->where('etiquetable_type', BlogPost::class)
            ->where('etiquetable_id', $post->id)
            ->delete();

        foreach ($etiquetaIds as $etiquetaId) {
            Connection::table('etiquetables')->insert([
                'etiqueta_id' => $etiquetaId,
                'etiquetable_type' => BlogPost::class,
                'etiquetable_id' => $post->id,
            ]);
        }
    }

    private function generarSlugUnico(string $titulo): string
    {
        $base = str_slug($titulo);
        $slug = $base;
        $i = 1;

        while (BlogPost::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function procesarImagen(array $archivo, bool $verificarTamano = true): string
    {
        $mime = $this->mimeReal($archivo);
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowed)) {
            throw new \RuntimeException('Tipo de archivo no permitido. Use JPG, PNG, GIF o WebP.');
        }

        $maxSize = 5 * 1024 * 1024;
        if ($verificarTamano && $archivo['size'] > $maxSize) {
            throw new \RuntimeException('El archivo excede el tamaño máximo de 5MB.');
        }

        if (!extension_loaded('gd') || !(gd_info()['WebP Support'] ?? false)) {
            throw new \RuntimeException('El servidor no tiene GD con soporte WebP.');
        }

        $dir = dirname(__DIR__, 2) . '/uploads/blog';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombre = 'blog_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.webp';
        $ruta = $dir . '/' . $nombre;
        $maxWidth = 1200;

        $img = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($archivo['tmp_name']),
            'image/png'  => imagecreatefrompng($archivo['tmp_name']),
            'image/gif'  => imagecreatefromgif($archivo['tmp_name']),
            'image/webp' => @imagecreatefromwebp($archivo['tmp_name']),
        };

        if (!$img) {
            throw new \RuntimeException('Error al procesar la imagen.');
        }

        $origWidth = imagesx($img);
        $origHeight = imagesy($img);

        if ($origWidth > $maxWidth) {
            $ratio = $maxWidth / $origWidth;
            $newHeight = (int) ($origHeight * $ratio);
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $maxWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($img);
            $img = $resized;
        }

        imagealphablending($img, false);
        imagesavealpha($img, true);
        $ok = imagewebp($img, $ruta, 85);
        imagedestroy($img);

        if (!$ok) {
            throw new \RuntimeException('Error al convertir la imagen a WebP.');
        }

        return 'blog/' . $nombre;
    }

    /**
     * Procesa la media animada de la tarjeta (GIF animado, MP4 o WebM).
     * Devuelve ['path' => ruta|null, 'tipo' => 'gif'|'video'|null].
     */
    private function procesarMedia(array $archivo): array
    {
        $mime = $this->mimeReal($archivo);
        $permitidos = ['image/gif', 'video/mp4', 'video/x-m4v', 'video/webm'];
        if (!in_array($mime, $permitidos)) {
            throw new \RuntimeException('Tipo de media no permitido. Use GIF animado, MP4 (H.264) o WebM.');
        }

        $maxSize = 8 * 1024 * 1024;
        if ($archivo['size'] > $maxSize) {
            throw new \RuntimeException('El archivo de media excede el tamaño máximo de 8MB.');
        }

        $dir = dirname(__DIR__, 2) . '/uploads/blog';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($mime === 'image/gif' && !$this->esGifAnimado($archivo['tmp_name'])) {
            return ['path' => null, 'tipo' => null];
        }

        $ext = match ($mime) {
            'image/gif' => 'gif',
            'video/mp4', 'video/x-m4v' => 'mp4',
            'video/webm' => 'webm',
        };
        $nombre = 'blog_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        if (!move_uploaded_file($archivo['tmp_name'], $dir . '/' . $nombre)) {
            throw new \RuntimeException('Error al guardar el archivo de media.');
        }

        return [
            'path' => 'blog/' . $nombre,
            'tipo' => $mime === 'image/gif' ? 'gif' : 'video',
            'ruta' => $dir . '/' . $nombre,
        ];
    }

    /**
     * Resuelve el embed de la tarjeta (Instagram/Twitter/etc.) y devuelve
     * ['card_embed_url' => string|null, 'card_embed_html' => string|null].
     */
    private function procesarEmbedTarjeta(?string $url): array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return ['card_embed_url' => null, 'card_embed_html' => null];
        }

        $resultado = $this->oembedService->resolve($url);
        if (empty($resultado['success']) || empty($resultado['html'])) {
            throw new \RuntimeException('No se pudo resolver el embed de la tarjeta. Verifica la URL. ' . ($resultado['error'] ?? ''));
        }

        return ['card_embed_url' => $url, 'card_embed_html' => $resultado['html']];
    }

    /**
     * Gestiona la subida de imagen destacada, media de tarjeta y embed oEmbed
     * tanto en creación como en actualización.
     */
    private function procesarArchivosAlGuardar(?BlogPost $postActual, array &$datos): void
    {
        $imagenActual = $postActual->imagen_destacada_path ?? '';
        $mediaActual = $postActual->media_destacada_path ?? '';
        $subioImagen = isset($_FILES['imagen_destacada']) && $_FILES['imagen_destacada']['error'] === UPLOAD_ERR_OK;
        $subioMedia = isset($_FILES['media_destacada']) && $_FILES['media_destacada']['error'] === UPLOAD_ERR_OK;

        if ($subioImagen) {
            $this->eliminarArchivo($imagenActual);
            $datos['imagen_destacada_path'] = $this->procesarImagen($_FILES['imagen_destacada']);
        }

        $tieneImagen = !empty($datos['imagen_destacada_path'] ?? null) || $imagenActual !== '';

        if ($subioMedia) {
            $archivoMedia = $_FILES['media_destacada'];

            // El video necesita una imagen estática como póster y para SEO (og:image).
            if (!$tieneImagen && in_array($this->mimeReal($archivoMedia), ['video/mp4', 'video/x-m4v', 'video/webm'], true)) {
                throw new \RuntimeException('Para reproducir un video en la tarjeta debes subir también la imagen destacada (se usa como póster y para SEO).');
            }

            $media = $this->procesarMedia($archivoMedia);

            if ($media['path'] === null) {
                // GIF estático: no es animación; se trata como imagen normal.
                $this->eliminarArchivo($mediaActual);
                $datos['media_destacada_path'] = null;
                $datos['media_destacada_tipo'] = null;
                if (!$tieneImagen) {
                    $datos['imagen_destacada_path'] = $this->procesarImagen($archivoMedia);
                }
            } else {
                $this->eliminarArchivo($mediaActual);
                $datos['media_destacada_path'] = $media['path'];
                $datos['media_destacada_tipo'] = $media['tipo'];

                // GIF animado sin imagen estática: póster WebP desde el archivo
                // ya movido (el temporal original ya no existe).
                if ($media['tipo'] === 'gif' && !$tieneImagen) {
                    $datos['imagen_destacada_path'] = $this->procesarImagen([
                        'tmp_name' => $media['ruta'],
                        'type' => 'image/gif',
                        'size' => (int) @filesize($media['ruta']),
                        'name' => basename($media['ruta']),
                    ], false);
                }
            }
        }

        if (array_key_exists('card_embed_url', $datos)) {
            $embed = $this->procesarEmbedTarjeta($datos['card_embed_url']);
            $datos['card_embed_url'] = $embed['card_embed_url'];
            $datos['card_embed_html'] = $embed['card_embed_html'];
        }

        unset($datos['media_destacada']);
        unset($datos['imagen_destacada']);
    }

    private function eliminarArchivo(?string $ruta): void
    {
        if (!empty($ruta) && file_exists(dirname(__DIR__, 2) . '/uploads/' . $ruta)) {
            unlink(dirname(__DIR__, 2) . '/uploads/' . $ruta);
        }
    }

    private function mimeReal(array $archivo): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $archivo['tmp_name']);
                finfo_close($finfo);
                if ($mime && $mime !== 'application/octet-stream') {
                    return $mime;
                }
            }
        }

        $ext = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
        return match ($ext) {
            'gif' => 'image/gif',
            'mp4', 'm4v' => 'video/mp4',
            'webm' => 'video/webm',
            default => $archivo['type'] ?? '',
        };
    }

    private function esGifAnimado(string $ruta): bool
    {
        $contenido = @file_get_contents($ruta);
        return $contenido !== false && strpos($contenido, 'NETSCAPE2.0') !== false;
    }
}
