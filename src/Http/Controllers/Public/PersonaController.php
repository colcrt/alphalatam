<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\View;
use App\Core\Response;
use App\Database\Connection;
use App\Services\PersonaService;

class PersonaController
{
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $paginator = Connection::table('personas')
            ->where('status', 'publicado')
            ->whereNull('deleted_at')
            ->orderByDesc('nombre')
            ->paginate(15, $page);

        View::render('public.personas.index', [
            'paginator' => $paginator,
        ]);
    }

    public function show(string $slug): void
    {
        $service = new PersonaService();
        $persona = $service->obtenerFichaPublica($slug);

        if (!$persona) {
            Response::notFound();
            return;
        }

        View::render('public.personas.show', [
            'persona' => $persona,
        ]);
    }
}
