<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Database\Connection;
use App\Models\Contrato;
use App\Repositories\Contracts\ContratoRepositoryInterface;

class EloquentContratoRepository implements ContratoRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array
    {
        $query = Connection::table('contratos')
            ->whereNull('deleted_at');

        if (!empty($filtros['busqueda'])) {
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $query = $query->where('numero_contrato', 'LIKE', $busqueda)
                ->orWhere('objeto', 'LIKE', $busqueda);
        }

        if (!empty($filtros['empresa_id'])) {
            $query = $query->where('empresa_id', $filtros['empresa_id']);
        }

        $query = $query->orderByDesc('created_at');

        return $query->paginate($porPagina, $pagina);
    }

    public function buscarPorId(int $id): ?Contrato
    {
        $row = Connection::table('contratos')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return null;
        }

        $contrato = Contrato::fromRow($row);
        $this->cargarEmpresa($contrato);

        return $contrato;
    }

    public function crear(array $datos): Contrato
    {
        $contrato = Contrato::create($datos);
        return $contrato;
    }

    public function actualizar(Contrato $contrato, array $datos): Contrato
    {
        $contrato->update($datos);
        return $contrato;
    }

    public function eliminar(Contrato $contrato): bool
    {
        return $contrato->delete();
    }

    public function buscarPorEmpresa(int $empresaId): array
    {
        $rows = Connection::table('contratos')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        return Contrato::fromRows($rows);
    }

    public function contar(): int
    {
        return Connection::table('contratos')
            ->whereNull('deleted_at')
            ->count();
    }

    private function cargarEmpresa(Contrato $contrato): void
    {
        $contrato->empresa = $contrato->belongsTo(
            \App\Models\Empresa::class,
            'empresa_id',
            'id'
        );
    }
}
