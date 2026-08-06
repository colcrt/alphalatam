<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers;

use App\Services\BlogService;

class ComentarioApiController extends ApiBaseController
{
    private BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        parent::__construct();
        $this->blogService = $blogService;
    }

    public function index(): void
    {
        $status = $_GET['status'] ?? '';
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 20);

        $comentarios = $this->blogService->obtenerComentariosPaginados($status, $page, $perPage);

        $this->json([
            'data' => $comentarios['data'],
            'meta' => [
                'current_page' => $comentarios['current_page'],
                'last_page' => $comentarios['last_page'],
                'total' => $comentarios['total'],
                'per_page' => $perPage,
            ],
        ]);
    }

    public function approve(int $id): void
    {
        try {
            $this->blogService->cambiarEstadoComentario($id, 'aprobado');
            $this->json(['message' => 'Comentario aprobado.']);
        } catch (\Exception $e) {
            $this->jsonResponse(500, ['message' => 'Error al aprobar: ' . $e->getMessage()]);
        }
    }

    public function reject(int $id): void
    {
        try {
            $this->blogService->cambiarEstadoComentario($id, 'rechazado');
            $this->json(['message' => 'Comentario rechazado.']);
        } catch (\Exception $e) {
            $this->jsonResponse(500, ['message' => 'Error al rechazar: ' . $e->getMessage()]);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $this->blogService->eliminarComentario($id);
            $this->json(['message' => 'Comentario eliminado.']);
        } catch (\Exception $e) {
            $this->jsonResponse(500, ['message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }
}
