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

class BlogService
{
    private $repositorio;
    private SeoService $seoService;
    private HtmlSanitizer $htmlSanitizer;

    public function __construct($repositorio = null, ?SeoService $seoService = null, ?HtmlSanitizer $htmlSanitizer = null)
    {
        $this->repositorio = $repositorio ?? App::make('App\\Repositories\\Contracts\\BlogRepositoryInterface');
        $this->seoService = $seoService ?? App::make(SeoService::class);
        $this->htmlSanitizer = $htmlSanitizer ?? App::make(HtmlSanitizer::class);
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

        if (isset($_FILES['imagen_destacada']) && $_FILES['imagen_destacada']['error'] === UPLOAD_ERR_OK) {
            $datos['imagen_destacada_path'] = $this->procesarImagen($_FILES['imagen_destacada']);
        }
        unset($datos['imagen_destacada']);

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

        if (isset($_FILES['imagen_destacada']) && $_FILES['imagen_destacada']['error'] === UPLOAD_ERR_OK) {
            if (!empty($post->imagen_destacada_path) && file_exists(dirname(__DIR__, 2) . '/uploads/' . $post->imagen_destacada_path)) {
                unlink(dirname(__DIR__, 2) . '/uploads/' . $post->imagen_destacada_path);
            }
            $datos['imagen_destacada_path'] = $this->procesarImagen($_FILES['imagen_destacada']);
        }
        unset($datos['imagen_destacada']);

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

    private function procesarImagen(array $archivo): string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($archivo['type'], $allowed)) {
            throw new \RuntimeException('Tipo de archivo no permitido. Use JPG, PNG, GIF o WebP.');
        }

        $maxSize = 5 * 1024 * 1024;
        if ($archivo['size'] > $maxSize) {
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

        $img = match ($archivo['type']) {
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
}
