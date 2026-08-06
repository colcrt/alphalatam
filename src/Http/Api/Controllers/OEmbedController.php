<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Core\Response;
use App\Services\OEmbedService;

class OEmbedController
{
    private OEmbedService $service;

    public function __construct()
    {
        $this->service = App::make(OEmbedService::class);
    }

    public function resolve(): void
    {
        try {
            if (!Auth::check()) {
                Response::json(['error' => 'No autenticado.'], 401);
                return;
            }

            $url = trim($_POST['url'] ?? $_GET['url'] ?? '');

            if (!$url) {
                Response::json(['error' => 'La URL es requerida.'], 422);
                return;
            }

            $result = $this->service->resolve($url);

            if ($result['success']) {
                Response::json([
                    'success' => true,
                    'html' => $result['html'],
                    'provider' => $result['provider'],
                    'provider_name' => $result['provider_name'] ?? $result['provider'],
                    'type' => $result['type'],
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Error al resolver el embed.',
                ], 422);
            }
        } catch (\Throwable $e) {
            Response::json([
                'error' => 'Error al procesar la solicitud.',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }
}
