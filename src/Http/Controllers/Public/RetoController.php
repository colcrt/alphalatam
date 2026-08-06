<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\App;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Services\ConfiguracionService;

class RetoController
{
    public function votar(): void
    {
        $request = Request::fromGlobals();

        if (!Csrf::verify($request)) {
            Response::json(['error' => 'Token de seguridad inválido.'], 403);
            return;
        }

        $indice = (int) $request->input('opcion', -1);

        $configService = App::make(ConfiguracionService::class);
        $reto = $configService->obtener('reto_activa');

        if (!is_array($reto) || empty($reto['pregunta']) || empty($reto['opciones'])) {
            Response::json(['error' => 'No hay un reto activo.'], 404);
            return;
        }

        $opciones = array_values($reto['opciones']);

        if (!isset($opciones[$indice])) {
            Response::json(['error' => 'Opción inválida.'], 422);
            return;
        }

        $opciones[$indice]['votos'] = (int) ($opciones[$indice]['votos'] ?? 0) + 1;

        $reto['opciones'] = $opciones;
        $configService->guardar('reto_activa', $reto, 'general');

        Response::json([
            'ok' => true,
            'pregunta' => $reto['pregunta'],
            'opciones' => $opciones,
            'total_votos' => array_sum(array_column($opciones, 'votos')),
            'respuesta_correcta' => (int) ($reto['respuesta_correcta'] ?? -1),
        ]);
    }
}
