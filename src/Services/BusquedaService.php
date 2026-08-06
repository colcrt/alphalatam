<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class BusquedaService
{
    private const LIMITE_RESULTADOS = 20;

    public function buscarGlobal(string $termino): array
    {
        if (mb_strlen($termino) < 2) {
            return [];
        }

        $termino = $this->sanitizarTermino($termino);

        if ($termino === '') {
            return [];
        }

        $filas = Connection::table('blog_posts')
            ->where('status', 'publicado')
            ->whereNull('deleted_at')
            ->whereRaw(
                "MATCH(`titulo`, `extracto`) AGAINST(? IN BOOLEAN MODE)",
                [$termino]
            )
            ->limit(self::LIMITE_RESULTADOS)
            ->get();

        $resultados = [];
        foreach ($filas as $fila) {
            $resultados[] = [
                'tipo' => $fila->tipo,
                'titulo' => $fila->titulo,
                'url' => route('blog.show', ['slug' => $fila->slug]),
            ];
        }

        return $resultados;
    }

    private function sanitizarTermino(string $termino): string
    {
        $termino = preg_replace('/[+\-<>()~!@"*]/', ' ', $termino);
        $termino = preg_replace('/\s+/', ' ', trim($termino));

        if (mb_strlen($termino) > 200) {
            $termino = mb_substr($termino, 0, 200);
        }

        return $termino;
    }
}
