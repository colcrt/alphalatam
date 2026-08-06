<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\App;
use App\Core\View;
use App\Database\Connection;
use App\Services\ConfiguracionService;

class HomeController
{
    public function index(): void
    {
        $estelar = $this->obtenerEstelar();
        $estelarId = $estelar->id ?? 0;

        $noticiasRecientes = Connection::table('blog_posts')
            ->where('tipo', 'noticia')
            ->where('status', 'publicado')
            ->whereNull('deleted_at')
            ->where('id', '!=', $estelarId)
            ->orderByDesc('published_at')
            ->limit(11)->get();

        $opinionRecientes = Connection::table('blog_posts')
            ->where('tipo', 'opinion')
            ->where('status', 'publicado')
            ->whereNull('deleted_at')
            ->where('id', '!=', $estelarId)
            ->orderByDesc('published_at')
            ->limit(12)->get();

        $investigacionesRecientes = Connection::table('blog_posts')
            ->where('tipo', 'investigacion')
            ->where('status', 'publicado')
            ->whereNull('deleted_at')
            ->where('id', '!=', $estelarId)
            ->orderByDesc('published_at')
            ->limit(10)->get();

        $encuesta = null;
        $reto = null;
        $configService = App::make(ConfiguracionService::class);
        $encuestaRaw = $configService->obtener('encuesta_activa');
        if (is_array($encuestaRaw) && !empty($encuestaRaw['pregunta']) && !empty($encuestaRaw['opciones'])) {
            $opciones = array_map(fn ($opcion) => [
                'texto' => (string) ($opcion['texto'] ?? ''),
                'votos' => (int) ($opcion['votos'] ?? 0),
            ], array_values($encuestaRaw['opciones']));

            $encuesta = [
                'pregunta' => (string) $encuestaRaw['pregunta'],
                'opciones' => $opciones,
                'total_votos' => array_sum(array_column($opciones, 'votos')),
            ];
        }

        $retoRaw = $configService->obtener('reto_activa');
        if (is_array($retoRaw) && !empty($retoRaw['pregunta']) && !empty($retoRaw['opciones'])) {
            $opcionesReto = array_map(fn ($opcion) => [
                'texto' => (string) ($opcion['texto'] ?? ''),
                'votos' => (int) ($opcion['votos'] ?? 0),
            ], array_values($retoRaw['opciones']));

            $reto = [
                'pregunta' => (string) $retoRaw['pregunta'],
                'opciones' => $opcionesReto,
                'total_votos' => array_sum(array_column($opcionesReto, 'votos')),
                'respuesta_correcta' => (int) ($retoRaw['respuesta_correcta'] ?? -1),
            ];
        }

        View::render('public.home', [
            'noticiasRecientes' => $noticiasRecientes,
            'opinionRecientes' => $opinionRecientes,
            'investigacionesRecientes' => $investigacionesRecientes,
            'estelar' => $estelar,
            'encuesta' => $encuesta,
            'reto' => $reto,
        ]);
    }

    /**
     * Nota principal: el artículo marcado como "destacado" publicado más reciente.
     * Si no hay ninguno marcado, cae a la última investigación y, si no, al último post.
     */
    private function obtenerEstelar(): ?object
    {
        $consulta = function (array $condiciones = []): ?object {
            $query = Connection::table('blog_posts')
                ->leftJoin('users', 'users.id', '=', 'blog_posts.autor_id')
                ->select('blog_posts.*', 'users.name as autor_nombre')
                ->where('blog_posts.status', 'publicado')
                ->whereNull('blog_posts.deleted_at');

            foreach ($condiciones as $columna => $valor) {
                $query = $query->where($columna, $valor);
            }

            return $query->orderByDesc('blog_posts.published_at')->limit(1)->first();
        };

        $estelar = $consulta(['blog_posts.destacado' => 1]);

        if (!$estelar) {
            $estelar = $consulta(['blog_posts.tipo' => 'investigacion']);
        }

        return $estelar ?? $consulta();
    }
}
