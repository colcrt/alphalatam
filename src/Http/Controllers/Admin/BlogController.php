<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\BlogPost;
use App\Services\BlogService;

final class BlogController
{
    private BlogService $service;

    public function __construct()
    {
        $this->service = App::make(BlogService::class);
    }

    public function index(): void
    {
        $user = Auth::user();

        View::render('admin.app', [
            'user' => $user,
            'module' => 'blog',
        ]);
    }

    public function create(): void
    {
        $user = Auth::user();

        View::render('admin.blog-form', [
            'user' => $user,
            'postId' => null,
        ]);
    }

    public function edit(int $id): void
    {
        $user = Auth::user();

        View::render('admin.blog-form', [
            'user' => $user,
            'postId' => $id,
        ]);
    }

    public function borrados(): void
    {
        $user = Auth::user();
        $this->autorizarModeracion($user);

        $pagina = $this->service->obtenerSolicitudesBorrado((int) ($_GET['page'] ?? 1));

        View::render('admin.blog-borrados', [
            'user' => $user,
            'solicitudes' => $pagina['data'],
            'paginacion' => $pagina,
        ]);
    }

    public function aprobarBorrado(string $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/blog/borrados'));
            exit;
        }

        $user = Auth::user();
        $this->autorizarModeracion($user);

        $post = BlogPost::find((int) $id);
        if (!$post || $post->deleted_at !== null || !$post->tieneSolicitudBorrado()) {
            Session::flash('error', 'Artículo no encontrado o sin solicitud de borrado.');
            header('Location: ' . url('/admin/blog/borrados'));
            exit;
        }

        $this->service->aprobarBorrado($post);

        Session::flash('exito', 'Solicitud aprobada. El artículo "' . $post->titulo . '" fue eliminado.');
        header('Location: ' . url('/admin/blog/borrados'));
        exit;
    }

    public function rechazarBorrado(string $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/blog/borrados'));
            exit;
        }

        $user = Auth::user();
        $this->autorizarModeracion($user);

        $post = BlogPost::find((int) $id);
        if (!$post || $post->deleted_at !== null || !$post->tieneSolicitudBorrado()) {
            Session::flash('error', 'Artículo no encontrado o sin solicitud de borrado.');
            header('Location: ' . url('/admin/blog/borrados'));
            exit;
        }

        $this->service->rechazarBorrado($post);

        Session::flash('exito', 'Solicitud rechazada. El artículo "' . $post->titulo . '" se mantiene.');
        header('Location: ' . url('/admin/blog/borrados'));
        exit;
    }

    private function autorizarModeracion(object $user): void
    {
        if (!$user->esAdmin()) {
            Session::flash('error', 'No tienes permisos para moderar solicitudes de borrado.');
            header('Location: ' . url('/admin/dashboard'));
            exit;
        }
    }
}
