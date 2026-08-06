<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Contrato;

interface ContratoRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array;
    public function buscarPorId(int $id): ?Contrato;
    public function crear(array $datos): Contrato;
    public function actualizar(Contrato $contrato, array $datos): Contrato;
    public function eliminar(Contrato $contrato): bool;
    public function buscarPorEmpresa(int $empresaId): array;
    public function contar(): int;
}
