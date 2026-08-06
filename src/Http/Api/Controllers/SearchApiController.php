<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\BusquedaService;

class SearchApiController
{
    private BusquedaService $service;

    public function __construct()
    {
        $this->service = App::make(BusquedaService::class);
    }

    public function buscar(): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            $request = Request::fromGlobals();
            $termino = $request->query('q', '');

            if (mb_strlen(trim($termino)) < 2) {
                Response::json([
                    'data' => [],
                    'mensaje' => 'El término de búsqueda debe tener al menos 2 caracteres.',
                ]);
                return;
            }

            $resultados = $this->service->buscarGlobal($termino);

            $agrupados = [];
            foreach ($resultados as $item) {
                $tipo = $item['tipo'];
                if (!isset($agrupados[$tipo])) {
                    $agrupados[$tipo] = ['tipo' => $tipo, 'items' => []];
                }
                $agrupados[$tipo]['items'][] = $item;
            }

            Response::json([
                'data' => array_values($agrupados),
                'total' => count($resultados),
                'termino' => $termino,
            ]);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Error al realizar búsqueda.', 'detalle' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : 'Error interno del servidor'], 500);
        }
    }
}
