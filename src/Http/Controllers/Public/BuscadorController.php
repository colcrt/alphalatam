<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\View;
use App\Services\BusquedaService;

class BuscadorController
{
    public function index(): void
    {
        $termino = trim($_GET['q'] ?? '');
        $resultados = [];

        if ($termino !== '') {
            $service = new BusquedaService();
            $resultados = $service->buscarGlobal($termino);
        }

        View::render('public.buscar.index', [
            'termino' => $termino,
            'resultados' => $resultados,
            'total' => count($resultados),
        ]);
    }
}
