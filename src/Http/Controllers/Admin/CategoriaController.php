<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth\Auth;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Services\BlogService;

class CategoriaController
{
    private BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    public function index(): void
    {
        $user = Auth::user();
        $categorias = $this->blogService->obtenerCategorias();

        View::render('admin.categorias-index', [
            'user' => $user,
            'categorias' => $categorias,
        ]);
    }

    public function create(): void
    {
        $user = Auth::user();

        View::render('admin.categorias-form', [
            'user' => $user,
            'editMode' => false,
            'categoria' => null,
        ]);
    }

    public function edit(int $id): void
    {
        $user = Auth::user();
        $categoria = $this->blogService->obtenerCategoriaPorId($id);

        View::render('admin.categorias-form', [
            'user' => $user,
            'editMode' => true,
            'categoria' => $categoria ? $categoria->toArray() : null,
        ]);
    }

    public function show(int $id): void
    {
        header('Location: ' . url('/admin/categorias/' . $id . '/editar'));
        exit;
    }

    public function store(): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/categorias'));
            exit;
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];

        if (empty($data['nombre'])) {
            Session::flash('error', 'El nombre es obligatorio.');
            header('Location: ' . url('/admin/categorias'));
            exit;
        }

        if (empty($data['slug'])) {
            $data['slug'] = str_slug($data['nombre']);
        }

        try {
            $this->blogService->crearCategoria($data);
            Session::flash('success', 'Categoría creada correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al crear la categoría: ' . $e->getMessage());
        }

        header('Location: ' . url('/admin/categorias'));
        exit;
    }

    public function update(int $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/categorias'));
            exit;
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];

        if (empty($data['nombre'])) {
            Session::flash('error', 'El nombre es obligatorio.');
            header('Location: ' . url('/admin/categorias'));
            exit;
        }

        if (empty($data['slug'])) {
            $data['slug'] = str_slug($data['nombre']);
        }

        try {
            $this->blogService->actualizarCategoria($id, $data);
            Session::flash('success', 'Categoría actualizada correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al actualizar la categoría: ' . $e->getMessage());
        }

        header('Location: ' . url('/admin/categorias'));
        exit;
    }

    public function destroy(int $id): void
    {
        $request = Request::fromGlobals();
        if (!Csrf::verify($request)) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            header('Location: ' . url('/admin/categorias'));
            exit;
        }

        try {
            $this->blogService->eliminarCategoria($id);
            Session::flash('success', 'Categoría eliminada correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al eliminar: ' . $e->getMessage());
        }

        header('Location: ' . url('/admin/categorias'));
        exit;
    }
}
