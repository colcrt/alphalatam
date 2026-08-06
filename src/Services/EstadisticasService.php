<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Cache;
use App\Database\Connection;

class EstadisticasService
{
    private const CACHE_TTL = 43200;

    public function resumen(): array
    {
        return Cache::remember('estadisticas:resumen', self::CACHE_TTL, function () {
            $base = Connection::table('blog_posts')
                ->whereNull('deleted_at');

            return [
                'total_posts' => (clone $base)->count(),
                'total_noticias' => (clone $base)->where('tipo', 'noticia')->count(),
                'total_opinion' => (clone $base)->where('tipo', 'opinion')->count(),
                'total_investigaciones' => (clone $base)->where('tipo', 'investigacion')->count(),
                'total_borradores' => (clone $base)->where('status', 'borrador')->count(),
                'total_publicados' => (clone $base)->where('status', 'publicado')->count(),
                'total_comentarios_pendientes' => Connection::table('blog_comentarios')
                    ->where('status', 'pendiente')
                    ->count(),
                'total_categorias' => Connection::table('blog_categorias')
                    ->count(),
                'total_etiquetas' => Connection::table('etiquetas')
                    ->count(),
                'total_usuarios' => Connection::table('users')
                    ->count(),
            ];
        });
    }

    public function postsPorTipo(): array
    {
        return Cache::remember('estadisticas:posts_por_tipo', self::CACHE_TTL, function () {
            $filas = Connection::table('blog_posts')
                ->select('tipo', Connection::raw('COUNT(*) as total'))
                ->where('status', 'publicado')
                ->whereNull('deleted_at')
                ->groupBy('tipo')
                ->get();

            $resultado = [];
            foreach ($filas as $fila) {
                $resultado[$fila->tipo] = (int) $fila->total;
            }
            return $resultado;
        });
    }
}
