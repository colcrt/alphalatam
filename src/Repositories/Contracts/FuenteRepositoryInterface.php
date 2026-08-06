<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Fuente;

interface FuenteRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array;
    public function buscarPorId(int $id): ?Fuente;
    public function crear(array $datos): Fuente;
    public function actualizar(Fuente $fuente, array $datos): Fuente;
    public function eliminar(Fuente $fuente): bool;
    public function buscarPorEntidad(string $tipo, int $id): array;
    public function contar(): int;
}
