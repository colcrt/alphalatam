<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Domain\Noticia\DTO\NoticiaData;
use App\Models\Noticia;
use App\Services\NoticiaService;

class NoticiaApiController
{
    private NoticiaService $service;

    public function __construct()
    {
        $this->service = App::make(NoticiaService::class);
    }

    public function index(): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            $filtros = array_filter([
                'busqueda' => $_GET['busqueda'] ?? null,
                'status' => $_GET['status'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');

            $resultado = $this->service->paraAdmin($filtros);

            Response::json([
                'data' => $resultado['data'],
                'meta' => [
                    'current_page' => $resultado['current_page'],
                    'last_page' => $resultado['last_page'],
                    'per_page' => $resultado['per_page'],
                    'total' => $resultado['total'],
                ],
            ]);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Error al listar noticias.', 'detalle' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : 'Error interno del servidor'], 500);
        }
    }

    public function store(): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            $request = Request::fromGlobals();
            $datos = $request->json() ?? $request->all();

            $validador = Validator::make($datos, [
                'titulo' => 'required|string|min:2|max:255',
                'medio' => 'nullable|string|max:255',
                'url_original' => 'nullable|string|max:500',
                'status' => 'nullable|string|in:borrador,publicado',
            ]);

            if ($validador->fails()) {
                Response::json(['error' => 'Validación fallida.', 'errores' => $validador->errors()], 422);
                return;
            }

            $noticia = $this->service->crear(NoticiaData::fromArray($datos));

            Response::json([
                'mensaje' => 'Noticia creada exitosamente.',
                'data' => $noticia,
            ], 201);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Error al crear noticia.', 'detalle' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : 'Error interno del servidor'], 500);
        }
    }

    public function update(string $id): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            $noticia = Noticia::find((int) $id);
            if (!$noticia) {
                Response::json(['error' => 'Noticia no encontrada.'], 404);
                return;
            }

            $request = Request::fromGlobals();
            $datos = $request->json() ?? $request->all();

            $validador = Validator::make($datos, [
                'titulo' => 'required|string|min:2|max:255',
                'medio' => 'nullable|string|max:255',
                'url_original' => 'nullable|string|max:500',
                'status' => 'nullable|string|in:borrador,publicado',
            ]);

            if ($validador->fails()) {
                Response::json(['error' => 'Validación fallida.', 'errores' => $validador->errors()], 422);
                return;
            }

            $noticia = $this->service->actualizar($noticia, NoticiaData::fromArray($datos));

            Response::json([
                'mensaje' => 'Noticia actualizada exitosamente.',
                'data' => $noticia,
            ]);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Error al actualizar noticia.', 'detalle' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : 'Error interno del servidor'], 500);
        }
    }

    public function destroy(string $id): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            $noticia = Noticia::find((int) $id);
            if (!$noticia) {
                Response::json(['error' => 'Noticia no encontrada.'], 404);
                return;
            }

            $this->service->eliminar($noticia);

            Response::json(['mensaje' => 'Noticia eliminada exitosamente.']);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Error al eliminar noticia.', 'detalle' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : 'Error interno del servidor'], 500);
        }
    }

    public function publish(string $id): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            if (!Auth::canPublish()) {
                Response::json(['error' => 'No tiene permiso para publicar.'], 403);
                return;
            }

            $noticia = Noticia::find((int) $id);
            if (!$noticia) {
                Response::json(['error' => 'Noticia no encontrada.'], 404);
                return;
            }

            $noticia = $this->service->publicar($noticia);

            Response::json([
                'mensaje' => 'Noticia publicada exitosamente.',
                'data' => $noticia,
            ]);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Error al publicar noticia.', 'detalle' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : 'Error interno del servidor'], 500);
        }
    }
}
