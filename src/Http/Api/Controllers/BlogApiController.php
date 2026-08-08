<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\BlogPost;
use App\Services\BlogService;

class BlogApiController
{
    private BlogService $service;

    public function __construct()
    {
        $this->service = App::make(BlogService::class);
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
                'tipo' => $_GET['tipo'] ?? null,
                'categoria_id' => $_GET['categoria'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');

            if (!Auth::isAdmin()) {
                $filtros['autor_id'] = Auth::id();
            }

            $resultado = $this->service->paraAdmin($filtros);

            // El listado no necesita el HTML resuelto del embed (la tarjeta usa la
            // URL y el HTML se regenera desde la ficha). Se descarta para aligerar el JSON.
            $filas = array_map(function ($fila) {
                if (is_object($fila)) {
                    unset($fila->card_embed_html);
                }
                return $fila;
            }, $resultado['data']);
            $resultado['data'] = $filas;

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
            Response::json([
                'error' => 'Error al listar posts del blog.',
                'detalle' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ], 500);
        }
    }

    public function store(): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            if (!Auth::canPublish()) {
                Response::json(['error' => 'No tiene permiso para crear artículos.'], 403);
                return;
            }

            $request = Request::fromGlobals();
            if (!Csrf::verify($request)) {
                Response::json(['error' => 'Token CSRF inválido.'], 403);
                return;
            }

            $datos = $request->json() ?? $request->all();

            if (!Auth::isAdmin()) {
                unset($datos['destacado']);
                unset($datos['interes']);
            }

            $validador = Validator::make($datos, [
                'titulo' => 'required|string|min:2|max:255',
                'tipo' => 'required|string|in:noticia,opinion,investigacion',
                'categoria_id' => 'nullable|numeric',
                'status' => 'nullable|string|in:borrador,publicado',
                'destacado' => 'nullable|boolean',
                'interes' => 'nullable|boolean',
                'fuentes' => 'nullable|string',
            ]);

            if ($validador->fails()) {
                Response::json(['error' => 'Validación fallida.', 'errores' => $validador->errors()], 422);
                return;
            }

            $post = $this->service->crear($datos);

            Response::json([
                'mensaje' => 'Post del blog creado exitosamente.',
                'data' => $post,
            ], 201);
        } catch (\Throwable $e) {
            Response::json([
                'error' => 'Error al crear post del blog.',
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ], 500);
        }
    }

    public function update(string $id): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            if (!Auth::canPublish()) {
                Response::json(['error' => 'No tiene permiso para editar artículos.'], 403);
                return;
            }

            $post = BlogPost::find((int) $id);
            if (!$post || $post->deleted_at !== null) {
                Response::json(['error' => 'Post del blog no encontrado.'], 404);
                return;
            }

            if (!Auth::isAdmin() && (int) $post->autor_id !== Auth::id()) {
                Response::json(['error' => 'No tiene permiso para editar este artículo.'], 403);
                return;
            }

            $request = Request::fromGlobals();
            $datos = $request->json() ?? $request->all();

            if (!Auth::isAdmin()) {
                unset($datos['destacado']);
                unset($datos['interes']);
            }

            $validador = Validator::make($datos, [
                'titulo' => 'required|string|min:2|max:255',
                'tipo' => 'sometimes|required|string|in:noticia,opinion,investigacion',
                'categoria_id' => 'nullable|numeric',
                'status' => 'nullable|string|in:borrador,publicado',
                'destacado' => 'nullable|boolean',
                'interes' => 'nullable|boolean',
                'fuentes' => 'nullable|string',
            ]);

            if ($validador->fails()) {
                Response::json(['error' => 'Validación fallida.', 'errores' => $validador->errors()], 422);
                return;
            }

            $post = $this->service->actualizar($post, $datos);

            Response::json([
                'mensaje' => 'Post del blog actualizado exitosamente.',
                'data' => $post,
            ]);
        } catch (\Throwable $e) {
            Response::json([
                'error' => 'Error al actualizar post del blog.',
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ], 500);
        }
    }

    public function destroy(string $id): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            $post = BlogPost::find((int) $id);
            if (!$post || $post->deleted_at !== null) {
                Response::json(['error' => 'Post del blog no encontrado.'], 404);
                return;
            }

            if (!Auth::isAdmin()) {
                if (!Auth::canPublish()) {
                    Response::json(['error' => 'No tiene permiso para eliminar artículos.'], 403);
                    return;
                }

                if ((int) $post->autor_id !== Auth::id()) {
                    Response::json(['error' => 'No tiene permiso para eliminar este artículo.'], 403);
                    return;
                }

                if ($post->tieneSolicitudBorrado()) {
                    Response::json(['error' => 'Este artículo ya tiene una solicitud de borrado pendiente.'], 409);
                    return;
                }

                $this->service->solicitarBorrado($post, Auth::id());

                Response::json([
                    'mensaje' => 'Solicitud de borrado enviada para moderación.',
                    'moderacion' => true,
                ]);
                return;
            }

            $this->service->eliminar($post);

            Response::json(['mensaje' => 'Post del blog eliminado exitosamente.']);
        } catch (\Throwable $e) {
            Response::json([
                'error' => 'Error al eliminar post del blog.',
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ], 500);
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

            $post = BlogPost::find((int) $id);
            if (!$post || $post->deleted_at !== null) {
                Response::json(['error' => 'Post del blog no encontrado.'], 404);
                return;
            }

            if (!Auth::isAdmin() && (int) $post->autor_id !== Auth::id()) {
                Response::json(['error' => 'No tiene permiso para publicar este artículo.'], 403);
                return;
            }

            $post = $this->service->publicar($post);

            Response::json([
                'mensaje' => 'Post del blog publicado exitosamente.',
                'data' => $post,
            ]);
        } catch (\Throwable $e) {
            Response::json([
                'error' => 'Error al publicar post del blog.',
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ], 500);
        }
    }
}
