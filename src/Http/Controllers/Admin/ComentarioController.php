<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth\Auth;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Services\BlogService;

class ComentarioController
{
    private BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    public function index(): void
    {
        $user = Auth::user();
        $status = $_GET['status'] ?? '';
        $comentarios = $this->blogService->obtenerComentariosFiltrados($status);

        View::render('admin.comentarios-index', [
            'user' => $user,
            'comentarios' => $comentarios,
            'statusFiltro' => $status,
        ]);
    }

    public function approve(int $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/comentarios'));
            exit;
        }

        try {
            $this->blogService->cambiarEstadoComentario($id, 'aprobado');
            Session::flash('success', 'Comentario aprobado.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al aprobar: ' . $e->getMessage());
        }

        header('Location: ' . url('/admin/comentarios'));
        exit;
    }

    public function reject(int $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/comentarios'));
            exit;
        }

        try {
            $this->blogService->cambiarEstadoComentario($id, 'rechazado');
            Session::flash('success', 'Comentario rechazado.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al rechazar: ' . $e->getMessage());
        }

        header('Location: ' . url('/admin/comentarios'));
        exit;
    }

    public function destroy(int $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/comentarios'));
            exit;
        }

        try {
            $this->blogService->eliminarComentario($id);
            Session::flash('success', 'Comentario eliminado.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al eliminar: ' . $e->getMessage());
        }

        header('Location: ' . url('/admin/comentarios'));
        exit;
    }
}
