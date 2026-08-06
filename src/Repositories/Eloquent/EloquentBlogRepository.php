<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Database\Connection;
use App\Models\BlogPost;
use App\Models\BlogCategoria;
use App\Models\User;
use App\Models\BlogPostVersion;
use App\Repositories\Contracts\BlogRepositoryInterface;

class EloquentBlogRepository implements BlogRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array
    {
        $query = Connection::table('blog_posts')
            ->whereNull('deleted_at');

        if (!empty($filtros['busqueda'])) {
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $query = $query->whereRaw('(titulo LIKE ? OR extracto LIKE ?)', [$busqueda, $busqueda]);
        }

        if (!empty($filtros['status'])) {
            $query = $query->where('status', $filtros['status']);
        }

        if (!empty($filtros['tipo'])) {
            $query = $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['categoria_id'])) {
            $query = $query->where('categoria_id', $filtros['categoria_id']);
        }

        if (!empty($filtros['autor_id'])) {
            $query = $query->where('autor_id', $filtros['autor_id']);
        }

        $query = $query->orderByDesc('created_at');

        return $query->paginate($porPagina, $pagina);
    }

    public function buscarPorSlugPublicado(string $slug): ?BlogPost
    {
        $row = Connection::table('blog_posts')
            ->where('slug', $slug)
            ->where('status', 'publicado')
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return null;
        }

        $blog = BlogPost::fromRow($row);
        $this->cargarRelaciones($blog);

        return $blog;
    }

    public function crear(array $datos): BlogPost
    {
        $datos['slug'] = BlogPost::generarSlug($datos['titulo'] ?? '');
        $blog = BlogPost::create($datos);
        return $blog;
    }

    public function actualizar(BlogPost $blog, array $datos): BlogPost
    {
        if (isset($datos['titulo']) && $datos['titulo'] !== $blog->titulo) {
            $datos['slug'] = BlogPost::generarSlug($datos['titulo']);
        }

        $blog->update($datos);
        return $blog;
    }

    public function eliminar(BlogPost $blog): bool
    {
        return $blog->delete();
    }

    public function contar(): int
    {
        return Connection::table('blog_posts')
            ->whereNull('deleted_at')
            ->count();
    }

    private function cargarRelaciones(BlogPost $blog): void
    {
        if ($blog->categoria_id) {
            $blog->categoria = BlogCategoria::find($blog->categoria_id);
        }

        if ($blog->autor_id) {
            $blog->autor = User::find($blog->autor_id);
        }

        $rows = Connection::table('blog_post_versiones')
            ->where('blog_post_id', $blog->id)
            ->orderByDesc('created_at')
            ->get();
        $blog->versiones = BlogPostVersion::fromRows($rows);
    }
}
