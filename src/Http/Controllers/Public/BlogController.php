<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\View;
use App\Core\Response;
use App\Database\Connection;

class BlogController
{
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $tipoFiltro = $_GET['tipo'] ?? null;

        $query = Connection::table('blog_posts')
            ->where('blog_posts.status', 'publicado')
            ->whereNull('blog_posts.deleted_at')
            ->leftJoin('blog_categorias', 'blog_categorias.id', '=', 'blog_posts.categoria_id')
            ->leftJoin('users', 'users.id', '=', 'blog_posts.autor_id')
            ->select(
                'blog_posts.*',
                'blog_categorias.nombre as categoria_nombre',
                'users.name as autor_nombre'
            );

        if ($tipoFiltro && in_array($tipoFiltro, ['noticia', 'opinion', 'investigacion'])) {
            $query = $query->where('blog_posts.tipo', $tipoFiltro);
        }

        $paginator = $query
            ->orderByDesc('blog_posts.published_at')
            ->paginate(15, $page);

        View::render('public.blog.index', [
            'paginator' => $paginator,
            'tipoActual' => $tipoFiltro,
        ]);
    }

    public function show(string $slug): void
    {
        $post = Connection::table('blog_posts')
            ->where('slug', $slug)
            ->where('status', 'publicado')
            ->whereNull('deleted_at')
            ->first();

        if (!$post) {
            Response::notFound();
            return;
        }

        $categoria = null;
        if ($post->categoria_id) {
            $categoria = Connection::table('blog_categorias')
                ->where('id', $post->categoria_id)
                ->first();
        }

        $autor = null;
        if ($post->autor_id) {
            $autor = Connection::table('users')
                ->where('id', $post->autor_id)
                ->first();
        }

        $comentarios = Connection::table('blog_comentarios')
            ->where('blog_post_id', $post->id)
            ->whereNull('parent_id')
            ->where('status', 'aprobado')
            ->orderByDesc('created_at')
            ->get();

        View::render('public.blog.show', [
            'post' => $post,
            'categoria' => $categoria,
            'autor' => $autor,
            'comentarios' => $comentarios,
        ]);
    }
}
