<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\PartidoPolitico;

interface PartidoPoliticoRepositoryInterface
{
    public function paginarParaAdmin(array $filtros = [], int $porPagina = 20, int $pagina = 1): array;
    public function buscarPorSlugPublicado(string $slug): ?PartidoPolitico;
    public function crear(array $datos): PartidoPolitico;
    public function actualizar(PartidoPolitico $partido, array $datos): PartidoPolitico;
    public function eliminar(PartidoPolitico $partido): bool;
    public function contar(): int;
}
