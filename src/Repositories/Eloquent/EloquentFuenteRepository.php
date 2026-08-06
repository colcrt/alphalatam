<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Database\Connection;
use App\Models\Fuente;
use App\Repositories\Contracts\FuenteRepositoryInterface;

class EloquentFuenteRepository implements FuenteRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array
    {
        $query = Connection::table('fuentes');

        if (!empty($filtros['busqueda'])) {
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $query = $query->where('titulo', 'LIKE', $busqueda)
                ->orWhere('autor', 'LIKE', $busqueda)
                ->orWhere('url', 'LIKE', $busqueda);
        }

        if (!empty($filtros['tipo'])) {
            $query = $query->where('tipo', $filtros['tipo']);
        }

        $query = $query->orderByDesc('created_at');

        return $query->paginate($porPagina, $pagina);
    }

    public function buscarPorId(int $id): ?Fuente
    {
        $row = Connection::table('fuentes')
            ->where('id', $id)
            ->first();

        if (!$row) {
            return null;
        }

        $fuente = Fuente::fromRow($row);
        $this->cargarReferencias($fuente);

        return $fuente;
    }

    public function crear(array $datos): Fuente
    {
        $fuente = Fuente::create($datos);
        return $fuente;
    }

    public function actualizar(Fuente $fuente, array $datos): Fuente
    {
        $fuente->update($datos);
        return $fuente;
    }

    public function eliminar(Fuente $fuente): bool
    {
        return $fuente->delete();
    }

    public function buscarPorEntidad(string $tipo, int $id): array
    {
        $rows = Connection::table('fuente_referenciable')
            ->where('referenciable_type', $tipo)
            ->where('referenciable_id', $id)
            ->get();

        $fuenteIds = array_column((array) $rows, 'fuente_id');

        if (empty($fuenteIds)) {
            return [];
        }

        $fuentes = Connection::table('fuentes')
            ->whereIn('id', $fuenteIds)
            ->orderByDesc('created_at')
            ->get();

        return Fuente::fromRows($fuentes);
    }

    public function contar(): int
    {
        return Connection::table('fuentes')->count();
    }

    private function cargarReferencias(Fuente $fuente): void
    {
        $referencias = Connection::table('fuente_referenciable')
            ->where('fuente_id', $fuente->getKey())
            ->get();

        $fuente->referencias = $referencias;
    }
}
