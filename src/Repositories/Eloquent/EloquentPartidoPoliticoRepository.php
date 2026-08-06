<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Database\Connection;
use App\Models\PartidoPolitico;
use App\Repositories\Contracts\PartidoPoliticoRepositoryInterface;

class EloquentPartidoPoliticoRepository implements PartidoPoliticoRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array
    {
        $query = Connection::table('partidos_politicos')
            ->whereNull('deleted_at');

        if (!empty($filtros['busqueda'])) {
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $query = $query->where('nombre', 'LIKE', $busqueda)
                ->orWhere('siglas', 'LIKE', $busqueda);
        }

        if (!empty($filtros['status'])) {
            $query = $query->where('status', $filtros['status']);
        }

        $query = $query->orderByDesc('created_at');

        return $query->paginate($porPagina, $pagina);
    }

    public function buscarPorSlugPublicado(string $slug): ?PartidoPolitico
    {
        $row = Connection::table('partidos_politicos')
            ->where('slug', $slug)
            ->where('status', 'publicado')
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return null;
        }

        $partido = PartidoPolitico::fromRow($row);
        $this->cargarRelaciones($partido);

        return $partido;
    }

    public function crear(array $datos): PartidoPolitico
    {
        $datos['slug'] = PartidoPolitico::generarSlug($datos['nombre'] ?? '');
        $partido = PartidoPolitico::create($datos);
        return $partido;
    }

    public function actualizar(PartidoPolitico $partido, array $datos): PartidoPolitico
    {
        if (isset($datos['nombre']) && $datos['nombre'] !== $partido->nombre) {
            $datos['slug'] = PartidoPolitico::generarSlug($datos['nombre']);
        }

        $partido->update($datos);
        return $partido;
    }

    public function eliminar(PartidoPolitico $partido): bool
    {
        return $partido->delete();
    }

    public function contar(): int
    {
        return Connection::table('partidos_politicos')
            ->whereNull('deleted_at')
            ->count();
    }

    private function cargarRelaciones(PartidoPolitico $partido): void
    {
        $partido->relaciones = [
            'personas' => $partido->personas(),
            'fuentes' => $partido->fuentes(),
        ];
    }
}
