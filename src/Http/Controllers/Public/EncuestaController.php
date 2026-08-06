<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\App;
use App\Core\Auth\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Services\ConfiguracionService;

class EncuestaController
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
        $encuesta = $configService->obtener('encuesta_activa');

        if (!is_array($encuesta) || empty($encuesta['pregunta']) || empty($encuesta['opciones'])) {
            Response::json(['error' => 'No hay una encuesta activa.'], 404);
            return;
        }

        $opciones = array_values($encuesta['opciones']);

        if (!isset($opciones[$indice])) {
            Response::json(['error' => 'Opción inválida.'], 422);
            return;
        }

        $opciones[$indice]['votos'] = (int) ($opciones[$indice]['votos'] ?? 0) + 1;

        $encuesta['opciones'] = $opciones;
        $configService->guardar('encuesta_activa', $encuesta, 'general');

        Response::json([
            'ok' => true,
            'pregunta' => $encuesta['pregunta'],
            'opciones' => $opciones,
            'total_votos' => array_sum(array_column($opciones, 'votos')),
        ]);
    }
}
