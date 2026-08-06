<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers;

use App\Core\Session;
use App\Core\Validator;
use App\Services\BlogService;

class CategoriaApiController extends ApiBaseController
{
    private BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        parent::__construct();
        $this->blogService = $blogService;
    }

    public function index(): void
    {
        $categorias = $this->blogService->obtenerCategorias();
        $this->json(['data' => $categorias]);
    }

    public function store(): void
    {
        $input = $this->getJsonInput();
        $validator = Validator::make($input, [
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            $this->jsonResponse(422, ['errors' => $validator->errors()]);
            return;
        }

        try {
            if (empty($input['slug'])) {
                $input['slug'] = str_slug($input['nombre']);
            }
            $categoria = $this->blogService->crearCategoria($input);
            $this->json(['data' => $categoria, 'message' => 'Categoría creada correctamente.'], 201);
        } catch (\Exception $e) {
            $this->jsonResponse(500, ['message' => 'Error al crear: ' . $e->getMessage()]);
        }
    }

    public function update(int $id): void
    {
        $input = $this->getJsonInput();
        $validator = Validator::make($input, [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            $this->jsonResponse(422, ['errors' => $validator->errors()]);
            return;
        }

        try {
            if (empty($input['slug']) && !empty($input['nombre'])) {
                $input['slug'] = str_slug($input['nombre']);
            }
            $this->blogService->actualizarCategoria($id, $input);
            $this->json(['message' => 'Categoría actualizada correctamente.']);
        } catch (\Exception $e) {
            $this->jsonResponse(500, ['message' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $this->blogService->eliminarCategoria($id);
            $this->json(['message' => 'Categoría eliminada correctamente.']);
        } catch (\Exception $e) {
            $this->jsonResponse(500, ['message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }
}
